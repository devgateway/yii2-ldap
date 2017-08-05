<?php
namespace devgateway\ldap;

use devgateway\ldap\Connection;

class PagedResults implements \Iterator
{
    protected static $functions = [
        Connection::BASE =>     'ldap_read',
        Connection::ONELEVEL => 'ldap_list',
        Connection::SUBTREE =>  'ldap_search'
    ];
    protected $conn;
    protected $function;
    protected $search_result;
    protected $cookie = '';
    protected $current_entry = false;

    public function __construct(
        $conn,
        $scope,
        string $base,
        string $filter,
        array $attrs = [],
        int $sizelimit = 0,
        int $timelimit = 0,
        int $deref = LDAP_DEREF_NEVER,
        int $page_size = 500,
        bool $page_critical = false
    ) {
        $this->conn = $conn;

        // validate search scope
        if (array_key_exists($scope, self::functions)) {
            $self->function = self::functions[$scope];
        } else {
            $valid_scopes = implode(', ', array_keys(self::functions));
            $message = "Scope must be one of: $valid_scopes, not $scope";
            throw new \OutOfRangeException($message);
        }

        // call appropriate search function
        $this->search_result = @$function(
            $this->conn,
            $base,
            $filter,
            $attrs,
            $sizelimit,
            $timelimit,
            $deref
        );
        if (!$this->search_result) {
            throw new LdapException();
        }
    }

    public function rewind()
    {
        // send pagination control
        if ($this->page_size) {
            $paging_supported = ldap_control_paged_result(
                $this->conn,
                $this->page_size,
                $this->page_critical
            );
            if (!$paging_supported) {
                $this->page_size = 0;
            }
        }

        $this->current_entry = @ldap_first_entry($this->conn, $this->search_result);
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
        $this->current_entry = @ldap_next_entry($this->conn, $this->search_result);
    }

    public function valid()
    {
        return $this->current_entry !== false;
    }

    public function __destruct()
    {
        ldap_free_result($this->search_result);
    }
}

