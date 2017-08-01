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
        $expected = 'Foobar';

        $oa = new OidArray();
        $oa[$this->gn] = $expected;
        $by_oid = $oa['2.5.4.42'];
        $by_attr_name = $oa['givenName'];
        $by_attr_alias = $oa['gn'];

        $this->assertEquals($expected, $by_oid);
        $this->assertEquals($expected, $by_attr_name);
        $this->assertEquals($expected, $by_attr_alias);
    }

    public function testBadOid() {
        $bad_oid = array('9.9.9', 'Foobar');
        $oa = new OidArray();

        $this->expectException('UnexpectedValueException');
        $oa[$bad_oid] = 42;
    }
}
