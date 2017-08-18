<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\Parser;

class MockParser extends Parser
{
    public function getTokens()
    {
        return parent::getTokens();
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
        $inline = '( 2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128} )';
        $wrapped = <<<'EOF'
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
        $padded = <<<'EOF'
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
        $cn_definition = <<<'EOF'
( 2.5.4.3 NAME ( 'cn' 'commonName' )
       DESC 'RFC2256: common name(s) for which the entity is known by'
       SUP name )
EOF;
        $esc_definition = <<<'EOF'
( 1.1.1.1.1 NAME sarcasm DESC '\27Why test backup\5crestore\27,
  they said. \27It\27ll be fine\27, they said.')
EOF;

        return [
            'inline' =>      [$business_cat, $inline],
            'wrapped' =>     [$business_cat, $wrapped],
            'padded' =>      [$business_cat, $padded],
            'multi-value' => [$common_name,  $cn_definition],
            'escaped' =>     [$escaped,      $esc_definition]
        ];
    }

    /**
     * @dataProvider badDescriptionProvider
     */
    public function testExceptions($desc, $is_attribute, $exception_name)
    {
        $parser = new Parser($desc);

        $this->expectException($exception_name);
        $parser->parse($is_attribute);
    }

    public function badDescriptionProvider()
    {
        $lexing_exception = 'devgateway\ldap\LexingException';
        $parsing_exception = 'devgateway\ldap\ParsingException';
        $missing_parens = '2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128}';

        return [
            'missing parens' => [$missing_parens, true, $lexing_exception],
        ];
    }
}

