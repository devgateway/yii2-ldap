<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\LdapResults;
use devgateway\ldap\LdapConnection;

class TestLdapResults extends TestCase
{
    protected $conn;
    protected $base;

    public function setUp()
    {
        require('settings.php');

        $this->base = $base;
        $this->conn = new LdapConnection($host, $port, $bind_dn, $bind_pw);
    }

    /**
     * @dataProvider scopeProvider
     */
    public function testSearchScopes($method)
    {
        $this->assertInstanceOf('devgateway\\ldap\\LdapConnection', $this->conn);

        $filter = '(objectClass=*)';
        $limit = 1;

        $search_results = $this->conn->$method($this->base, $filter, array(), 0, $limit);

        $this->assertInstanceOf('devgateway\\ldap\\LdapResults', $search_results);
        $this->assertEquals($limit, $search_results->count());

        $i = 0;
        foreach($search_results as $dn => $attrs) {
            $this->assertNotEquals('', $dn);
            $this->assertArrayHasKey('count', $attrs);
            $i++;
        }

        $this->assertEquals($limit, $i);
    }

    public function scopeProvider()
    {
        return [
            'Subtree search' => ['search'],
            'One level search' => ['list'],
            'Base search' => ['read']
        ];
    }

    public function tearDown()
    {
        unset($this->conn);
    }
}

