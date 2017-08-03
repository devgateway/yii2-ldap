<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\LdapResults;

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

        $handle = @ldap_search($this->conn, $this->base, $filter, array(), 0, $limit);
        if ($handle === false) {
            throw new \Exception('Search failed');
        }

        $search_results = new LdapResults($this->conn, $handle);

        $this->assertInstanceOf('devgateway\\ldap\\LdapResults', $search_results);
        $this->assertEquals($limit, $search_results->count());

        $i = 0;
        foreach($search_results as $key => $value) {
            $this->assertNotEquals('', $key);
            $this->assertArrayHasKey('count', $value);
            $i++;
        }

        $this->assertEquals($limit, $i);
    }

    public function tearDown()
    {
        ldap_unbind($this->conn);
    }
}

