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

    public function testOidOnly()
    {
        $oid = '1.2.3.4.5';
        $just_oid = array($oid);
        $oa = new OidArray();
        $oa[$just_oid] = 42;

        $this->assertEquals(42, $oa[$oid]);
    }

    /**
     * @dataProvider oidProvider
     */
    public function testValidation($oid_array, $oid_valid)
    {
        $oa = new OidArray();

        if (!$oid_valid) {
            $this->expectException('UnexpectedValueException');
        }

        $oa[$oid_array] = 42;
    }

    public function oidProvider()
    {
        return [
            'just OID'       => [['2.84.73'],                       true],
            'ends with dot'  => [['1.2.3.', 'something'],           false],
            'two names'      => [['1.2.3', 'something', 'smth'],    true],
            '1st number'     => [['9.9.9', 'incorrect'],            false],
            'not dotted int' => [['foo', 'bar'],                    false],
            'ISO node'       => [['1'],                             true]
        ];
    }

    public function testExistence()
    {
        $first_name = 'John';
        $last_name = 'Doe';

        $oa = new OidArray();
        $oa[$this->gn] = $first_name;

        $this->assertTrue(isset($oa['GIVENNAME']));
        $this->assertFalse(isset($oa['userPassword']));
    }
}
