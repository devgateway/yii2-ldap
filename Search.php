<?php
namespace devgateway\ldap;

use devgateway\ldap\Connection;

class Search implements \Iterator
{
    protected static $functions = [
        Connection::BASE =>     'ldap_read',
        Connection::ONELEVEL => 'ldap_list',
        Connection::SUBTREE =>  'ldap_search'
    ];
    protected $conn;
    protected $search_function;
    protected $search_result;
    protected $cookie = '';
    protected $current_entry = false;
    protected $entries_seen = 0;

    public function __construct(
        $conn,
        int $scope,
        string $base,
        string $filter,
        array $attrs = [],
        int $size_limit = 0,
        int $time_limit = 0,
        int $deref = LDAP_DEREF_NEVER,
        int $page_size = 500,
        bool $page_critical = false
    ) {
        // validate search scope
        if (array_key_exists($scope, self::$functions)) {
            $this->search_function = self::$functions[$scope];
        } else {
            $valid_scopes = implode(', ', array_keys(self::$functions));
            $message = "Scope must be one of: $valid_scopes, not $scope";
            throw new \OutOfRangeException($message);
        }

        $this->conn = $conn;
        $this->base = $base;
        $this->filter = $filter;
        $this->attrs = $attrs;
        $this->size_limit = $size_limit;
        $this->time_limit = $time_limit;
        $this->deref = $deref;
        $this->page_size = $page_size;
        $this->page_critical = $page_critical;
    }

    private function sendPaginationControl()
    {
        $pagination_supported = ldap_control_paged_result(
            $this->conn,
            $this->page_size,
            $this->page_critical,
            $this->cookie
        );

        if (!$pagination_supported) {
            $this->page_size = 0;
        }
    }

    private function doSearch()
    {
        $this->search_result = @($this->search_function)(
            $this->conn,
            $this->base,
            $this->filter,
            $this->attrs,
            0,
            $this->size_limit,
            $this->time_limit,
            $this->deref
        );
        if (!$this->search_result) {
            throw new LdapException($this->conn);
        }

        // retrieve the first result
        $this->current_entry = ldap_first_entry($this->conn, $this->search_result);
    }

    public function rewind()
    {
        $this->entries_seen = 0;

        // send pagination control
        if ($this->page_size) {
            $this->sendPaginationControl();
        }

        // call appropriate search function
        $this->doSearch();
    }

    public function current()
    {
        return ldap_get_attributes($this->conn, $this->current_entry);
    }

    public function key()
    {
        return ldap_get_dn($this->conn, $this->current_entry);
    }

    public function next()
    {
        $this->current_entry = @ldap_next_entry($this->conn, $this->current_entry);

        // if pagination enabled, and current page read
        if ($this->page_size && !$this->current_entry) {
            // receive a cookie for the next search
            $success = @ldap_control_paged_result_response(
                $this->conn,
                $this->search_result,
                $this->cookie
            );
            // ignore errors if read enough entries
            if (!$success && $this->entries_seen < $this->size_limit) {
                throw new LdapException($this->conn);
            }

            // if the cookie is set
            if (!is_null($this->cookie) && $this->cookie != '') {
                // reissue a control with it, and continue searching
                $this->sendPaginationControl();
                $this->doSearch();
            }
        }
    }

    public function valid()
    {
        $valid = $this->current_entry !== false;

        if ($valid) {
            $this->entries_seen++;
        };

        return $valid;
    }

    public function __destruct()
    {
        ldap_free_result($this->search_result);
    }
}
