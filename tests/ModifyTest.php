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
        $this->entry = [
            'cn' => "test_mod",
            'memorySize' => "2",
            'virtualCPU' => "2",
            'objectClass' => ["virtualMachine", "device", "ansibleHost"]
        ];
        $this->dn = sprintf('cn=%s,%s', $this->entry['cn'], $base);

        $this->conn = new Connection($config);

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

        $result = ldap_add($this->canonical_conn, $this->dn, $this->entry);
        if (!$result) {
            throw new \Exception('Can\'t add');
        }
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
        $mod_single['description'] = "added description";
        $mod_single_filter = sprintf(
            '(&(objectClass=virtualMachine)(cn=test_mod)(description=%s))',
            $mod_single['description']
        );

        $mod_extra['cn'] = "tsm";
        $mod_extra_filter = sprintf(
            '(&(objectClass=virtualMachine)(cn=test_mod)(cn=%s))',
            $mod_extra['cn']
        );

        $mod_multiple = [
            'priority' => "3",
            'description' => "test_add ldap object"
        ];
        $mod_multiple_filter = sprintf(
            '(&(objectClass=virtualMachine)(cn=test_mod)(priority=%s)(description=%s))',
            $mod_multiple['priority'],
            $mod_multiple['description']
        );

        $mod_single_orig['description'] = "changed description";
        $mod_extra_orig['cn'] = "tsmorig";
        $mod_multiple_orig = [
            'priority' => "5",
            'description' => "test_add_orig ldap object"
        ];
        $mod_extra_repl['cn'] = ["test_mod", "tsm"];
        $mod_extra_repl_filter = sprintf(
            '(&(objectClass=virtualMachine)(cn=%s)(cn=%s))',
            $mod_extra_repl['cn'][0],
            $mod_extra_repl['cn'][1]
        );

        $mod_entry_new = [
            'cn' => "test_mod",
            'description' => "test_add ldap object",
            'memorySize' => "7",
            'virtualCPU' => "7",
            'objectClass' => ["virtualMachine", "device", "ansibleHost"]
        ];
        $mod_entry_new_filter = sprintf(
            '(&(objectClass=virtualMachine)(cn=%s)(description=%s)(memorySize=%s)(virtualCPU=%s))',
            $mod_entry_new['cn'],
            $mod_entry_new['description'],
            $mod_entry_new['memorySize'],
            $mod_entry_new['virtualCPU']
        );

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
        $result = ldap_delete($this->canonical_conn, $this->dn);
        if (!$result) {
            throw new \Exception('Can\'t delete');
        }
        ldap_unbind($this->canonical_conn);
        unset($this->conn);
    }
}
