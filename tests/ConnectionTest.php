<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\Search;
use devgateway\ldap\Connection;

class ConnectionTest extends TestCase
{
    protected $conn;
    protected $base;

    public function setUp()
    {
        $config = require('config.php');
        $this->base = $base;

        $this->conn = new Connection($config);
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
    public function testSearchScopes($scope)
    {
        $this->assertInstanceOf('devgateway\\ldap\\Connection', $this->conn);

        $filter = '(objectClass=*)';
        $limit = 1;

        $search_results = $this->conn->search(
            $scope,
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

    public function testAdd()
    {
        require('config.php');

        $this->assertInstanceOf('devgateway\\ldap\\Connection', $this->conn);

        $add_result = $this->conn->add($add_dn, $add_entry);

        $filter = sprintf('(&(objectClass=virtualMachine)(cn=%s))', $add_entry['cn']);
        $limit = 1;

        $search_results = $this->conn->search(Connection::BASE, $add_dn, $filter, [], $limit);

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
            'Subtree search' => [Connection::SUBTREE],
            'One level search' => [Connection::ONELEVEL],
            'Base search' => [Connection::BASE]
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

