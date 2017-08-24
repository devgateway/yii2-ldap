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
        'syntax',
        '_len'
    ];

    public function __construct(Schema $schema, array $definition)
    {
        if (isset($definition['syntax'])) {
            $matches = [];
            $matched = preg_match(
                '/^ ([^{]+) ( (\{ (\d+) \})? ) $/x',
                $definition['syntax'],
                $matches
            );
            if ($matched) {
                $syntax = $schema[$matches[1]];
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

        parent::__construct($properties);
    }
}

