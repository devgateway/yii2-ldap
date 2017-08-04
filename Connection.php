<?php
namespace devgateway\ldap;

use devgateway\ldap\Results;

class LdapException extends \RuntimeException
{
    public function __construct($connection)
    {
        $code = @ldap_errno($connection);
        $message = @ldap_err2str($code);
        parent::__construct($message, $code);
    }
}

class Connection
{
    constant BASE = 0;
    constant ONELEVEL = 1;
    constant SUBTREE = 2;

    protected $conn;
    protected $page_size;
    protected $page_critical;
    protected static $functions = [
        BASE =>     'ldap_read',
        ONELEVEL => 'ldap_list',
        SUBTREE =>  'ldap_search'
    ];

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

    private function search(
        $scope,
        string $base,
        string $filter,
        array $attrs = [],
        int $sizelimit = 0,
        int $timelimit = 0,
        int $deref = LDAP_DEREF_NEVER
    ) {
        if (array_key_exists($scope, self::functions)) {
            $function = self::functions[$scope];
        } else {
            $valid_scopes = implode(', ', array_keys(self::functions));
            $message = "Scope must be one of: $valid_scopes, not $scope";
            throw new \OutOfRangeException($message);
        }

        $result = @$function(
            $this->conn,
            $base,
            $filter,
            $attrs,
            $sizelimit,
            $timelimit,
            $deref
        );
        if (!$result) {
            throw new LdapException();
        }

        return new Results($this->conn, $result);
    }
}

