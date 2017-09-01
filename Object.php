<?php
/**
 * Object class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\OidArray;

class Object extends OidArray
{
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

