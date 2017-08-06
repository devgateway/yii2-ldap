<?php
namespace devgateway\ldap;

use devgateway\ldap\Results;
use yii\base\Component;

class Connection extends Component
{
    const BASE = 0;
    const ONELEVEL = 1;
    const SUBTREE = 2;

    protected $conn = false;
    protected $bound = false;

    public $host = null;
    public $port = 389;
    public $bind_dn = null;
    public $bind_pw = null;

    protected function connect()
    {
        $this->conn = ldap_connect($this->host, $this->port);
        if (!$this->conn) {
            throw new \RuntimeException("LDAP settings invalid");
        }

        $result = ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!$result) {
            throw new LdapException($this->conn);
        }
    }

    protected function bind()
    {
        if ($this->bound) {
            return;
        }

        if ($this->conn === false) {
            $this->connect();
        }

        $result = ldap_bind($this->conn, $this->bind_dn, $this->bind_pw);
        if ($result) {
            $this->bound = true;
        } else {
            throw new LdapException($this->conn);
        }
    }

    public function rebind($bind_dn, $bind_pw)
    {
        $this->bind_dn = $bind_dn;
        $this->bind_pw = $bind_pw;

        $this->bound = false;
        $this->bind();
    }

    public function __destruct()
    {
        if ($this->conn !== false) {
            ldap_unbind($this->conn);
        }
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
        int $scope,
        string $base,
        string $filter,
        array $attrs = [],
        int $sizelimit = 0,
        int $timelimit = 0,
        int $deref = LDAP_DEREF_NEVER,
        int $page_size = 500,
        bool $page_critical = false
    ) {
        return new Results(
            $this->conn,
            $scope,
            $base,
            $filter,
            $attrs,
            $sizelimit,
            $timelimit,
            $deref,
            $page_size,
            $page_critical
        );
    }
}

