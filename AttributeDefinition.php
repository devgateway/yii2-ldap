<?php
/**
 * AttributeDefinition class
 *
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\Definition;
use devgateway\ldap\Schema;

class AttributeDefinition extends Definition
{
    protected static $keys = [
        'single_value',
        'no_user_modification',
        'syntax'
    ];

    public function __construct(Schema $schema, array $definition)
    {
        if ($definition['syntax']) {
            $oid = $definition['syntax'];
            $syntax = $schema[$oid];
        } else {
            $sup = $schema[$definition['sup']];
            $syntax = $schema[$sup]->syntax;
        }
        $definition['syntax'] = $syntax;

        foreach (self::$keys as $key) {
            $this->properties[$key] = $definition[$key];
        }

        parent::__construct($properties);
    }
}

