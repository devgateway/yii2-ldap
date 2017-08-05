<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\Results;
use devgateway\ldap\Connection;

class TestLdapResults extends TestCase
{
    protected $conn;
    protected $base;

    public function setUp()
    {
        require('settings.php');

        $this->base = $base;

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
        $filter = '(objectClass=*)';
        $limit = 1;

        $search_results = new Results(
            $this->conn,
            Connection::SUBTREE,
            $this->base,
            $filter,
            [],
            $limit
        );

        $this->assertInstanceOf('devgateway\\ldap\\Results', $search_results);

        $i = 0;
        foreach($search_results as $dn => $attrs) {
            $this->assertNotEquals('', $dn);
            $this->assertArrayHasKey('count', $attrs);
            $i++;
        }

        $this->assertEquals($limit, $i);
    }

    public function tearDown()
    {
        ldap_unbind($this->conn);
    }
}

