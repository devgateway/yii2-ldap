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
        switch ($this->type) {
            case SYNTAX_BOOLEAN:
                $result = $value ? 'TRUE' : 'FALSE';

            case SYNTAX_GENERALIZED_TIME:
            case SYNTAX_UTC_TIME:
                $result = $value->format('YmdHi\Z');

            default:
                $result = $value;
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
        switch ($this->type) {
            case SYNTAX_BOOLEAN:
                switch ($serialized) {
                    case 'TRUE':
                        $result = true;
                        break;
                    case 'FALSE':
                        $result = false;
                        break;
                    default:
                        throw new \UnexpectedValueException($serialized);
                }
                break;

            case SYNTAX_UTC_TIME:
                $result = static::parseUtcTime($serialized);
                if ($result === false) {
                    throw new \UnexpectedValueException($serialized);
                }
                break;

            case SYNTAX_GENERALIZED_TIME:
                $result = static::parseGeneralizedTime($serialized);
                if ($result === false) {
                    throw new \UnexpectedValueException($serialized);
                }
                break;

            case SYNTAX_INTEGER:
                $result = intval($serialized);
                break;

            default:
                $result = $serialized;
        }

        return $result;
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
            $newer_php = version_compare(PHP_VERSION, '5.5.10', '>=');
            if ($newer_php) {
                $tz_info = isset($gt['diff']) ? $gt['diff'] : 'UTC';
            } else {
                $tz_info = 'UTC';
            }
            $time_zone = new \DateTimeZone($tz_info);

            // build a DateTime from date and time zone, for now
            $date = $gt['year'] . $gt['month'] . $gt['day'];
            $result = \DateTime::createFromFormat('Ymd', $date, $time_zone);

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
            if (!$newer_php && isset($gt['diff'])) {
                $diff = [];
                $pattern = '/^ ([+-]) (\d{2}) ((\d{2})?) $/x';
                preg_match($pattern, $gt['diff'], $diff);

                $offset = new \DateInterval('PT' . $diff[2]); // hours
                $offset->i = intval($diff[3]); // minutes
                $offset->invert = (int) $diff[1] == '-';

                $result->add($offset);
            }
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
     * Convert UTC Time to DateTime.
     *
     * @param string $ut_string UTC Time per ASN.1
     * @throws \RuntimeException If regex can't be run.
     * @return DateTime Parsed date as object.
     */
    public static function parseUtcTime($ut_string)
    {
        $ut_pattern = <<<'END'
            /^
                (?P<year>\d{2})
                (?P<month>\d{2})
                (?P<day>\d{2})
                (?P<hour>\d{2})
                (?P<minute>\d{2})
                (?P<second>(\d{2})?)
                (
                    Z |
                    (?P<diff>[+-]\d{4})
                )
            $/x
END;
        $ut = [];
        $matched = preg_match(static::$ut_pattern, $ut_string, $ut);

        if ($matched) {
            $newer_php = version_compare(PHP_VERSION, '5.5.10', '>=');
            if ($newer_php) {
                $tz_info = isset($gt['diff']) ? $gt['diff'] : 'UTC';
            } else {
                $tz_info = 'UTC';
            }

            // build a DateTime from date and time zone, for now
            $date = $ut['year'] . $ut['month'] . $ut['day'];
            $result = \DateTime::createFromFormat('ymd', $date, $time_zone);

            // specify the exact time
            $result->setTime($ut['hour'], $ut['minute'], $ut['second']);
            if (!$newer_php && isset($gt['diff'])) {
                $diff = [];
                $pattern = '/^ ([+-]) (\d{2}) (\d{2}) $/x';
                preg_match($pattern, $gt['diff'], $diff);

                $offset = new \DateInterval("PT${diff[2]}H${diff[3]}M");
                $offset->invert = (int) $diff[1] == '-';

                $result->add($offset);
            }
        } elseif ($matched === 0) {
            $result = false;
        } else {
            throw new \RuntimeException(
                'Error running a regex match'
            );
        }

        return $result;
    }
}
