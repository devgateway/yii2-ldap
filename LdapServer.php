<?php
namespace ldap;

class LDAPServerError extends \Exception 
{
}

class LDAPAuthError extends \Exception 
{
}

class LdapServer
{
    private $base;
    private $conn;
    private $filter;

    public function __construct($user, $pass)
    {
        require('settings.php');
        $this->base = $base;
        $this->conn = ldap_connect($host, $port);
        $this->filter = sprintf($filter, $user);

        if (!$this->conn) throw new LDAPServerError();

        $result = ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!$result) throw new LDAPServerError();
    }
}
