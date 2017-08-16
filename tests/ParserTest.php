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
    public function testLexer($desc, $tokens)
    {
    }

    public function escapeProvider()
    {
        return [
        ];
    }
}

