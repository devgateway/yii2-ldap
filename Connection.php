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
    protected $conn;

    public function __construct($host, $port = null, $bind_dn = null, $bind_pw = null)
    {
        $this->bind($host, $port, $bind_dn, $bind_pw);
    }

    public function bind($host, $port = null, $bind_dn = null, $bind_pw = null)
    {
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

    public function search($base, $filter, $attrs = null, $sizelimit = 0, $timelimit = 0, $deref = 0 )
    {
        $result = @ldap_search($this->conn, $base, $filter, $attrs, $sizelimit, $timelimit, $deref);
        if (!$result) throw new AuthError();
        return new Results($this->conn, $result);
    }

    public function read($base, $filter, $attrs = null, $sizelimit = 0, $timelimit = 0, $deref = 0)
    {
        $result = @ldap_read($this->conn, $base, $filter, $attrs, $sizelimit, $timelimit, $deref);
        if (!$result) throw new AuthError();
        return new Results($this->conn, $result);
    }

    public function list($base, $filter, $attrs = null, $sizelimit = 0, $timelimit = 0, $deref = 0)
    {
        $result = @ldap_list($this->conn, $base, $filter, $attrs, $sizelimit, $timelimit, $deref);
        if (!$result) throw new AuthError();
        return new Results($this->conn, $result);
    }

}
