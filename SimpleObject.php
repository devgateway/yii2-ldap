<?php
/**
 * SimpleObject class
 *
 * @link https://github.com/devgateway/yii2-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\OidArray;
use devgateway\ldap\Schema;

/**
 * Object of a single LDAP object class.
 */
class SimpleObject extends OidArray
{
    /** @var Schema $schema Schema object for current LDAP connection. */
    protected $schema;

    /** @var ObjectDefinition $definition Definition of this object class in schema. */
    protected $definition;

    /**
     * Load schema definitions, and parse LDAP array with them.
     *
     * @param Schema $schema Schema object to query.
     * @param string $class_name Object class name.
     * @param mixed[] $entry Array of attributes from PHP LDAP extension.
     */
    public function __construct(
        Schema $schema,
        $class_name,
        $entry
    ) {
        $this->schema = $schema;
        $this->definition = $schema[$class_name];

        $count = $entry['count'];
        for ($i = 0; $i < $count; $i++) {
            $attr_name = $entry[$i];

            // only interested in my MUST and MAY attributes
            $relevant_attribute = isset($this->definition->must[$attr_name])
                || isset($this->definition->may[$attr_name]);
            if ($relevant_attribute) {
                $definition = $schema[$attr_name];
                $offset = OidArray::offsetMake($definition);

                // parse arrays of strings according to syntax rules
                if ($definition->single_value) {
                    $value = $definition->syntax->unserialize($entry[$attr_name][0]);
                } else {
                    $value = [];
                    foreach ($entry[$attr_name] as $key => $each_value) {
                        if ($key !== 'count') {
                            $value[] = $definition->syntax->unserialize($each_value);
                        }
                    }
                }

                $this[$offset] = $value;
            }
        }
    }

    /**
     * Return ObjectDefinition for current object class.
     *
     * @return ObjectDefinition Definition for current object class.
     */
    public function getDefinition()
    {
        return $this->definition;
    }
}
