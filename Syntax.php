<?php
/**
 * Syntax classes
 *
 * @link https://github.com/devgateway/yii2-ldap
 * @link https://tools.ietf.org/html/rfc4517
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

/**
 * Set of rules to serialize/unserialize values to/from LDAP string formats.
 */
class Syntax
{
    const SYNT_ATTRIBUTE_TYPE_DESCRIPTION = 3;
    const SYNT_BIT_STRING = 6;
    const SYNT_BOOLEAN = 7;
    const SYNT_COUNTRY_STRING = 11;
    const SYNT_DELIVERY_METHOD = 14;
    const SYNT_DIRECTORY_STRING = 15;
    const SYNT_DIT_CONTENT_RULE_DESCRIPTION = 16;
    const SYNT_DIT_STRUCTURE_RULE_DESCRIPTION = 17;
    const SYNT_DN = 12;
    const SYNT_ENHANCED_GUIDE = 21;
    const SYNT_FACSIMILE_TELEPHONE_NUMBER = 22;
    const SYNT_FAX = 23;
    const SYNT_GENERALIZED_TIME = 24;
    const SYNT_GUIDE = 25;
    const SYNT_IA5_STRING = 26;
    const SYNT_INTEGER = 27;
    const SYNT_JPEG = 28;
    const SYNT_LDAP_SYNTAX_DESCRIPTION = 54;
    const SYNT_MATCHING_RULE_DESCRIPTION = 30;
    const SYNT_MATCHING_RULE_USE_DESCRIPTION = 31;
    const SYNT_NAME_AND_OPTIONAL_UID = 34;
    const SYNT_NAME_FORM_DESCRIPTION = 35;
    const SYNT_NUMERIC_STRING = 36;
    const SYNT_OBJECT_CLASS_DESCRIPTION = 37;
    const SYNT_OCTET_STRING = 40;
    const SYNT_OID = 38;
    const SYNT_OTHER_MAILBOX = 39;
    const SYNT_POSTAL_ADDRESS = 41;
    const SYNT_PRINTABLE_STRING = 44;
    const SYNT_SUBSTRING_ASSERTION = 58;
    const SYNT_TELEPHONE_NUMBER = 50;
    const SYNT_TELETEX_TERMINAL_IDENTIFIER = 51;
    const SYNT_TELEX_NUMBER = 52;
    const SYNT_UTC_TIME = 53;

    /**
     * @var string $gt_pattern Regex pattern for Generalized Time.
     * @see https://tools.ietf.org/html/rfc4517#section-3.3.13
     */
    protected static $gt_pattern = <<<'END'
        /^
            (?P<year>\d{4})
            (?P<month>\d{2})
            (?P<day>\d{2})
            (?P<hour>\d{2})
            (
                (?P<minute>\d{2})
                (?P<second>(\d{2})?)
            )?
            (
                [.,]
                (?P<frac>\d)
            )?
            (
                Z |
                (?P<diff>[+-]\d{2}(\d{2})?)
            )
        $/x
END;

    /** @var int $type Last part of standard syntax OID. */
    public $type;

    /**
     * List all known syntaxes and their OIDs.
     *
     * @return Syntax[] Array of syntax objects with OIDs as keys.
     */
    public static function getAll()
    {
        $all = [];
        $me = new \ReflectionClass(__CLASS__);

        foreach ($me->getConstants() as $type) {
            $syntax = new Syntax();
            $syntax->type = $type;
            $all["1.3.6.1.4.1.1466.115.121.1.$type"] = $syntax;
        }

        return $all;
    }

    /**
     * Convert a value to LDAP format according to the syntax rules.
     *
     * @param mixed $value The original value.
     * @throws \UnexpectedValueException If the value violates the syntax rules.
     * @return string Value suitable for PHP LDAP extension functions.
     */
    public function serialize($value)
    {
        if (!$this->validate($value)) {
            throw new SyntaxException($value);
        }

        switch ($this->syntax_type) {
            case SYNTAX_ATTRIBUTE_TYPE_DESCRIPTION:
                $result = $value;
                break;

            case SYNTAX_BIT_STRING:
                break;

            case SYNTAX_BOOLEAN:
                $result = $value ? 'TRUE' : 'FALSE';
                break;

            case SYNTAX_COUNTRY_STRING:
                $result = $value;
                break;

            case SYNTAX_DELIVERY_METHOD:
                break;

            case SYNTAX_DIRECTORY_STRING:
                $result = $value;
                break;

            case SYNTAX_DIT_CONTENT_RULE_DESCRIPTION:
                break;

            case SYNTAX_DIT_STRUCTURE_RULE_DESCRIPTION:
                break;

            case SYNTAX_DN:
                $result = $value;
                break;

            case SYNTAX_ENHANCED_GUIDE:
                break;

            case SYNTAX_FACSIMILE_TELEPHONE_NUMBER:
                break;

            case SYNTAX_FAX:
                break;

            case SYNTAX_GENERALIZED_TIME:
                $result = $value->format("YmdHi\Z");
                break;

            case SYNTAX_GUIDE:
                $result = $value;
                break;

            case SYNTAX_IA5_STRING:
                break;

            case SYNTAX_INTEGER:
                $result = strval($value);
                break;

            case SYNTAX_JPEG:
                $result = $value;
                break;

            case SYNTAX_LDAP_SYNTAX_DESCRIPTION:
                break;

            case SYNTAX_MATCHING_RULE_DESCRIPTION:
                break;

            case SYNTAX_MATCHING_RULE_USE_DESCRIPTION:
                break;

            case SYNTAX_NAME_AND_OPTIONAL_UID:
                break;

            case SYNTAX_NAME_FORM_DESCRIPTION:
                break;

            case SYNTAX_NUMERIC_STRING:
                $result = $value;
                break;

            case SYNTAX_OBJECT_CLASS_DESCRIPTION:
                $result = $value;
                break;

            case SYNTAX_OCTET_STRING:
                $result = $value;
                break;

            case SYNTAX_OID:
                $result = $value;
                break;

            case SYNTAX_OTHER_MAILBOX:
                break;

            case SYNTAX_POSTAL_ADDRESS:
                break;

            case SYNTAX_PRINTABLE_STRING:
                $result = $value;
                break;

            case SYNTAX_SUBSTRING_ASSERTION:
                break;

            case SYNTAX_TELEPHONE_NUMBER:
                $result = $value;
                break;

            case SYNTAX_TELETEX_TERMINAL_IDENTIFIER:
                break;

            case SYNTAX_TELEX_NUMBER:
                break;

            case SYNTAX_UTC_TIME:
                break;

        }
    }

