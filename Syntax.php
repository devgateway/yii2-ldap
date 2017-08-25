<?php
/**
 * Syntax classes
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @link https://tools.ietf.org/html/rfc4517
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

define('SYNTAX_ATTRIBUTE_TYPE_DESCRIPTION',     3);
define('SYNTAX_BIT_STRING',                     6);
define('SYNTAX_BOOLEAN',                        7);
define('SYNTAX_COUNTRY_STRING',                 11);
define('SYNTAX_DELIVERY_METHOD',                14);
define('SYNTAX_DIRECTORY_STRING',               15);
define('SYNTAX_DIT_CONTENT_RULE_DESCRIPTION',   16);
define('SYNTAX_DIT_STRUCTURE_RULE_DESCRIPTION', 17);
define('SYNTAX_DN',                             12);
define('SYNTAX_ENHANCED_GUIDE',                 21);
define('SYNTAX_FACSIMILE_TELEPHONE_NUMBER',     22);
define('SYNTAX_FAX',                            23);
define('SYNTAX_GENERALIZED_TIME',               24);
define('SYNTAX_GUIDE',                          25);
define('SYNTAX_IA5_STRING',                     26);
define('SYNTAX_INTEGER',                        27);
define('SYNTAX_JPEG',                           28);
define('SYNTAX_LDAP_SYNTAX_DESCRIPTION',        54);
define('SYNTAX_MATCHING_RULE_DESCRIPTION',      30);
define('SYNTAX_MATCHING_RULE_USE_DESCRIPTION',  31);
define('SYNTAX_NAME_AND_OPTIONAL_UID',          34);
define('SYNTAX_NAME_FORM_DESCRIPTION',          35);
define('SYNTAX_NUMERIC_STRING',                 36);
define('SYNTAX_OBJECT_CLASS_DESCRIPTION',       37);
define('SYNTAX_OCTET_STRING',                   40);
define('SYNTAX_OID',                            38);
define('SYNTAX_OTHER_MAILBOX',                  39);
define('SYNTAX_POSTAL_ADDRESS',                 41);
define('SYNTAX_PRINTABLE_STRING',               44);
define('SYNTAX_SUBSTRING_ASSERTION',            58);
define('SYNTAX_TELEPHONE_NUMBER',               50);
define('SYNTAX_TELETEX_TERMINAL_IDENTIFIER',    51);
define('SYNTAX_TELEX_NUMBER',                   52);
define('SYNTAX_UTC_TIME',                       53);

class SyntaxException extends \RuntimeException
{
    public function __construct(string $serialized, array $expected = [])
    {
        if (empty($expected)) {
            $msg = "Value '$serialized' invalid per syntax";
        } else {
            $values = implode(', ', $expected);
            $msg = "Value '$serialized' invalid. Expected one of: $values";
        }

        parent::__construct($msg);
    }
}

class Syntax
{
    protected $syntax_type;
    protected static $types = [
        SYNTAX_ATTRIBUTE_TYPE_DESCRIPTION,
        SYNTAX_BIT_STRING,
        SYNTAX_BOOLEAN,
        SYNTAX_COUNTRY_STRING,
        SYNTAX_DELIVERY_METHOD,
        SYNTAX_DIRECTORY_STRING,
        SYNTAX_DIT_CONTENT_RULE_DESCRIPTION,
        SYNTAX_DIT_STRUCTURE_RULE_DESCRIPTION,
        SYNTAX_DN,
        SYNTAX_ENHANCED_GUIDE,
        SYNTAX_FACSIMILE_TELEPHONE_NUMBER,
        SYNTAX_FAX,
        SYNTAX_GENERALIZED_TIME,
        SYNTAX_GUIDE,
        SYNTAX_IA5_STRING,
        SYNTAX_INTEGER,
        SYNTAX_JPEG,
        SYNTAX_LDAP_SYNTAX_DESCRIPTION,
        SYNTAX_MATCHING_RULE_DESCRIPTION,
        SYNTAX_MATCHING_RULE_USE_DESCRIPTION,
        SYNTAX_NAME_AND_OPTIONAL_UID,
        SYNTAX_NAME_FORM_DESCRIPTION,
        SYNTAX_NUMERIC_STRING,
        SYNTAX_OBJECT_CLASS_DESCRIPTION,
        SYNTAX_OCTET_STRING,
        SYNTAX_OID,
        SYNTAX_OTHER_MAILBOX,
        SYNTAX_POSTAL_ADDRESS,
        SYNTAX_PRINTABLE_STRING,
        SYNTAX_SUBSTRING_ASSERTION,
        SYNTAX_TELEPHONE_NUMBER,
        SYNTAX_TELETEX_TERMINAL_IDENTIFIER,
        SYNTAX_TELEX_NUMBER,
        SYNTAX_UTC_TIME
    ];

    public static function getAll()
    {
        $all = [];

        foreach (self::$types as $syntax_type) {
            $syntax = new Syntax($syntax_type);
            $oid = $syntax->__toString();
            $all[$oid] = $syntax;
        }

        return $all;
    }

    public function __construct(int $syntax_type)
    {
        if (in_array($syntax_type, self::$types)) {
            $this->syntax_type = $syntax_type;
        } else {
            throw new \OutOfRangeException("Unknown syntax ID $syntax_type");
        }
    }

    public static function serialize($value)
    {
        switch ($this->syntax_type) {
            case SYNTAX_ATTRIBUTE_TYPE_DESCRIPTION:
            case SYNTAX_BIT_STRING:
            case SYNTAX_BOOLEAN:
                return $value ? 'TRUE' : 'FALSE';

            case SYNTAX_COUNTRY_STRING:
            case SYNTAX_DELIVERY_METHOD:
            case SYNTAX_DIRECTORY_STRING:
            case SYNTAX_DIT_CONTENT_RULE_DESCRIPTION:
            case SYNTAX_DIT_STRUCTURE_RULE_DESCRIPTION:
            case SYNTAX_DN:
            case SYNTAX_ENHANCED_GUIDE:
            case SYNTAX_FACSIMILE_TELEPHONE_NUMBER:
            case SYNTAX_FAX:
            case SYNTAX_GENERALIZED_TIME:
            case SYNTAX_GUIDE:
            case SYNTAX_IA5_STRING:
            case SYNTAX_INTEGER:
                if (is_integer($value)) {
                    return strval($value);
                } else {
                    throw new SyntaxException($value);
                }

            case SYNTAX_JPEG:
                if (is_string($value)) {
                    return $value;
                } else {
                    throw new SyntaxException('<non-string data>');
                }

            case SYNTAX_LDAP_SYNTAX_DESCRIPTION:
            case SYNTAX_MATCHING_RULE_DESCRIPTION:
            case SYNTAX_MATCHING_RULE_USE_DESCRIPTION:
            case SYNTAX_NAME_AND_OPTIONAL_UID:
            case SYNTAX_NAME_FORM_DESCRIPTION:
            case SYNTAX_NUMERIC_STRING:
            case SYNTAX_OBJECT_CLASS_DESCRIPTION:
            case SYNTAX_OCTET_STRING:
            case SYNTAX_OID:
            case SYNTAX_OTHER_MAILBOX:
            case SYNTAX_POSTAL_ADDRESS:
            case SYNTAX_PRINTABLE_STRING:
            case SYNTAX_SUBSTRING_ASSERTION:
            case SYNTAX_TELEPHONE_NUMBER:
            case SYNTAX_TELETEX_TERMINAL_IDENTIFIER:
            case SYNTAX_TELEX_NUMBER:
            case SYNTAX_UTC_TIME:

        }
    }

    public static function unserialize(string $serialized)
    {
        switch ($this->syntax_type) {
            case SYNTAX_ATTRIBUTE_TYPE_DESCRIPTION:
            case SYNTAX_BIT_STRING:
            case SYNTAX_BOOLEAN:
                switch ($serialized) {
                    case 'TRUE':
                        return true;
                        break;
                    case 'FALSE':
                        return false;
                        break;
                    default:
                        throw new SyntaxException($serialized, ['TRUE', 'FALSE']);
                }

            case SYNTAX_COUNTRY_STRING:
            case SYNTAX_DELIVERY_METHOD:
            case SYNTAX_DIRECTORY_STRING:
            case SYNTAX_DIT_CONTENT_RULE_DESCRIPTION:
            case SYNTAX_DIT_STRUCTURE_RULE_DESCRIPTION:
            case SYNTAX_DN:
            case SYNTAX_ENHANCED_GUIDE:
            case SYNTAX_FACSIMILE_TELEPHONE_NUMBER:
            case SYNTAX_FAX:
            case SYNTAX_GENERALIZED_TIME:
            case SYNTAX_GUIDE:
            case SYNTAX_IA5_STRING:
            case SYNTAX_INTEGER:
                return intval($serialized);

            case SYNTAX_JPEG:
            case SYNTAX_LDAP_SYNTAX_DESCRIPTION:
            case SYNTAX_MATCHING_RULE_DESCRIPTION:
            case SYNTAX_MATCHING_RULE_USE_DESCRIPTION:
            case SYNTAX_NAME_AND_OPTIONAL_UID:
            case SYNTAX_NAME_FORM_DESCRIPTION:
            case SYNTAX_NUMERIC_STRING:
            case SYNTAX_OBJECT_CLASS_DESCRIPTION:
            case SYNTAX_OCTET_STRING:
            case SYNTAX_OID:
            case SYNTAX_OTHER_MAILBOX:
            case SYNTAX_POSTAL_ADDRESS:
            case SYNTAX_PRINTABLE_STRING:
            case SYNTAX_SUBSTRING_ASSERTION:
            case SYNTAX_TELEPHONE_NUMBER:
            case SYNTAX_TELETEX_TERMINAL_IDENTIFIER:
            case SYNTAX_TELEX_NUMBER:
            case SYNTAX_UTC_TIME:

        }
    }

    public function __toString()
    {
        return '1.3.6.1.4.1.1466.115.121.1.' . $this->syntax_type;
    }
}

