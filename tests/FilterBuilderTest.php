<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\FilterBuilder;

class FilterBuilderTest extends TestCase
{
    public function testOr()
    {
        $test_or = FilterBuilder::_or(['objectClass' => ['device', 'user']]);
        $this->assertEquals($test_or, "(|(objectClass=device)(objectClass=user))");
    }
    public function testAnd()
    {
        $test_and_one = FilterBuilder::_and(['objectClass' => ['device', 'user']]);
        $test_and_two = FilterBuilder::_and(['objectClass' => ['x', 'y']]);

        $this->assertEquals($test_and_one, "(&(objectClass=device)(objectClass=user))");
        $this->assertEquals($test_and_two, "(&(objectClass=x)(objectClass=y))");
    }
    public function testNot()
    {
        $test_not = FilterBuilder::_not(['drink' => 'liquor']);
        $this->assertEquals($test_not, "(!(drink=liquor))");
    }
    public function testCombined()
    {
        $test_and_one = FilterBuilder::_and(['objectClass' => ['device', 'user']]);
        $test_and_two = FilterBuilder::_and(['objectClass' => ['x', 'y']]);
        $test_or = FilterBuilder::_or(['objectClass' => ['device', 'user']]);
        $test_or_combined = FilterBuilder::_or($test_and_one, $test_and_two);

        $this->assertEquals($test_or_combined, "(|(&(objectClass=device)(objectClass=user))(&(objectClass=x)(objectClass=y)))");
    }

}

