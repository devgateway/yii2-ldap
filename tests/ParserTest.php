<?php
use PHPUnit\Framework\TestCase;
use devgateway\ldap\Parser;

define('LEXING_EXCEPTION', 'devgateway\ldap\LexingException');
define('PARSING_EXCEPTION', 'devgateway\ldap\ParsingException');


class MockParser extends Parser
{
    public function getTokens($description, &$position)
    {
        return parent::getTokens($description, $position);
    }
}

class ParserTest extends TestCase
{
    /**
     * @dataProvider descriptionProvider
     */
    public function testLexer($expected, $description)
    {
        // unwrap long lines
        $description = str_replace("\n ", '', $description);

        $parser = new MockParser();
        $position = 0;
        $tokens = $parser->getTokens($description, $position);

        $this->assertEquals($expected, $tokens);
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
     * @dataProvider badAttributeDescriptionProvider
     */
    public function testAttributeExceptions($desc, $exception_name)
    {
        $parser = new Parser();

        $this->expectException($exception_name);
        $parser->parseAttributeDefinition($desc);
    }

    public function badAttributeDescriptionProvider()
    {
        $missing_parens = '2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128}';
        $unbalanced_quote = '( 2.5.4.15 NAME \'businessCategory DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128} )';
        $bareword = '( 2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128})';
        $bareword_quote = '( 2.5.4.15 NAME businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128} )';
        $bareword_bkslash = '( 2.5.\4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128} )';
        $no_syntax = '( 2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch )';
        $collective_usage = '( 2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128} COLLECTIVE USAGE directoryOperation )';
        $no_user_mod = '( 2.5.4.15 NAME \'businessCategory\' DESC \'RFC2256: business cat' .
            'egory\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1' .
            '.1466.115.121.1.15{128} NO-USER-MODIFICATION )';

        return [
            'missing parens' =>        [$missing_parens,   LEXING_EXCEPTION],
            'unbalanced quote' =>      [$unbalanced_quote, LEXING_EXCEPTION],
            'unterminated bareword' => [$bareword,         LEXING_EXCEPTION],
            'bareword quote' =>        [$bareword_quote,   LEXING_EXCEPTION],
            'bareword backslash' =>    [$bareword_bkslash, LEXING_EXCEPTION],
            'no syntax no sup' =>      [$no_syntax,        PARSING_EXCEPTION],
            'collective usage' =>      [$collective_usage, PARSING_EXCEPTION],
            'no user modification' =>  [$no_user_mod,      PARSING_EXCEPTION],
        ];
    }

    /**
     * @dataProvider badObjectDescriptionProvider
     */
    public function testObjectExceptions($desc, $exception_name)
    {
        $parser = new Parser();

        $this->expectException($exception_name);
        $parser->parseObjectDefinition($desc);
    }

    public function badObjectDescriptionProvider()
    {
        $two_kinds = '( 2.5.6.3 NAME \'locality\' DESC \'RFC2256: a locality\' SUP' .
            ' top STRUCTURAL ABSTRACT MAY ( street $ st $ l $ description ) )';

        return [
            'two class kinds' => [$two_kinds, PARSING_EXCEPTION]
        ];
    }

    public function testObjectParsing()
    {
        $description = <<<'EOF'
( 2.5.6.14 NAME 'device'
  DESC 'RFC2256: a device'
  SUP top STRUCTURAL
  MUST cn
  MAY ( serialNumber $ seeAlso $ owner $ ou $ o $ l $ description ) )
EOF;
        $expected = json_decode(
            '{"structural":true,"auxiliary":false,"abstract":false' .
            ',"obsolete":false,"must":["cn"],"may":["serialNumber","seeAlso","owner","ou","o","l"' .
            ',"description"],"oid":"2.5.6.14","name":["device"],"desc":"RFC2256: a device","sup":' .
            '["top"]}',
            true
        );

        $parser = new Parser();
        $actual = $parser->parseAttributeDefinition($description);

        $this->assertEquals($expected, $actual);
    }


    public function testAttributeParsing()
    {
        $description = <<<'EOF'
( 2.5.4.50 NAME 'uniqueMember'
  DESC 'RFC2256: unique member of a group'
  EQUALITY uniqueMemberMatch
  SYNTAX 1.3.6.1.4.1.1466.115.121.1.34 )
EOF;
        $expected = json_decode(
            '{"obsolete":false,"single_value":false,"collective":false,' .
            '"no_user_modification":false,"usage":"userApplications","oid":"2.5.4.50","name":["un' .
            'iqueMember"],"desc":"RFC2256: unique member of a group","equality":"uniqueMemberMatc' .
            'h","syntax":"1.3.6.1.4.1.1466.115.121.1.34"}',
            true
        );
        $parser = new Parser();
        $actual = $parser->parseAttributeDefinition($description);

        $this->assertEquals($expected, $actual);
    }
}

