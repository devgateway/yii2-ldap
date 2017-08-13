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

