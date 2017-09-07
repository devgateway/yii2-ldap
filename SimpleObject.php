<?php
/**
 * SimpleObject class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\OidArray;
use devgateway\ldap\Schema;

class SimpleObject extends OidArray
{
    protected $schema;
    protected $definition;

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

            if (
                isset($this->definition->must[$attr_name]) or
                isset($this->definition->may[$attr_name])
            ) {
                $definition = $schema[$attr_name];
                $offset = OidArray::offsetMake($definition);

                if ($definition->single_value) {
                    $value = $definition->syntax->unserialize($entry[$attr_name][0]);
                } else {
                    $value = [];
                    foreach ($entry[$attr_name] as $key => $each_value) {
                        if ($key != 'count') {
                            $value[] = $definition->syntax->unserialize($each_value);
                        }
                    }
                }

                $this[$offset] = $value;
            }
        }
    }

    public function getDefinition()
    {
        return $this->definition;
    }
}

