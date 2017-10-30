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
        // PHP interpreter will sort the array during initialization,
        // so we set each member separately in order to randomize it
        $unsorted = [];
        $unsorted['posixAccount'] = [
            'cn' => 'jdoe',
            'userPassword' => 'hunter2',
            'homeDirectory' => '/home/jdoe',
            'uid' => 'jdoe'
        ];
        $unsorted['shadowAccount'] = [
            'uid' => 'jdoe', // duplicate uid
            'userPassword' => 'hunter2', // duplicate userPassword
        ];
        $unsorted['inetOrgPerson'] = [
            'surname' => 'Doe',
            'givenName' => 'John',
            'employeeNumber' => 42,
            'mail' => 'john@example.net'
        ];

        $sorted = [
            'inetOrgPerson' => [
                'employeeNumber' => 42,
                'givenName' => 'John',
                'mail' => 'john@example.net',
                'surname' => 'Doe'
            ],
            'posixAccount' => [
                'cn' => 'jdoe',
                'homeDirectory' => '/home/jdoe',
                'uid' => 'jdoe',
                'userPassword' => 'hunter2'
            ],
            'shadowAccount' => []
        ];

        return [
            [$unsorted, $sorted]
        ];
    }
}

