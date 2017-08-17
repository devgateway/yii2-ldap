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
        $common_name = json_decode(
            '["2.5.4.3","NAME",["cn","commonName"],"DESC","RFC2256: common name(s) for which the ' .
            'entity is known by","SUP","name"]'
        );
        $escaped = json_decode(
            '["1.1.1.1.1","NAME","sarcasm","DESC","\'Why test backup\\\\restore\', they said. \'' .
            'It\'ll be fine\', they said."]'
        );
        $business_cat_inline = '( 2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128} )';
        $business_cat_wrapped = <<<'EOF'
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
EOF;
        $business_cat_padded = <<<'EOF'
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
EOF;
        $common_name_def = <<<'EOF'
( 2.5.4.3 NAME ( 'cn' 'commonName' )
       DESC 'RFC2256: common name(s) for which the entity is known by'
       SUP name )
EOF;
        $escaped_def = <<<'EOF'
( 1.1.1.1.1 NAME sarcasm DESC '\27Why test backup\5crestore\27,
  they said. \27It\27ll be fine\27, they said.')
EOF;

        return [
            'inline' =>      [$business_cat, $business_cat_inline],
            'wrapped' =>     [$business_cat, $business_cat_wrapped],
            'padded' =>      [$business_cat, $business_cat_padded],
            'multi-value' => [$common_name,  $common_name_def],
            'escaped' =>     [$escaped,      $escaped_def]
        ];
    }
}

