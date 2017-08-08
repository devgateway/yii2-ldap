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

interface ISyntax
{
    public function validate($value);
}

interface IMatchingRule
{
    public function eq($op1, $op2);
    public function gt($op1, $op2);
    public function lt($op1, $op2);
    public function sub($main_str, $str);
}

abstract class Syntax
{
}

abstract class BaseMatchingRule
{
}

class MatchingRule extends BaseMatchingRule
{
}

class Ordering extends BaseMatchingRule
{
}

class Substring extends BaseMatchingRule
{
}

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
        MatchingRule $equality,
        Ordering $ordering,
        Substring $substr,
        Syntax $syntax,
        bool $singlevalue = false,
        bool $obsolete = false,
        bool $collective = false,
        bool $nousermod = false
    ) {
    }
}
