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

        $this->conn->add($test_dn, $test_entry);

        $limit = 1;
        $search_results = $this->conn->search(Connection::BASE, $test_dn, $test_filter, [], $limit);
        $i = 0;

        foreach ($search_results as $dn => $attrs) {
            $this->assertNotEquals('', $dn);
            $this->assertArrayHasKey('count', $attrs);
            $i++;
        }

        $this->assertEquals($limit, $i);
    }

    public function testDelete()
    {
        require('config.php');

        $limit = 1;
        $search_results = $this->conn->search(Connection::BASE, $test_dn, $test_filter, [], $limit);
        $i = 0;

        foreach ($search_results as $dn => $attrs) {
            $this->assertNotEquals('', $dn);
            $this->assertArrayHasKey('count', $attrs);
            $i++;
        }

        $this->assertEquals($limit, $i);

        $this->conn->delete($test_dn);

        try {
            $search_results = $this->conn->search(Connection::BASE, $test_dn, $test_filter, [], $limit);
            $i = 0;

            foreach ($search_results as $result) {
                $i++;
            }

            $this->assertEquals(0, $i);
        } catch (\Exception $e) {
            $expected_code = 0x20;
            $error_code = $e->getCode();
            $this->assertEquals($expected_code, $error_code);
        }
    }

    public function testRename()
    {
        require('config.php');

        $this->conn->add($test_dn, $test_entry);

        $limit = 1;
        $search_results = $this->conn->search(Connection::BASE, $test_dn, $test_filter, [], $limit);
        $i = 0;

        foreach ($search_results as $dn => $attrs) {
            $this->assertNotEquals('', $dn);
            $this->assertArrayHasKey('count', $attrs);
            $i++;
        }

        $this->assertEquals($limit, $i);

        $this->conn->rename($test_dn, $test_dn_rename, null, true);

        $limit = 1;
        $search_results = $this->conn->search(Connection::BASE, $test_dn_rename_full, $test_filter_rename, [], $limit);
        $i = 0;

        foreach ($search_results as $dn => $attrs) {
            $this->assertNotEquals('', $dn);
            $this->assertArrayHasKey('count', $attrs);
            $i++;
        }

        $this->assertEquals($limit, $i);

        $limit = 0;
        try {
            $search_results = $this->conn->search(Connection::BASE, $test_dn, $test_filter, [], $limit);
            $i = 0;

            foreach ($search_results as $result) {
                $i++;
            }

            $this->assertEquals(0, $i);
        } catch (\Exception $e) {
            $expected_code = 0x20;
            $error_code = $e->getCode();
            $this->assertEquals($expected_code, $error_code);
        }

        $this->conn->delete($test_dn_rename_full);
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

