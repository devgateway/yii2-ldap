<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\Parser;

class MockParser extends Parser
{
    public function getTokens()
    {
        return $this->tokens;
    }
}

class ParserTest extends TestCase
{
    /**
     * @dataProvider descriptionProvider
     */
    public function testLexer($expected, $desc)
    {
        $parser = new MockParser($desc);
        $this->assertEquals($expected, $parser->getTokens());
    }

    public function descriptionProvider()
    {
        $business_cat = json_decode(
            '["2.5.4.15","NAME","businessCategory","DESC","RFC2256: business category","EQUALITY"' .
            ',"caseIgnoreMatch","SUBSTR","caseIgnoreSubstringsMatch", "SYNTAX","1.3.6.1.4.1.1466.' .
            '115.121.1.15{128}"]'
        );
        $common_name = json_decode('["2.5.4.3","NAME",["cn","commonName"],"DESC","RFC2256: common' .
            ' name(s) for which the entity is known by","SUP","name"]');

        return [
            'inline' => [
                $business_cat,
                '( 2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business category\' EQUALIT' .
                'Y caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.1' .
                '21.1.15{128} )'
            ],
            'wrapped' => [$business_cat, <<<'EOF'
( 2.5.
 4.15 NAME 'businessCat
 egory'
   DESC '
 RFC2256: business category
 '
  EQUALITY
   caseIgnoreMatch
    SUBSTR caseIgno
 reSubstringsMatch
  SYNTAX 1.3.6.1.4.1.1466
 .115.121.1.15{128} )
EOF
            ],
            'wrapped & padded' => [$business_cat, <<<'EOF'
(   2.5.
 4.15    NAME 'businessCat
 egory'
   DESC    '
 RFC2256: business category
 '   
  EQUALITY
   caseIgnoreMatch
    SUBSTR                            caseIgno
 reSubstringsMatch
  SYNTAX     1.3.6.1.4.1.1466
 .115.121.1.15{128}    )
EOF
            ],
            'multi-value' => [$common_name, <<<'EOF'
( 2.5.4.3 NAME ( 'cn' 'commonName' )
       DESC 'RFC2256: common name(s) for which the entity is known by'
       SUP name )
EOF
            ],
        ];
    }
}

