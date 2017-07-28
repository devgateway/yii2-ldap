<?php
namespace ldap;

class Syntax
{
}

interface IMatchingRule
{
}

class MatchingRule
{
}

class AttributeDefinition
{
    protected $oid;
    protected $name;
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
        array $name,
        string $desc,
        bool $obsolete,
        MatchingRule $equality,
        MatchingRule $ordering,
        MatchingRule $substr,
        Syntax $syntax,
        bool $singlevalue,
        bool $collective,
        bool $nousermod
    ) {
    }
}
