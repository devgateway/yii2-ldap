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
    protected static $keys = [
        'oid',
        'name',
        'desc',
        'obsolete'
    ];

    protected $properties = [];
    protected $short_name;

    public function __construct(array $properties)
    {
        foreach (self::$keys as $key) {
            $this->properties[$key] = $properties[$key];
        }

        $this->short_name = $this->getShortName();
    }

    protected function getShortName()
    {
        $max_length = 0;
        foreach ($this->properties['name'] as $name) {
            $length = strlen($name);
            if ($length > $max_length) {
                $max_length = $length;
                $short_name = $name;
            }
        }

        return isset($short_name) ? $short_name : $this->properties['oid'];
    }

    public function __toString()
    {
        return $this->short_name;
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->properties)) {
            $value = $this->properties[$name];
        } else {
            $value = null;
            trigger_error("Unknown property: $name");
        }
        return $value;
    }
}

