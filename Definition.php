<?php
/**
 * Definition class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

abstract class Definition
{
    protected $oid;
    protected $names;
    protected $sup;

    public function __construct(
        string $oid,
        array $names = [],
        $sup = null
    ) {
        $this->$oid = $oid;
        $this->$names = $names;
        $this->$sup = $sup;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$$property;
        }
    }
}

