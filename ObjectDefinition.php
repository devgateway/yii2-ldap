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
        $attribute_set = [];

        foreach (['may', 'must'] as $may_or_must) {

            // ensure my own lists exist
            if (!array_key_exists($may_or_must, $definition)) {
                $definition[$may_or_must] = [];
            }

            // resolve my lists into AttributeDefinition objects
            $attributes = [];
            foreach ($definition[$may_or_must] as $name) {
                $attribute = $schema[$name];
                $attributes[$attribute->oid] = $attribute;
            }

            // merge my parents' attributes
            if (array_key_exists('sup', $definition)) {
                foreach ($definition['sup'] as $name) {
                    $parent_attributes = $schema[$name]->properties[$may_or_must];
                    foreach ($parent_attributes as $attribute) {
                        $attributes[$attribute->oid] = $attribute;
                    }
                }
            }

            $attribute_set[$may_or_must] = $attributes;
        }

        // remove inherited MAYs which have been redeclared as MUSTs
        $attribute_set['may'] = array_diff(
            $attribute_set['may'],
            $attribute_set['must']
        );

        foreach (['may', 'must'] as $may_or_must) {
            $definition[$may_or_must] = OidArray::fromArray($attribute_set[$may_or_must]);
        }

        foreach (self::$keys as $key) {
            $this->properties[$key] = $definition[$key];
        }

        parent::__construct($definition);
    }
}

