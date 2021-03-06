<?php
/**
 * CompoundObject class
 *
 * @link https://github.com/devgateway/yii2-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\SimpleObject;
use devgateway\ldap\Schema;

/** Representation of an LDAP object of one or more object classes. */
class CompoundObject extends OidArray
{
    /**
     * Initialize all simple objects of individual classes.
     *
     * @param Schema $schema Schema object to query.
     * @param mixed[] $entry Array of attributes from PHP LDAP extension.
     */
    public function __construct(Schema $schema, $entry)
    {
        for ($i = 0; $i < $entry['count']; $i++) {
            $attr_name = $entry[$i];
            $num_attrs = $entry[$attr_name]['count'];

            // initialize each object class as a SimpleObject
            if (strtolower($attr_name) == 'objectclass') {
                for ($j = 0; $j < $num_attrs; $j++) {
                    $class_name = $entry[$attr_name][$j];
                    $simple_object = new SimpleObject($schema, $class_name, $entry);
                    $offset = OidArray::offsetMake($simple_object->getDefinition());
                    $this[$offset] = $simple_object;
                }
            }
        }
    }

    /**
     * Get JSON representation of all classes and their attributes.
     *
     * @return string JSON representation.
     */
    public function __toString()
    {
        $result = [];
        foreach ($this as $class_name => $simple_object) {
            $result[$class_name] = $simple_object->canonical_names;
        }

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    /**
     * Export the object to a sorted two-dimensional array. Keys are sorted object class names,
     * and values are arrays of attributes, also sorted by name.
     *
     * @return array[] Attributes grouped by simple object.
     */
    public function toArray()
    {
        $seen_attrs = [];
        $result = [];

        foreach ($this as $class_name => $ignored) {
            $result[$class_name] = null;
        }

        // sort simple objects by class name
        ksort($result);

        foreach (array_keys($result) as $class_name) {
            // from current object class, remove attrs already present in other classes
            $result[$class_name] = array_diff_key(
                $this[$class_name]->canonical_names,
                $seen_attrs
            );
            // sort attributes
            ksort($result[$class_name]);
            // append new attrs to the list of seen ones
            $seen_attrs = array_replace($seen_attrs, $result[$class_name]);
        }

        return $result;
    }
}
