<?php
/**
 * CompoundObject class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

use devgateway\ldap\SimpleObject;
use devgateway\ldap\Schema;
use devgateway\ldap\SimpleObject;

class CompoundObject extends OidArray
{
    public function __construct(Schema $schema, array $entry)
    {
        for ($i=0; $i < $entry["count"]; $i++) {
            $key = $entry[$i];
            if (strtolower($key) == "objectclass") {
                for ($j = 0; $j<$entry[$key]['count']; $j++) {
                    $simple_object = new SimpleObject($schema, $entry[$key][$j], $entry);
                    $offset = OidArray::offsetMake($simple_object);
                    $this[$offset] = $simple_object;
                }
            }
        }
    }
}

