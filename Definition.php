<?php
/**
 * Definition class
 *
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

abstract class Definition
{
    protected $desc = '';
    protected $sup = null;
    protected $obsolete = false;

    public function __construct(
        string $oid,
        array $name,
        string $desc = '',
        Definition $sup = null,
        bool $obsolete = false
    ) {
        $this->desc = $desc;
        $this->sup = $sup;
        $this->obsolete = $obsolete;
    }

    protected function makeShortName()
    {
        $max_length = 0;
        foreach ($this->name as $name) {
            $length = strlen($name);
            if ($length > $max_length) {
                $max_length = $length;
                $short_name = $name;
            }
        }

        return isset($short_name) ? $short_name : $this->oid;
    }

    public function __get(string $name)
    {
        return property_exists($this, $name) ? $this->$name : null;
    }
}

