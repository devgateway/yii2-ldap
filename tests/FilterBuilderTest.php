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

    public function testGTE()
    {
        $GTE = FilterBuilder::_gte(['age' => 21]);
        $OR= FilterBuilder::_or(['drink' => ['whiskey', 'gin', 'rum']]);
        $COMBINED_ONE = FilterBuilder::_and($OR,$GTE);
        $COMBINED_TWO = FilterBuilder::_and(['objectClass' => ['x', 'y']],$GTE);

        $this->assertEquals($GTE, "(age>=21)");
        $this->assertEquals($COMBINED_ONE, "(&(|(drink=whiskey)(drink=gin)(drink=rum))(age>=21))");
        $this->assertEquals($COMBINED_TWO, "(&(objectClass=x)(objectClass=y)(age>=21))");

    }

    public function testLTE()
    {
        $GTE = FilterBuilder::_lte(['age' => 21]);
        $OR= FilterBuilder::_or(['drink' => ['whiskey', 'gin', 'rum']]);
        $COMBINED_ONE = FilterBuilder::_and($OR,$GTE);
        $COMBINED_TWO = FilterBuilder::_and(['objectClass' => ['x', 'y']],$GTE);

        $this->assertEquals($GTE, "(age<=21)");
        $this->assertEquals($COMBINED_ONE, "(&(|(drink=whiskey)(drink=gin)(drink=rum))(age<=21))");
        $this->assertEquals($COMBINED_TWO, "(&(objectClass=x)(objectClass=y)(age<=21))");

    }

    public function testEach()
    {
        $each = FilterBuilder::_each(['givenName', 'surname', 'uid'], 'adam');
        $this->assertEquals($each, "(&(givenName=adam)(surname=adam)(uid=adam))");
    }

    public function testEither()
    {
        $either = FilterBuilder::_either(['givenName', 'surname', 'uid'], 'adam');
        $this->assertEquals($either, "(|(givenName=adam)(surname=adam)(uid=adam))");
    }

    public function testAny()
    {
        $any = FilterBuilder::_any(['givenName', 'surname', 'uid'], " adam  smi");
        $this->assertEquals($any, "(|(givenName=*adam*)(givenName=*smi*)(surname=*adam*)(surname=*smi*)(uid=*adam*)(uid=*smi*))");
    }

}