    /**
     * Convert a value from LDAP format to an appropriate PHP native type.
     *
     * @param string $serialized Value received from PHP LDAP extension functions.
     * @throws \UnexpectedValueException If the value violates the syntax rules.
     * @return mixed The native value.
     */
    public function unserialize($serialized)
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
                        throw new \UnexpectedValueException($serialized);
                }

            case SYNTAX_COUNTRY_STRING:
                return $serialized;

            case SYNTAX_DELIVERY_METHOD:
            case SYNTAX_DIRECTORY_STRING:
                return $serialized;

            case SYNTAX_DIT_CONTENT_RULE_DESCRIPTION:
            case SYNTAX_DIT_STRUCTURE_RULE_DESCRIPTION:
            case SYNTAX_DN:
                return $serialized;
            case SYNTAX_ENHANCED_GUIDE:
            case SYNTAX_FACSIMILE_TELEPHONE_NUMBER:
            case SYNTAX_FAX:
            case SYNTAX_GENERALIZED_TIME:
                $date_time = static::parseGeneralizedTime($serialized);
                if ($date_time !== false) {
                    return $date_time;
                } else {
                    throw new \UnexpectedValueException($serialized);
                }

            case SYNTAX_GUIDE:
            case SYNTAX_IA5_STRING:
                return $serialized;

            case SYNTAX_INTEGER:
                return intval($serialized);

            case SYNTAX_JPEG:
		return $serialized;
            case SYNTAX_LDAP_SYNTAX_DESCRIPTION:
            case SYNTAX_MATCHING_RULE_DESCRIPTION:
            case SYNTAX_MATCHING_RULE_USE_DESCRIPTION:
            case SYNTAX_NAME_AND_OPTIONAL_UID:
            case SYNTAX_NAME_FORM_DESCRIPTION:
            case SYNTAX_NUMERIC_STRING:
                return $serialized;

            case SYNTAX_OBJECT_CLASS_DESCRIPTION:
                return $serialized;

            case SYNTAX_OCTET_STRING:
		return $serialized;

            case SYNTAX_OID:
		return $serialized;

            case SYNTAX_OTHER_MAILBOX:
            case SYNTAX_POSTAL_ADDRESS:
            case SYNTAX_PRINTABLE_STRING:
                return $serialized;

            case SYNTAX_SUBSTRING_ASSERTION:
            case SYNTAX_TELEPHONE_NUMBER:
                return $serialized;

            case SYNTAX_TELETEX_TERMINAL_IDENTIFIER:
            case SYNTAX_TELEX_NUMBER:
            case SYNTAX_UTC_TIME:
        }
    }

    /**
     * Convert Generalized Time to DateTime.
     *
     * @param string $gt_string Generalized Time per RFC 4517
     * @throws \RuntimeException If regex can't be run.
     * @return DateTime Parsed date as object.
     */
    public static function parseGeneralizedTime($gt_string)
    {
        $gt = [];
        $matched = preg_match(static::$gt_pattern, $gt_string, $gt);

        if ($matched) {
            // build a DateTime from date and time zone, for now
            $date = "${gt['year']}/${gt['month']}/${gt['day']}";
            $time_zone = new \DateTimeZone(
                isset($gt['diff']) ? $gt['diff'] : 'UTC'
            );
            $result = new \DateTime($date, $time_zone);

            // specify the exact time
            $hour = intval($gt['hour']);

            $frac = $gt['frac'] ? intval($gt['frac']) : 0;
            if ($gt['minute']) {
                $minute = intval($gt['minute']);

                if ($gt['second']) {
                    // PHP < 7.1 doesn't support milliseconds in DateTime,
                    // so we apply frac to seconds here
                    $second = round(floatval("${gt['second']}.$frac"));
                } else {
                    // if second is omitted, frac is a fraction of a minute
                    $second = $frac ? 60 * $frac / 10 : 0;
                }
            } else {
                // if minute is omitted, frac is a fraction of an hour
                $minute = 0;
                // overflow handled properly by DateTime
                $second = $frac ? round(60 * 60 * $frac / 10) : 0;
            }

            $result->setTime($hour, $minute, $second);
        } elseif ($matched === 0) {
            $result = false;
        } else {
            throw new \RuntimeException(
                'Error running a regex match'
            );
        }

        return $result;
    }

    /**
     * Return the syntax OID.
     *
     * @return string Syntax OID.
     */
    public function __toString()
    {
        return '1.3.6.1.4.1.1466.115.121.1.' . $this->syntax_type;
    }
}
