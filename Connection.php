<?php
namespace devgateway\ldap;

use devgateway\ldap\PagedResults;

class Connection
{
    constant BASE = 0;
    constant ONELEVEL = 1;
    constant SUBTREE = 2;

    protected $conn;
    protected $page_size;
    protected $page_critical;

    public function __construct(
        string $host,
        int $port = 389,
        $bind_dn = null,
        $bind_pw = null,
        int $page_size = 500,
        bool $page_critical = false
    ) {
        $this->page_size = $page_size;
        $this->page_critical = $page_critical;

        $this->bind($host, $port, $bind_dn, $bind_pw, $page_size, $page_critical);
    }

    public function bind(
        string $host,
        int $port = 389,
        $bind_dn = null,
        $bind_pw = null,
        int $page_size = 500,
        bool $page_critical = false
    ) {
        $this->conn = ldap_connect($host, $port);
        if (!$this->conn) {
            throw new \RuntimeException("LDAP settings invalid");
        }

        $result = ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!$result) {
            throw new LdapException($this->conn);
        }

        # bind anonymously
        $result = ldap_bind($this->conn);
        if (!$result) {
            throw new LdapException($this->conn);
        }
    }

    public function __destruct()
    {
        ldap_unbind($this->conn);
    }

    # escapes dangerous characters from the input string
    public static function escapeFilter($string)
    {
        if (function_exists('ldap_escape')) {
            return ldap_escape($string, '', LDAP_ESCAPE_FILTER);
        } else {
            $map = array(
                '\\' => '\\5c', # gotta be first, see str_replace info
                '*' => '\\2a',
                '(' => '\\28',
                ')' => '\\29',
                "\0" => '\\00'
            );
            return str_replace(array_keys($map), $map, $string);
        }
    }

    public function search(
        $scope,
        string $base,
        string $filter,
        array $attrs = [],
        int $sizelimit = 0,
        int $timelimit = 0,
        int $deref = LDAP_DEREF_NEVER
    ) {
        return new PagedResults(
            $this->conn,
            $scope,
            $base,
            $filter,
            $attrs,
            $sizelimit,
            $timelimit,
            $deref
        );
    }
}

