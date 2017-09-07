<?php
/**
 * CompoundObject class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\SimpleObject;
use devgateway\ldap\Schema;

class CompoundObject extends OidArray
{
    public function __construct(Schema $schema, $entry)
    {
        for ($i = 0; $i < $entry['count']; $i++) {
            $attr_name = $entry[$i];
            $num_attrs = $entry[$attr_name]['count'];

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

    public function __toString()
    {
        $result = [];
        foreach ($this as $class_name => $simple_object) {
            $result[$class_name] = $simple_object->canonical_names;
        }

        return json_encode($result, JSON_PRETTY_PRINT);
    }
}

