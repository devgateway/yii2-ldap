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

/** Base abstract class for AttributeDefinition and ObjectDefinition. */
abstract class Definition
{
    /** @var string[] $keys Names of recognized properties, lowercase and underscored. */
    protected static $keys = [
        'oid',
        'name',
        'desc',
        'obsolete'
    ];

    /** @var mixed[] $properties Values of schema definitions. */
    protected $properties = [];

    /** @var string $short_name Preferred symbolic name for the item. */
    protected $short_name;

    /**
     * Copy interesting properties from the definition; find short name.
     *
     * @param mixed[] $definition Schema definitions, keys lowercase and underscored.
     */
    public function __construct(array $definition)
    {
        foreach (self::$keys as $key) {
            $this->properties[$key] = $definition[$key];
        }

        $this->short_name = $this->getShortName();
    }

    /**
     * Find preferred (longest) symbolic name, or default to OID.
     *
     * @return string Preferred short name.
     */
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

    /**
     * Return preferred short name.
     *
     * @return string Preferred short name.
     */
    public function __toString()
    {
        return $this->short_name;
    }

    /**
     * Read-only property getter.
     *
     * @param string $name Property name, lowercase and underscored.
     * @return mixed Property value.
     */
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

