<?php
/**
 * AbstractObject
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

abstract class AbstractObject
{
    public function __get(string $name)
    {
        return property_exists($this, $name) ? $this->$name : null;
    }
}

