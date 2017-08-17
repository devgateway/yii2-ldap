<?php
/**
 * Parser class
 *
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

class LexingException extends \RuntimeException
{
    public function __construct(string $msg)
    {
        $info = "Error at character {$this->position}: $msg";
        parent::__construct($info);
    }
}

class Parser
{
    protected $tokens;
    protected $properties = [];
    protected $description;
    protected $length;
    protected $position = 0;

    protected static $attribute_keywords = [
	'NAME'                 => TYPE_ARRAY,
	'DESC'                 => TYPE_SCALAR,
	'OBSOLETE'             => TYPE_BOOL,
	'SUP'                  => TYPE_SCALAR,
	'EQUALITY'             => TYPE_SCALAR,
	'ORDERING'             => TYPE_SCALAR,
	'SUBSTR'               => TYPE_SCALAR,
	'SYNTAX'               => TYPE_SCALAR,
	'SINGLE-VALUE'         => TYPE_BOOL,
	'COLLECTIVE'           => TYPE_BOOL,
	'NO-USER-MODIFICATION' => TYPE_BOOL,
	'USAGE'                => TYPE_SCALAR
    ];
    protected static $objectclass_keywords = [
	'NAME'                 => TYPE_ARRAY,
	'DESC'                 => TYPE_SCALAR,
	'OBSOLETE'             => TYPE_BOOL,
	'SUP'                  => TYPE_SCALAR,
	'ABSTRACT'             => TYPE_BOOL,
	'STRUCTURAL'           => TYPE_BOOL,
	'AUXILIARY'            => TYPE_BOOL,
        'MUST'                 => TYPE_ARRAY,
        'MAY'                  => TYPE_ARRAY
    ];

    const TYPE_BOOL =   0;
    const TYPE_SCALAR = 1;
    const TYPE_ARRAY =  2;

    public function __construct(string $description)
    {
        // unwrap long lines
        $this->description = str_replace("\n ", '', $description);
        $this->length = strlen($this->description);

        $this->tokens = $this->getToken();
        if (!is_array($this->tokens)) {
            $msg = 'schema description must be enclosed in paretheses';
            throw new LexingException($msg);
        }
    }

    protected function getToken()
    {
        // find first non-blank character, move position there
        $matches = [];
        $matched = preg_match(
            '/ *([^ ])/',
            $this->description,
            $matches,
            0,
            $this->position
        );
        if ($matched) {
            // found beginning of next token, continue
            $first_char = $matches[1];
            $this->position += strlen($matches[0]) - 1;
        } else {
            // no more tokens
            return false;
        }

        // return string until $char, and move past it, or throw exception
        $read_until = function ($char, $error) {
            $end = strpos($this->description, $char, $this->position);
            if ($end === false) {
                throw new LexingException($error);
            }
            $token = substr(
                $this->description,
                $this->position,
                $end - $this->position
            );
            $this->position = $end + 1;
            return $token;
        };

        switch ($first_char) {
            case ')':
                $this->position++; // skip paren
                $token = false;
                break;

            case '(':
                $token = [];
                $this->position++; // skip opening paren
                while (true) {
                    $subtoken = $this->getToken();
                    if ($subtoken === false) {
                        break;
                    } else {
                        $token[] = $subtoken;
                    }
                }
                break;

            case '\'':
                $this->position++; // skip opening quote
                $quoted_token = $read_until('\'', 'unbalanced single quote');

                // unescape single quote and backslash
                $quoting = [
                    '\5c' => '\\',
                    '\5C' => '\\',
                    '\27' => '\''
                ];
                $token = str_replace(array_keys($quoting), $quoting, $quoted_token);
                break;

            default:
                $token = $read_until(' ', 'unterminated bareword');
        }

        return $token;
    }

    public function __get(string $name)
    {
        //$name = strtolower(str_replace('-', '_', $name));
        return array_key_exists($name, $this->properties) ? $this->properties : null;
    }

    public function parseAttribute()
    {
        $properties = [];
        foreach (self::$attribute_keywords
    }
}
