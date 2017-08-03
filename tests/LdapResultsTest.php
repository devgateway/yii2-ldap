<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\OidArray;

class TestLdapResults extends TestCase
{
    protected $conn;

    public function setUp()
    {
        require('settings.php');

        $this->conn = ldap_connect($host, $port);
        if ($this->conn === false) {
          throw new \Exception('Can\'t connect to LDAP server');
        }

        $result = ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!$result) {
            throw new \Exception('Can\'t request LDAPv3');
        }

        $result = ldap_bind($this->conn, $bind_dn, $bind_pw);
        if (!$result) {
            throw new \Exception('Can\'t bind to LDAP');
        }
    }

    public function testIterator()
    {
    }

    public function tearDown()
    {
        ldap_unbind($this->conn);
    }
}

