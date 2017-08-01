<?php
use PHPUnit\Framework\TestCase;
use ldap\OidArray;

class TestOidArray extends TestCase
{
    protected $gn;
    protected $sn;

    public function setUp()
    {
        $this->gn = array('2.5.4.42', 'givenName', 'gn');
        $this->sn = array('2.5.4.4', 'surname', 'sn');
    }

    public function testOffsetSetGet()
    {
        $first_name = 'John';
        $last_name = 'Doe';

        $oa = new OidArray();
        $oa[$this->gn] = $first_name;
        $oa[$this->sn] = $last_name;

        $by_oid = $oa['2.5.4.4'];
        $by_attr_name = $oa['surname'];
        $by_attr_alias = $oa['sn'];

        $this->assertEquals($last_name, $by_oid);
        $this->assertEquals($last_name, $by_attr_name);
        $this->assertEquals($last_name, $by_attr_alias);
    }

    public function testBadOid() {
        $bad_oid = array('9.9.9', 'Foobar');
        $oa = new OidArray();

        $this->expectException('UnexpectedValueException');
        $oa[$bad_oid] = 42;
    }
}
