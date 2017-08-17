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
    const TYPE_BOOL =   0;
    const TYPE_SCALAR = 1;
    const TYPE_ARRAY =  2;

    protected $tokens;
    protected $description;
    protected $length;
    protected $position = 0;

    protected static $attribute_keywords = [
        'NAME'                 => self::TYPE_ARRAY,
        'DESC'                 => self::TYPE_SCALAR,
        'OBSOLETE'             => self::TYPE_BOOL,
        'SUP'                  => self::TYPE_SCALAR,
        'EQUALITY'             => self::TYPE_SCALAR,
        'ORDERING'             => self::TYPE_SCALAR,
        'SUBSTR'               => self::TYPE_SCALAR,
        'SYNTAX'               => self::TYPE_SCALAR,
        'SINGLE-VALUE'         => self::TYPE_BOOL,
        'COLLECTIVE'           => self::TYPE_BOOL,
        'NO-USER-MODIFICATION' => self::TYPE_BOOL,
        'USAGE'                => self::TYPE_SCALAR
    ];
    protected static $attribute_defaults = [
        'obsolete'             => false,
        'single_value'         => false,
        'collective'           => false,
        'no_user_modification' => false
    ];
    protected static $objectclass_keywords = [
        'NAME'                 => self::TYPE_ARRAY,
        'DESC'                 => self::TYPE_SCALAR,
        'OBSOLETE'             => self::TYPE_BOOL,
        'SUP'                  => self::TYPE_SCALAR,
        'ABSTRACT'             => self::TYPE_BOOL,
        'STRUCTURAL'           => self::TYPE_BOOL,
        'AUXILIARY'            => self::TYPE_BOOL,
        'MUST'                 => self::TYPE_ARRAY,
        'MAY'                  => self::TYPE_ARRAY
    ];
    protected static $objectclass_defaults = [
        'structural'           => true,
        'auxiliary'            => false,
        'abstract'             => false,
        'obsolete'             => false,
        'must'                 => [],
        'may'                  => []
    ];

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

    public function parse($is_attribute)
    {
        if ($is_attribute) {
            $properties = self::$attribute_defaults;
            $keywords = &self::$attribute_keywords;
        } else {
            $properties = self::$objectclass_defaults;
            $keywords = &self::$objectclass_keywords;
        }

        // OID is always the first element
        $properties['oid'] = array_shift($this->tokens);

        foreach ($keywords as $keyword => $type) {
            $position = array_search($keyword, $this->tokens);
            if ($position !== false) {
                switch ($type) {
                    case self::TYPE_BOOL:
                        $value = true;
                        break;

                    case self::TYPE_SCALAR:
                        $value = $this->tokens[$position + 1];
                        break;

                    case self::TYPE_ARRAY:
                        $value = $this->tokens[$position + 1];
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                }

                $index = strtolower(str_replace('-', '_', $keyword));
                $properties[$index] = $value;
            }
        }

        return $properties;
    }
}
