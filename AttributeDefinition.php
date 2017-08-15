<?php
/**
 * AttributeDefinition class
 *
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\Definition;

class AttributeDefinition extends Definition
{
    protected $syntax;
    protected $singlevalue;

    public function __construct(
        string $oid,
        array $name,
        Syntax $syntax = null,
        Definition $sup = null,
        string $desc = '',
        bool $singlevalue = false
    ) {
        $this->singlevalue = $singlevalue;

        if (is_null($syntax)) {
            if (is_null($sup)) {
                $msg = 'Syntax and supertype can\'t both be null';
                throw new \RuntimeException($msg);
            } else {
                $this->syntax = $sup->syntax;
            }
        } else {
            $this->syntax = $syntax;
        }

        parent::__construct(
            $oid,
            $name,
            $desc,
            $sup,
            $obsolete
        );
    }

    public static function parse(string $description)
    {
        $properties = [];

        // unwrap long lines
        $description = str_replace("\n ", '', $description);

        // match OID
        $matches = [];
        $description_regex = '/^\s* \( \s+ ( [0-2](\.\d+)* ) \s+ (.+) \s+ \) \s*/x';
        $found = preg_match($description_regex, $description, $matches);
        if ($found) {
            $properties['oid'] = $matches[1];
            $remainder = $matches[3];
        } else {
            throw new \RuntimeException('Unable to parse attribute description');
        }

        return $properties;
    }
}

