<?php
use PHPUnit\Framework\TestCase;
#use devgateway\ldap\Search;
use devgateway\ldap\Connection;

class ModifyTest extends TestCase
{
    protected $conn;
    protected $base;
    protected $dn;
    protected $entry;

    public function setUp()
    {
        $config = require('config.php');
        $this->base = $base;
        $this->dn = $mod_dn;
        $this->entry = $mod_entry;

        $this->conn = new Connection($config);
        $this->conn->add($this->dn, $this->entry);
    }

    /**
     * @dataProvider modProvider
     */
    public function testModify($mod_op, $mod_entries, $mod_filter, $mod_orig = null)
    {
        $limit = 1;
        if ($mod_op === Connection::MOD_DEL) {
            $limit = 0;
            $this->conn->modify(Connection::MOD_ADD, $this->dn, $mod_entries);
        } elseif ($mod_op === Connection::MOD_REPLACE) {
            $this->conn->modify(Connection::MOD_ADD, $this->dn, $mod_orig);
        }

        $this->conn->modify($mod_op, $this->dn, $mod_entries);

        $search_results = $this->conn->search(Connection::BASE, $this->dn, $mod_filter, [], $limit);
        $i = 0;

        foreach ($search_results as $dn => $attrs) {
            $this->assertNotEquals('', $dn);
            $this->assertArrayHasKey('count', $attrs);
            $i++;
        }

        $this->assertEquals($limit, $i);
    }

    public function modProvider()
    {
        require('config.php');

        return [
            'single mod_add' => [Connection::MOD_ADD, $mod_single, $mod_single_filter],
            'extra mod_add' => [Connection::MOD_ADD, $mod_extra, $mod_extra_filter],
            'multiple mod_add' => [Connection::MOD_ADD, $mod_multiple, $mod_multiple_filter],
            'single mod_del' => [Connection::MOD_DEL, $mod_single, $mod_single_filter],
            'extra mod_del' => [Connection::MOD_DEL, $mod_extra, $mod_extra_filter],
            'multiple mod_del' => [Connection::MOD_DEL, $mod_multiple, $mod_multiple_filter],
            'single mod_replace' => [Connection::MOD_REPLACE, $mod_single, $mod_single_filter, $mod_single_orig],
            'extra mod_replace' => [Connection::MOD_REPLACE, $mod_extra_repl, $mod_extra_repl_filter, $mod_extra_orig],
            'multiple mod_repalce' => [Connection::MOD_REPLACE, $mod_multiple, $mod_multiple_filter, $mod_multiple_orig],
            'modify' => [Connection::MOD, $mod_entry_new, $mod_entry_new_filter]
        ];
    }

    public function tearDown()
    {
        $this->conn->delete($this->dn);
        unset($this->conn);
    }
}
