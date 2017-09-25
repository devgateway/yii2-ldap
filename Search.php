<?php
/**
 * Search class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\Connection;

/** Iterable search results implementing lazy search. */
class Search implements \Iterator
{
    /** @var array $functions Search scopes => PHP LDAP search functions. */
    protected static $functions = [
        Connection::BASE =>     'ldap_read',
        Connection::ONELEVEL => 'ldap_list',
        Connection::SUBTREE =>  'ldap_search'
    ];

    /** @var resource $conn LDAP connection handle. */
    protected $conn;

    /** @var string $search_function Name of PHP LDAP search function for
     * indirect call */
    protected $search_function;

    /** @var resource|bool $search_result Handle to LDAP search result or false
     * if search failed. */
    protected $search_result;

    /** @var string $cookie LDAP internal cookie for paged search results. */
    protected $cookie = '';

    /** @var resource|bool $current_entry Handle to each entry in LDAP search
     * results. Becomes false if no more entries left on current page. */
    protected $current_entry = false;

    /** @var int $entries_seen Number of entries returned by LDAP search so far.
     * Used to distinguish between client and server size limit hit. */
    protected $entries_seen = 0;

    /**
     * Initialize class members, and validate search scope.
     *
     * @param resource $conn LDAP connection handle.
     * @param int $scope Search scope, one of Connection::BASE, ONELEVEL, or SUBTREE.
     * @param string $base Search base.
     * @param string $filter Search filter. Must be properly escaped.
     * @param array $attrs Array of attributes to request from LDAP.
     * @param int $size_limit Limit search to this many results; 0 for no limit.
     * @param int $time_limit Limit search duration, in seconds; 0 for no limit.
     * @param int $deref Dereference aliases.
     * @param int $page_size Request paginated results, if supported.
     * @param bool $page_critical Raise an exception if pagination is not supported.
     */
    public function __construct(
        $conn,
        $scope,
        $base,
        $filter,
        $attrs = [],
        $size_limit = 0,
        $time_limit = 0,
        $deref = LDAP_DEREF_NEVER,
        $page_size = 500,
        $page_critical = false
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

    /**
     * Request paginated results from server, and fall back to normal search if
     * pagination not supported.
     */
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

    /**
     * Call appropriate LDAP search function, and retrieve the first result.
     *
     * @throws LdapException if search fails.
     */
    private function doSearch()
    {
        $function = $this->search_function;
        $this->search_result = @$function(
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

    /** Reset iterator, restart the search. */
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

    /**
     * Return current value of the iterator.
     *
     * @return array Attributes of current entry.
     */
    public function current()
    {
        return ldap_get_attributes($this->conn, $this->current_entry);
    }

    /**
     * Return current key of the iterator.
     *
     * @return string Distinguished name of current entry.
     */
    public function key()
    {
        return ldap_get_dn($this->conn, $this->current_entry);
    }

    /** Retrieve next page if required, retrieve next entry if possible. */
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

    /**
     * Test if current entry may be retrieved by iterator.
     *
     * @return bool Whether current entry is valid.
     */
    public function valid()
    {
        $valid = $this->current_entry !== false;

        if ($valid) {
            $this->entries_seen++;
        };

        return $valid;
    }

    /** Release and invalidate search result handle. */
    public function __destruct()
    {
        if ($this->search_result) {
            ldap_free_result($this->search_result);
        }
    }

    /**
     * Return a single search result.
     *
     * @return array Attributes of an entry.
     */
    public function getOne()
    {
        foreach ($this as $attrs) {
            return $attrs;
        }

        throw new \RuntimeException('Object not found');
    }

    /**
     * Return the DN of a single search result.
     *
     * @return string Distinguished name of the item.
     */
    public function getOneDn()
    {
        foreach ($this as $key => $ignore_value) {
            return $key;
        }

        throw new \RuntimeException('Object not found');
    }
}
