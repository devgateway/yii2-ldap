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
        $this->conn = new Connection($host, $port, $bind_dn, $bind_pw);
    }

    /**
     * @dataProvider escapeProvider
     */
    public function testEscape($unescaped, $escaped)
    {
        $result = Connection::escapeFilter($unescaped);
        $this->assertEquals($escaped, $result);
    }

    /**
     * @dataProvider scopeProvider
     */
    public function testSearchScopes($method)
    {
        $this->assertInstanceOf('devgateway\\ldap\\Connection', $this->conn);

        $filter = '(objectClass=*)';
        $limit = 1;

        $search_results = $this->conn->$method($this->base, $filter, array(), 0, $limit);

        $this->assertInstanceOf('devgateway\\ldap\\Results', $search_results);
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

    public function escapeProvider()
    {
        $quotes = '\'single\' or "double"';
        $no_escaping = 'Hello World!';
        return [
            'backslash & null' => ["input\\output\0", 'input\\5coutput\\00'],
            'asterisk & paren' => ['free (or *libre*)', 'free \\28or \\2alibre\\2a\\29'],
            'quotes' => [$quotes, $quotes],
            'all together' => ["\0*NULL* char (\\0)", '\\00\\2aNULL\\2a char \\28\\5c0\\29'],
            'empty' => ['', ''],
            'no escaping' => [$no_escaping, $no_escaping],
        ];
    }

    public function tearDown()
    {
        unset($this->conn);
    }
}

