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

    public function testCreate()
    {
        $oa = new OidArray();
        $this->assertInstanceOf('ldap\\OidArray', $oa);
    }
}
