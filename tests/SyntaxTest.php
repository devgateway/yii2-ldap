<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\Syntax;

class TestSyntax extends TestCase
{
    /**
     * @dataProvider gtProvider
     */
    public function testGTtoDT($gt, $expected)
    {
        $date_time = Syntax::parseGeneralizedTime($gt);
        $timestamp = intval($date_time->format('U'));
        $this->assertEquals($timestamp, $expected);
    }

    public function gtProvider()
    {
        return [
            'zulu' => ['199412161032Z', 787573920],
            'no minute' => ['1994121610Z', 787572000],
            'offset' => ['199412160532-0500', 787573920],
            'frac second' => ['20170401113245,9Z', 1491046366],
            'frac minute' => ['201704011132.5Z', 1491046350],
            'frac hour' => ['2017040111.5Z', 1491046200]
        ];
    }
}

