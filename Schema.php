<?php
/**
 * Schema and related classes
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://tools.ietf.org/html/rfc4517
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\AbstractObject;

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

abstract class Syntax extends AbstractObject
{
    public function getShortName()
    {
        return $this->oid;
    }
}

class AttributeTypeDescriptionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.3';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class BitStringSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.6';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class BooleanSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.7';

    public static function serialize($value)
    {
        return $value ? 'TRUE' : 'FALSE';
    }

    public static function unserialize(string $serialized)
    {
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
    }
}

class CountryStringSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.11';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class DeliveryMethodSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.14';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class DirectoryStringSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.15';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class DitContentRuleDescriptionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.16';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class DitStructureRuleDescriptionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.17';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class DnSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.12';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class EnhancedGuideSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.21';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class FacsimileTelephoneNumberSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.22';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class FaxSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.23';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class GeneralizedTimeSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.24';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class GuideSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.25';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class Ia5StringSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.26';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class IntegerSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.27';

    public static function serialize($value)
    {
        if (is_integer($value)) {
            return strval($value);
        } else {
            throw new SyntaxException($value);
        }
    }

    public static function unserialize(string $serialized)
    {
        return intval($serialized);
    }
}

class JpegSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.28';

    public static function serialize($value)
    {
        if (is_string($value)) {
            return $value;
        } else {
            throw new SyntaxException('<non-string data>');
        }
    }

    public static function unserialize(string $serialized)
    {
    }
}

class LdapSyntaxDescriptionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.54';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class MatchingRuleDescriptionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.30';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class MatchingRuleUseDescriptionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.31';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class NameAndOptionalUidSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.34';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class NameFormDescriptionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.35';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class NumericStringSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.36';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class ObjectClassDescriptionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.37';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class OctetStringSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.40';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class OtherMailboxSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.39';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class PostalAddressSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.41';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class PrintableStringSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.44';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class SubstringAssertionSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.58';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class TelephoneNumberSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.50';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class TeletexTerminalIdentifierSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.51';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class TelexNumberSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.52';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class UtcTimeSyntax extends Syntax
{
    protected static $oid = '1.3.6.1.4.1.1466.115.121.1.53';

    public static function serialize($value)
    {
    }

    public static function unserialize(string $serialized)
    {
    }
}

class Schema
{
    protected $schema = new OidArray();

    public function __construct()
    {
        $syntaxes = [
            new AttributeTypeDescriptionSyntax(),
            new BitStringSyntax(),
            new BooleanSyntax(),
            new CountryStringSyntax(),
            new DeliveryMethodSyntax(),
            new DirectoryStringSyntax(),
            new DitContentRuleDescriptionSyntax(),
            new DitStructureRuleDescriptionSyntax(),
            new DnSyntax(),
            new EnhancedGuideSyntax(),
            new FacsimileTelephoneNumberSyntax(),
            new FaxSyntax(),
            new GeneralizedTimeSyntax(),
            new GuideSyntax(),
            new Ia5StringSyntax(),
            new IntegerSyntax(),
            new JpegSyntax(),
            new LdapSyntaxDescriptionSyntax(),
            new MatchingRuleDescriptionSyntax(),
            new MatchingRuleUseDescriptionSyntax(),
            new NameAndOptionalUidSyntax(),
            new NameFormDescriptionSyntax(),
            new NumericStringSyntax(),
            new ObjectClassDescriptionSyntax(),
            new OctetStringSyntax(),
            new OtherMailboxSyntax(),
            new PostalAddressSyntax(),
            new PrintableStringSyntax(),
            new SubstringAssertionSyntax(),
            new TelephoneNumberSyntax(),
            new TeletexTerminalIdentifierSyntax(),
            new TelexNumberSyntax(),
            new UtcTimeSyntax()
        ];

        foreach ($syntaxes as $syntax) {
            $self->schema[$syntax->getShortName()] = $syntax;
        }
    }
}
