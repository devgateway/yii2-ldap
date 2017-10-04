<?php
/**
 * AttributeDefinition class
 *
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://github.com/devgateway/yii2-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\Definition;
use devgateway\ldap\Schema;

/** Attribute definition in LDAP schema. */
class AttributeDefinition extends Definition
{
    /** @var string[] $keys Names of recognized properties, lowercase and underscored. */
    protected static $keys = [
        'single_value',
        'no_user_modification',
        'syntax',
        '_len'
    ];

    /**
     * Set syntax and other properties from schema, possibly inherit syntax from superclass.
     *
     * @param Schema $schema LDAP schema.
     * @param mixed[] $definition Schema definitions, keys lowercase and underscored.
     */
    public function __construct(Schema $schema, $definition)
    {
        if (isset($definition['syntax'])) {
            $matches = [];
            $matched = preg_match(
                '/^ ([^{]+) ( (\{ (\d+) \})? ) $/x',
                $definition['syntax'],
                $matches
            );
            if ($matched) {
                // Some syntaxes got removed from the standard:
                // https://tools.ietf.org/html/rfc4517#appendix-B
                try {
                    $syntax = $schema[$matches[1]];
                } catch (\OutOfBoundsException $e) {
                    // fall back to octet string syntax
                    $syntax = $schema['1.3.6.1.4.1.1466.115.121.1.40'];
                }
                $len = $matches[2] ? intval($matches[3]) : 0;
            } else {
                throw new \RuntimeException(
                    'Unknown syntax format: ' . $definition['syntax']
                );
            }
        } else {
            $sup = $schema[$definition['sup']];
            $syntax = $sup->syntax;
            $len = $sup->_len;
        }
        $definition['syntax'] = $syntax;
        $definition['_len'] = $len;

        foreach (self::$keys as $key) {
            $this->properties[$key] = $definition[$key];
        }

        parent::__construct($definition);
    }
}
