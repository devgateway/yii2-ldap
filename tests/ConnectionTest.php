<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\Search;
use devgateway\ldap\Connection;
use devgateway\ldap\Schema;

class ConnectionTest extends TestCase
{
    protected $conn;
    protected $base;

    public function setUp()
    {
        $config = require('config.php');
        $this->base = $base;

        $this->conn = new Connection($config);

        $this->entry = [
            'cn' => "test",
            'memorySize' => "2",
            'virtualCPU' => "2",
            'objectClass' => ["virtualMachine", "device", "ansibleHost"]
        ];
        $this->dn = sprintf('cn=%s,%s', $this->entry['cn'], $base);
        $this->filter = sprintf('(&(objectClass=virtualMachine)(cn=%s))', $this->entry['cn']);

        $host = $config['host'];
        $port = isset($config['port']) ? $config['port'] : 389;
        $bind_dn = isset($config['bind_dn']) ? $config['bind_dn'] : null;
        $bind_pw = isset($config['bind_pw']) ? $config['bind_pw'] : null;

        $this->canonical_conn = ldap_connect($host, $port);
        if ($this->canonical_conn === false) {
          throw new \Exception('Can\'t connect to LDAP server');
        }

        $result = ldap_set_option($this->canonical_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!$result) {
            throw new \Exception('Can\'t request LDAPv3');
        }

        $result = ldap_bind($this->canonical_conn, $bind_dn, $bind_pw);
        if (!$result) {
            throw new \Exception('Can\'t bind to LDAP');
        }
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
        $this->conn->add($this->dn, $this->entry);

        $limit = 1;
        $search_results = $this->conn->search(Connection::BASE, $this->dn, $this->filter, [], $limit);
        $i = 0;

        foreach ($search_results as $dn => $attrs) {
            $this->assertNotEquals('', $dn);
            $this->assertArrayHasKey('count', $attrs);
            $i++;
        }

        $this->assertEquals($limit, $i);

        $result = ldap_delete($this->canonical_conn, $this->dn);
        if (!$result) {
            throw new \Exception('Can\'t delete');
        }
    }

    public function testDelete()
    {
        $result = ldap_add($this->canonical_conn, $this->dn, $this->entry);
        if (!$result) {
            throw new \Exception('Can\'t add');
        }

        $limit = 0;

        $this->conn->delete($this->dn);

        try {
            $search_results = $this->conn->search(Connection::BASE, $this->dn, $this->filter, [], $limit);
            $i = 0;

            foreach ($search_results as $result) {
                $i++;
            }

            $this->assertEquals($limit, $i);
        } catch (\Exception $e) {
            $expected_code = 0x20;
            $error_code = $e->getCode();
            $this->assertEquals($expected_code, $error_code);
        }
    }

    public function testRename()
    {
        $test_dn_rename = 'cn=test_rename';
        $test_dn_rename_full = sprintf('%s,%s', $test_dn_rename, $this->base);
        $test_filter_rename = sprintf('(&(objectClass=virtualMachine)(%s))', $test_dn_rename);

        $result = ldap_add($this->canonical_conn, $this->dn, $this->entry);
        if (!$result) {
            throw new \Exception('Can\'t add');
        }

        $this->conn->rename($this->dn, $test_dn_rename, null, true);

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
            $search_results = $this->conn->search(Connection::BASE, $this->dn, $this->filter, [], $limit);
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

        $result = ldap_delete($this->canonical_conn, $test_dn_rename_full);
        if (!$result) {
            throw new \Exception('Can\'t delete');
        }
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
        ldap_unbind($this->canonical_conn);
    }

    public function testStandardClass()
    {
        $schema = new Schema($this->conn);
        $cn_syntax = '1.3.6.1.4.1.1466.115.121.1.15';
        $syntax = $schema['device']->must['cn']->syntax->__toString();

        $this->assertEquals($cn_syntax, $syntax);
    }
}

