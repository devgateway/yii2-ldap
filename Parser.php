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
    public function __construct(int $position, string $msg)
    {
        parent::__construct("Error at character $position: $msg");
    }
}

class ParsingException extends \RuntimeException
{
}

class Parser
{
    const TYPE_BOOL =   0;
    const TYPE_SCALAR = 1;
    const TYPE_ARRAY =  2;

    protected $description;
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
        'no_user_modification' => false,
        'usage'                => 'userApplications'
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
    }

    protected function getTokens()
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
                throw new LexingException($this->position, $error);
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
                    $subtoken = $this->getTokens();
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
                if (strpbrk($token, '\\\'') !== false) {
                    throw new LexingException(
                        $this->position,
                        'bareword contains backslash or quote'
                    );
                }
        }

        return $token;
    }

    public function parse($is_attribute)
    {
        $tokens = $this->getTokens();
        if (!is_array($tokens)) {
            $msg = 'schema description must be enclosed in parentheses';
            throw new LexingException($this->position, $msg);
        }

        if ($is_attribute) {
            $properties = self::$attribute_defaults;
            $keywords = &self::$attribute_keywords;
        } else {
            $properties = self::$objectclass_defaults;
            $keywords = &self::$objectclass_keywords;
        }

        // OID is always the first element
        $properties['oid'] = array_shift($tokens);

        foreach ($keywords as $keyword => $type) {
            $position = array_search($keyword, $tokens);
            if ($position !== false) {
                switch ($type) {
                    case self::TYPE_BOOL:
                        $value = true;
                        break;

                    case self::TYPE_SCALAR:
                        $value = $tokens[$position + 1];
                        break;

                    case self::TYPE_ARRAY:
                        $value = $tokens[$position + 1];
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                }

                $index = strtolower(str_replace('-', '_', $keyword));
                $properties[$index] = $value;
            }
        }

        // validate properties
        if ($is_attribute) {
            if (!isset($properties['sup'])) {
                if (!isset($properties['syntax'])) {
                    $msg = 'Either SUP or SYNTAX must be set';
                    throw new ParsingException($msg);
                }
                if (
                    $properties['collective'] &&
                    $properties['usage'] != 'userApplications'
                ) {
                    $msg = 'COLLECTIVE requires USAGE userApplications';
                    throw new ParsingException($msg);
                }
                if (
                    $properties['no_user_modification'] &&
                    $usage = 'userApplications'
                ) {
                    $msg = 'NO-USER-MODIFICATION requires operational attribute';
                    throw new ParsingException($msg);
                }
            }
        } else {
            $i = 0;
            $kinds = ['structural', 'abstract', 'auxiliary'];
            foreach ($kinds as $kind) {
                if ($properties[$kind]) {
                    $i++;
                }
                if ($i > 1) {
                    $msg = 'Object class must be STRUCTURAL, ABSTRACT, or AUXILIARY';
                    throw new ParsingException($msg);
                }
            }
        }

        return $properties;
    }
}
