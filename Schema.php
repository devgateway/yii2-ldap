<?php
/**
 * Schema and related classes
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @link https://tools.ietf.org/html/rfc4512
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\Syntax;

class Schema
{
    protected $schema = new OidArray();

    public function __construct()
    {
        $syntaxes = [
            'AttributeTypeDescriptionSyntax',
            'BitStringSyntax',
            'BooleanSyntax',
            'CountryStringSyntax',
            'DeliveryMethodSyntax',
            'DirectoryStringSyntax',
            'DitContentRuleDescriptionSyntax',
            'DitStructureRuleDescriptionSyntax',
            'DnSyntax',
            'EnhancedGuideSyntax',
            'FacsimileTelephoneNumberSyntax',
            'FaxSyntax',
            'GeneralizedTimeSyntax',
            'GuideSyntax',
            'Ia5StringSyntax',
            'IntegerSyntax',
            'JpegSyntax',
            'LdapSyntaxDescriptionSyntax',
            'MatchingRuleDescriptionSyntax',
            'MatchingRuleUseDescriptionSyntax',
            'NameAndOptionalUidSyntax',
            'NameFormDescriptionSyntax',
            'NumericStringSyntax',
            'ObjectClassDescriptionSyntax',
            'OctetStringSyntax',
            'OtherMailboxSyntax',
            'PostalAddressSyntax',
            'PrintableStringSyntax',
            'SubstringAssertionSyntax',
            'TelephoneNumberSyntax',
            'TeletexTerminalIdentifierSyntax',
            'TelexNumberSyntax',
            'UtcTimeSyntax'
        ];

        foreach ($syntaxes as $syntax) {
            $this->schema[$syntax->short_name] = new $syntax();
        }
    }
}

