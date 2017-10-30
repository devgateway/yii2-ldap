<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\CompoundObject;
use devgateway\ldap\SimpleObject;

class MockableSimpleObject extends SimpleObject
{
    public function __construct($mock)
    {
        foreach ($mock as $name => $value) {
            $offset = ['1.2.3', $name];
            $this[$offset] = $value;
        }
    }
}

class MockableCompoundObject extends CompoundObject
{
    public function __construct($mock)
    {
        foreach ($mock as $class_name => $attrs) {
            $offset = ['1.2.3', $class_name];
            $this[$offset] = new MockableSimpleObject($attrs);
        }
    }
}

class CompoundObjectTest extends TestCase
{
    /**
     * @dataProvider mockProvider
     */
    public function testSort($unsorted, $expected)
    {
        $comp_object = new MockableCompoundObject($unsorted);
        $sorted = $comp_object->toArray();
        $this->assertTrue($sorted === $expected);
    }

    public function mockProvider()
    {
        return [ // array of test cases
            [ // array of arguments
                ['inetOrgPerson' => [
                    'surname' => 'Doe',
                    'givenName' => 'John',
                    'userId' => 42,
                    'mail' => 'john@example.net'
                ]],
                ['inetOrgPerson' => [
                    'givenName' => 'John',
                    'mail' => 'john@example.net',
                    'surname' => 'Doe',
                    'userId' => 42
                ]]
            ],
        ];
    }
}

