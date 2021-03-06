<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\Search;
use devgateway\ldap\Connection;

class SearchTest extends TestCase
{
    protected $conn;
    protected $base;

    public function setUp()
    {
        $config = require('config.php');
        $this->base = $base;

        $host = $config['host'];
        $port = isset($config['port']) ? $config['port'] : 389;
        $bind_dn = isset($config['bind_dn']) ? $config['bind_dn'] : null;
        $bind_pw = isset($config['bind_pw']) ? $config['bind_pw'] : null;

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

        $search_results = new Search(
            $this->conn,
            Connection::SUBTREE,
            $this->base,
            $filter,
            [],
            $limit
        );

        $this->assertInstanceOf('devgateway\\ldap\\Search', $search_results);

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

