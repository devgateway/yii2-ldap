<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\OidArray;

define('ATTR_GN_OID',   '2.5.4.42');
define('ATTR_GN_LONG',  'givenName');
define('ATTR_GN_SHORT', 'gn');

define('ATTR_SN_OID',   '2.5.4.4');
define('ATTR_SN_LONG',  'surname');
define('ATTR_SN_SHORT', 'sn');

class MockOidArray extends OidArray
{
    public static function offsetMake($value)
    {
        return parent::offsetMake($value);
    }
}

class TestOidArray extends TestCase
{
    protected $given_name = 'John';
    protected $surname = 'Doe';

    /**
     * @dataProvider personProvider
     */
    public function testOffsetSetGet($oa)
    {
        $this->assertEquals($this->surname, $oa[ATTR_SN_OID]);
        $this->assertEquals($this->surname, $oa[ATTR_SN_LONG]);
        $this->assertEquals($this->surname, $oa[ATTR_SN_SHORT]);
    }

    /**
     * @dataProvider oidProvider
     */
    public function testValidation($oid_array, $oid_valid)
    {
        $oa = new OidArray();

        if (!$oid_valid) {
            $exception_name = 'UnexpectedValueException';

            if (method_exists($this, 'expectException')) {
                $this->expectException($exception_name);
            } else {
                $this->setExpectedException($exception_name);
            }
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

        unset($oa[ATTR_SN_LONG]);
        $this->assertFalse(isset($oa[ATTR_SN_LONG]));
    }

    public function personProvider()
    {
        $oa = new OidArray();

        $given_name = array(ATTR_GN_OID, ATTR_GN_LONG, ATTR_GN_SHORT);
        $surname =    array(ATTR_SN_OID, ATTR_SN_LONG, ATTR_SN_SHORT);

        $oa[$given_name] = $this->given_name;
        $oa[$surname] =    $this->surname;

        return ['person' => [$oa]];
    }

    /**
     * @dataProvider personProvider
     */
    public function testIterator($oa)
    {
        $props = array();
        foreach($oa as $key => $val) {
            $props[$key] = $val;
        }

        $this->assertArrayHasKey(ATTR_GN_LONG, $props);
        $this->assertArrayHasKey(ATTR_SN_LONG, $props);

        $this->assertEquals($this->given_name, $props[ATTR_GN_LONG]);
        $this->assertEquals($this->surname,    $props[ATTR_SN_LONG]);
    }

    public function testSameObject()
    {
        $oa = new OidArray();
        $value = new \Exception;
        $given_name = array(ATTR_GN_OID, ATTR_GN_LONG, ATTR_GN_SHORT);
        $oa[$given_name] = $value;

        $this->assertTrue($oa[ATTR_GN_OID] === $oa[ATTR_GN_SHORT]);
        $this->assertTrue($oa[ATTR_GN_OID] === $oa[ATTR_GN_LONG]);
    }

    /**
     * @dataProvider valueProvider
     */
    public function testOffsetMake($value, $expected_offset)
    {
        $offset = MockOidArray::offsetMake($value);

        $this->assertEquals($expected_offset, $offset);
    }

    public function valueProvider()
    {
        return [
            'OID and names' => [
                [
                    'obsolete' => false,
                    'name' => [
                        ATTR_GN_LONG,
                        ATTR_GN_SHORT
                    ],
                    'single_value' => false,
                    'oid' => ATTR_GN_OID
                ],
                [
                    ATTR_GN_OID,
                    ATTR_GN_LONG,
                    ATTR_GN_SHORT
                ]
            ],
            'Just OID' => [
                [
                    'oid' => ATTR_GN_OID,
                    'answer' => 42
                ],
                [ATTR_GN_OID]
            ]
        ];
    }

    /**
     * @dataProvider valueProvider
     */
    public function testAppend($value, $ignored)
    {
        $oa = new OidArray();
        $oa->append($value);

        $this->assertEquals($value, $oa[ATTR_GN_OID]);
    }
}

