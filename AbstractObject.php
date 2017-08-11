<?php
/**
 * AbstractObject
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

class AbstractObject
{
    protected $oid;
    protected $name;

    abstract public function getShortName();

    public function __construct(string $oid, array $name)
    {
        $this->oid = $oid;
        $this->name = $name;
    }

    public function getIndex()
    {
        $index = $this->name;
        array_unshift($index, $this->oid);

        return $index;
    }
}
