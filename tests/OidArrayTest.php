<?php
use PHPUnit\Framework\TestCase;
use ldap\OidArray;

class TestOidArray extends TestCase
{
    protected $gn = array('2.5.4.42', 'givenName', 'gn');
    protected $sn = array('2.5.4.4', 'surname', 'sn');
    protected $first_name = 'John';
    protected $last_name = 'Doe';

    /**
     * @dataProvider personProvider
     */
    public function testOffsetSetGet($oa)
    {
        $this->assertEquals($this->last_name, $oa['2.5.4.4']);
        $this->assertEquals($this->last_name, $oa['surname']);
        $this->assertEquals($this->last_name, $oa['sn']);
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

    /**
     * @dataProvider personProvider
     */
    public function testExistence($oa)
    {
        $this->assertTrue(isset($oa['GIVENNAME']));
        $this->assertFalse(isset($oa['userPassword']));

        unset($oa['surname']);
        $this->assertFalse(isset($oa['surname']));
    }

    public function personProvider()
    {
        $oa = new OidArray();
        $oa[$this->gn] = $this->first_name;
        $oa[$this->sn] = $this->last_name;

        return [[$oa]];
    }
}
