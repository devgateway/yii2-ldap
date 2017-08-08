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
    protected $oid;
    protected $names;
    protected $sup;

    public function __construct(
        string $oid,
        array $names = array(),
        $sup = null
    ) {
        $this->$oid = $oid;
        $this->$names = $names;
        $this->$sup = $sup;
    }
}

