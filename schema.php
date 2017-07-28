<?php
namespace ldap;

trait ReadOnlyGetter
{
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$$property;
        }
    }
}

class ObjectDefinition
{
    protected $oid;
    protected $names;
    protected $sup;

    public function __construct(
        string $oid,
        array $names = array(),
        $sup = NULL
    ) {
        $this->$oid = $oid;
        $this->$names = $names;
        $this->$sup = $sup;
    }

    use ReadOnlyGetter;
}

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

/*
abstract class MatchingRule
{
}

class Match extends MatchingRule
{
}

class Ordering extends MatchingRule
{
}

class Substring extends MatchingRule
{
}
 */

class AttributeDefinition
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
        // MatchingRule $equality,
        // MatchingRule $ordering,
        // MatchingRule $substr,
        Syntax $syntax,
        bool $singlevalue = false,
        bool $obsolete = false,
        bool $collective = false,
        bool $nousermod = false
    ) {
    }
}

class Schema
{
    protected $syntaxes = array();
    protected $matching_rules = array();
    protected $attr_defs = array();
    protected $object_defs = array();
}
