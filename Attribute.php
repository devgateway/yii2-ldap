<?php
/**
 * Attribute class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\AttributeDefinition;

class Attribute
{
    protected $definition;
    protected $value;

    public function __construct(
        AttributeDefinition $definition,
        array $ldap_value = null
    ) {
        $this->definition = $definition;

        if ($definition->single_value) {
            $value = $definition->syntax->unserialize($ldap_value[0]);
        } else {
            $value = [];
            foreach ($ldap_value as $key => $each_value) {
                if ($key != 'count') {
                    $value[] = $definition->syntax->unserialize($each_value);
                }
            }
        }

        $this->value = $value;
    }

    public function __set(string $name, $value)
    {
        if ($name == 'value') {
        } else {
            throw new \RuntimeException("Unknown property: $name");
        }
    }

    public function __get(string $name)
    {
        if ($name == 'value') {
        } else {
            $value = null;
            trigger_error("Unknown property: $name");
        }

        return $value;
    }
}

