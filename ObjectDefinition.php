<?php
/**
 * ObjectDefinition class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\Definition;

class ObjectDefinition extends Definition
{
    protected static $keys = [
        'auxiliary',
        'structural',
        'abstract',
        'must',
        'may'
    ];

    public function __construct(Schema $schema, array $definition)
    {
        // find my superclass
        if (array_key_exists('sup', $definition) {
            $sup_name = strtolower($definition['sup']);
            $sup = $sup_name == 'top' ? null : $schema[$sup_name];
        } else {
            $sup = null;
        }

        foreach (['may', 'must'] as $may_or_must) {

            // ensure my own lists exist
            if (!array_key_exists($may_or_must, $definition)) {
                $definition[$may_or_must] = [];
            }

            // resolve my lists into AttributeDefinition objects
            $attribute_names = &$definition[$may_or_must]; // alias for readability
            $attributes = [];
            foreach ($attribute_names as $name) {
                $attributes[] = $schema[$name];
            }

            // inherit lists from my superclass
            if (!is_null($sup)) {
                $all_attributes = array_merge($attributes, $sup->properties[$may_or_must]);
                $definition[$may_or_must] = array_unique($all_attributes, SORT_REGULAR);
            }
        }

        // remove inherited MAYs which have been redeclared as MUSTs
        $definition['may'] = array_diff(
            $definition['may'],
            $definition['must']
        );

        foreach (self::$keys as $key) {
            $this->properties[$key] = $definition[$key];
        }

        parent::__construct($definition);
    }
}

