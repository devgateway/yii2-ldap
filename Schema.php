<?php
/**
 * Schema and related classes
 *
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://github.com/devgateway/yii2-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\Syntax;
use devgateway\ldap\OidArray;
use devgateway\ldap\Connection;
use devgateway\ldap\LexingException;

define('TYPE_BOOL', 0);
define('TYPE_SCALAR', 1);
define('TYPE_ARRAY', 2);
define('ATTRIBUTE', 0);
define('OBJECT_CLASS', 1);

/** Directory schema. */
class Schema extends OidArray
{
    /** @var int[] $attribute_keywords Types of the values expected to follow. */
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

    /** @var mixed[] $attribute_defaults Default values for attribute definitions. */
    protected static $attribute_defaults = [
        'desc'                 => null,
        'obsolete'             => false,
        'single_value'         => false,
        'collective'           => false,
        'no_user_modification' => false,
        'usage'                => 'userApplications'
    ];

    /** @var int[] $objectclass_keywords Types of the values expected to follow. */
    protected static $objectclass_keywords = [
        'NAME'                 => TYPE_ARRAY,
        'DESC'                 => TYPE_SCALAR,
        'OBSOLETE'             => TYPE_BOOL,
        'SUP'                  => TYPE_ARRAY,
        'ABSTRACT'             => TYPE_BOOL,
        'STRUCTURAL'           => TYPE_BOOL,
        'AUXILIARY'            => TYPE_BOOL,
        'MUST'                 => TYPE_ARRAY,
        'MAY'                  => TYPE_ARRAY
    ];

    /** @var mixed[] $objectclass_defaults Default values for object definitions. */
    protected static $objectclass_defaults = [
        'desc'                 => null,
        'structural'           => false, // defaults to true during validation
        'auxiliary'            => false,
        'abstract'             => false,
        'obsolete'             => false,
        'must'                 => [],
        'may'                  => []
    ];

    /** @var Connection $conn Connection object for subschema queries. */
    protected $conn;

    /**
     * Tokenize definition into keywords and their values.
     *
     * @param string $description Definition from schema.
     * @param int $position Current tokenizer position in the string.
     * @throws LexingException If tokenizing fails.
     * @return mixed[] Keywords and their values, possibly nested.
     */
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

    /**
     * Parse and validate attribute definition from schema.
     *
     * @param string $description Attribute definition.
     * @throws ParsingException If the definition violates LDAP standard.
     * @return mixed[] Attribute properties.
     */
    protected function parseAttributeDefinition($description)
    {
        $properties = $this->parse(
            $description,
            self::$attribute_defaults,
            self::$attribute_keywords
        );

        // validate
        if (!isset($properties['sup'])) {
            if (!isset($properties['syntax'])) {
                $msg = 'Either SUP or SYNTAX must be set';
                throw new ParsingException($msg);
            }

            $bad_collective = $properties['collective']
                && $properties['usage'] != 'userApplications';
            if ($bad_collective) {
                $msg = 'COLLECTIVE requires USAGE userApplications';
                throw new ParsingException($msg);
            }

            $not_operational = $properties['no_user_modification']
                && $properties['usage'] == 'userApplications';
            if ($not_operational) {
                $msg = 'NO-USER-MODIFICATION requires operational attribute';
                throw new ParsingException($msg);
            }
        }

        return $properties;
    }

    /**
     * Parse and validate object definition from schema.
     *
     * @param string $description Object definition.
     * @throws ParsingException If multiple class kinds declared.
     * @return mixed[] Object properties.
     */
    protected function parseObjectDefinition($description)
    {
        $properties = $this->parse(
            $description,
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

        if ($i == 0) {
            $properties['structural'] = true;
        }

        return $properties;
    }

    /**
     * Analyze schema definition.
     *
     * @param string $description Definition from schema.
     * @param mixed[] $properties Copy of default property values.
     * @param int[] $keywords Recognized keywords and the types they expect.
     * @throws LexingException If the definition is not parenthesized.
     * @return mixed[] Properties of the schema item.
     */
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
                    case TYPE_BOOL:
                        $value = true;
                        break;

                    case TYPE_SCALAR:
                        $value = $tokens[$index + 1];
                        break;

                    case TYPE_ARRAY:
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

    /**
     * Load predefined syntaxes, and dynamic schema definitions.
     *
     * @param Connection $conn LDAP connection object.
     * @throws \RuntimeException If loading definitions fails.
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;

        foreach (Syntax::getAll() as $oid => $syntax) {
            $this[[$oid]] = $syntax;
        }

        if (!$this->loadFromDse()) {
            throw new \RuntimeException('Unable to load schema from root DSE');
        }
    }

    /**
     * Load subschema definitions from root DSE.
     *
     * @return bool True if loading succeeded.
     */
    protected function loadFromDse()
    {
        $search_result = $this->conn->search(
            Connection::BASE,
            '', // root DSE
            '',
            ['subschemaSubentry']
        );

        foreach ($search_result as $root_dse) {
            $subschema_subentry = $root_dse['subschemaSubentry'][0];
        }

        $search_result = $this->conn->search(
            Connection::BASE,
            $subschema_subentry,
            '',
            ['attributeTypes', 'objectClasses']
        );

        foreach ($search_result as $subschema) { // just one iteration
            unset($subschema['attributeTypes']['count']);
            foreach ($subschema['attributeTypes'] as $definition) {
                $definition = $this->parseAttributeDefinition($definition);
                $definition['_type'] = ATTRIBUTE;
                $this->append($definition);
            }

            unset($subschema['objectClasses']['count']);
            foreach ($subschema['objectClasses'] as $definition) {
                $definition = $this->parseObjectDefinition($definition);
                $definition['_type'] = OBJECT_CLASS;
                $this->append($definition);
            }

            return true;
        }

        return false;
    }

    /**
     * Lazily initialize and return a schema object.
     *
     * @param string $offset OID or case-insensitive name of the element.
     * @return Syntax|AttributeDefinition|ObjectDefinition Initialized schema element.
     */
    public function offsetGet($offset)
    {
        $value = parent::offsetGet($offset);

        if (is_array($value)) {
            $offset = self::offsetMake($value);

            if ($value['_type'] == ATTRIBUTE) {
                $value = new AttributeDefinition($this, $value);
            } else {
                $value = new ObjectDefinition($this, $value);
            }

            $this->offsetSet($offset, $value);
        }

        return $value;
    }
}
