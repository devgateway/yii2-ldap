<?php
/**
 * AttributeDefinition class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\Definition;

class AttributeDefinition extends Definition
{
    protected $desc;
    protected $obsolete;
    protected $equality;
    protected $ordering;
    protected $substr;
    protected $syntax;
    protected $singlevalue;
    protected $collective;
    protected $nousermod;

    public function __construct(
        string $oid,
        array $names,
        string $desc,
        Syntax $syntax,
        bool $singlevalue = false,
        bool $obsolete = false,
        bool $collective = false,
        bool $nousermod = false
    ) {
    }
}

