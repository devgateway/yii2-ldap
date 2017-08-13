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
    protected $syntax;
    protected $singlevalue;
    protected $collective;
    protected $nousermodification;

    public function __construct(
        string $oid,
        array $name,
        Syntax $syntax,
        string $desc = '',
        bool $singlevalue = false,
        bool $obsolete = false,
        bool $collective = false,
        bool $nousermodification = false
    ) {
    }
}

