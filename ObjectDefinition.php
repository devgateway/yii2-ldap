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
        foreach (['may', 'must'] as $may_or_must) {

            // ensure my own lists exist
            if (!array_key_exists($may_or_must, $definition)) {
                $definition[$may_or_must] = [];
            }

            // resolve my lists into AttributeDefinition objects
            $attribute_names = &$definition[$may_or_must]; // alias for readability
            $attributes = [];
            foreach ($attribute_names as $name) {
                $attribute = $schema[$name];
                $id = spl_object_hash($attribute);
                // imitate a hash, so that array_merge returns unique elements
                $attributes[$id] = $attribute;
            }

            // push my attributes into an array
            $args = [$attributes];
            foreach ($definition['sup'] as $sup_name) {
                $sup = $schema[$sup_name];
                // push my parent's attributes into an array
                $args[] = $sup->properties[$may_or_must];
            }

            // merge my own and my parents' attributes
            $definition[$may_or_must] = call_user_func_array('array_merge', $args);
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

