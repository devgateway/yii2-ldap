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
    public function __construct(string &$description, int $position, string $msg)
    {
        $desc = substr($description, 0, 47) . "...";
        parent::__construct("$msg at position $position in: $desc");
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
        'SUP'                  => self::TYPE_ARRAY,
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
    }

    protected function getTokens($description, &$position)
    {
        // find first non-blank character, move position there
        $matches = [];
        $matched = preg_match(
            '/ *([^ ])/',
            $description,
            $matches,
            0,
            $position
        );
        if ($matched) {
            // found beginning of next token, continue
            $first_char = $matches[1];
            $position += strlen($matches[0]) - 1;
        } else {
            // no more tokens
            return false;
        }

        // return string until $char, and move past it, or throw exception
        $read_until = function ($char, $error) use ($description, &$position) {
            $end = strpos($description, $char, $position);
            if ($end === false) {
                throw new LexingException(
                    $description,
                    $position,
                    $error
                );
            }
            $token = substr(
                $description,
                $position,
                $end - $position
            );
            $position = $end + 1;
            return $token;
        };

        switch ($first_char) {
            case ')':
                $position++; // skip closing paren
                $token = false;
                break;

            case '(':
                $token = [];
                $position++; // skip opening paren
                while (true) {
                    $subtoken = $this->getTokens($description, $position);
                    if ($subtoken === false) {
                        break;
                    } elseif ($subtoken != '$') {
                        $token[] = $subtoken;
                    }
                }
                break;

            case '\'':
                $position++; // skip opening quote
                $quoted_token = $read_until('\'', 'Unbalanced single quote');

                // unescape single quote and backslash
                $quoting = [
                    '\5c' => '\\',
                    '\5C' => '\\',
                    '\27' => '\''
                ];
                $token = str_replace(array_keys($quoting), $quoting, $quoted_token);
                break;

            default:
                $token = $read_until(' ', 'Unterminated bareword');
                if (strpbrk($token, '\\\'') !== false) {
                    throw new LexingException(
                        $description,
                        $position,
                        'Bareword contains backslash or quote'
                    );
                }
        }

        return $token;
    }

    public function parseAttributeDefinition()
    {
        $properties = $this->parse(
            self::$attribute_defaults,
            self::$attribute_keywords
        );

        // validate
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

        return $properties;
    }

    public function parseObjectDefinition()
    {
        $properties = $this->parse(
            self::$objectclass_defaults,
            self::$objectclass_keywords
        );

        // validate
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

        return $properties;
    }

    protected function parse($description, $properties, &$keywords)
    {
        // unwrap long lines
        $description = str_replace("\n ", '', $description);

        // tokenize the string
        $position = 0;
        $tokens = $this->getTokens($description, $position);
        if (!is_array($tokens)) {
            $msg = 'Schema description must be enclosed in parentheses';
            throw new LexingException(
                $description,
                $position,
                $msg
            );
        }

        // OID is always the first element
        $properties['oid'] = array_shift($tokens);

        foreach ($keywords as $keyword => $type) {
            $index = array_search($keyword, $tokens);
            if ($index !== false) {
                switch ($type) {
                    case self::TYPE_BOOL:
                        $value = true;
                        break;

                    case self::TYPE_SCALAR:
                        $value = $tokens[$index + 1];
                        break;

                    case self::TYPE_ARRAY:
                        $value = $tokens[$index + 1];
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
