<?php
/**
 * OidArray class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

/**
 * Dictionary of items by OID and, optionally, by case-insensitive symbolic names.
 */
class OidArray implements \ArrayAccess, \IteratorAggregate
{
    /** @var array $oids Items by OID. */
    protected $oids = [];

    /** @var array $names Items by lowercase symbolic name. */
    protected $names = [];

    /** @var array $canonical_names Items by "pretty" symbolic name. */
    protected $canonical_names = [];

    /**
     * Verify OID per ITU X.660 standard.
     *
     * @param string $oid OID to verify.
     * @return bool Whether OID is valid.
     */
    final private static function validateOid($oid)
    {
        // OIDs start with 0, 1, or 2, and consist of dot-separated numbers
        if (preg_match('/^[0-2](\.\d+)*$/', $oid)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the iterator over "pretty" names and values.
     *
     * @return \ArrayIterator The iterator.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->canonical_names);
    }

    /**
     * Check whether a key exists in the dictionary.
     *
     * @param string $offset OID or case-insensitive name of the element.
     * @return bool true if it exists
     */
    public function offsetExists($offset)
    {
        $idx = strtolower($offset);
        return isset($this->names[$idx]) || isset($this->oids[$idx]);
    }

    /**
     * Retrieve a value by OID or case-insensitive symbolic name.
     *
     * @param string $offset OID or case-insensitive name of the element.
     * @return mixed Element value.
     */
    public function offsetGet($offset)
    {
        $idx = strtolower($offset);

        // names used more often than OIDs, so try them first
        if (isset($this->names[$idx])) {
            return $this->names[$idx];
        } elseif (isset($this->oids[$idx])) {
            return $this->oids[$idx];
        } else {
            throw new \OutOfBoundsException("Key not found: $offset");
        }
    }

    /**
     * Set a value by all possible keys.
     *
     * @param array $offset List of possible keys. The first key must be an
     * OID, the rest are optional symbolic names. The longest symbolic names
     * becomes the "pretty" name; if no names given, OID is the "pretty" name.
     * @param mixed $value The value of the element.
     */
    public function offsetSet($offset, $value)
    {
        // OID is the first and the only mandatory item
        $oid = array_shift($offset);
        if (! $this->validateOid($oid)) {
            throw new \UnexpectedValueException("Invalid OID: $oid");
        }
        $this->oids[$oid] = $value;
        // canonical name defaults to OID
        $canonical_name = $oid;
        $max_length = 0;

        foreach ($offset as $name) {
            $idx = strtolower($name);
            // use an alias, so when updated, reflects in all arrays
            $this->names[$idx] = &$value;

            // make the longest name the canonical name
            $name_length = strlen($name);
            if ($name_length > $max_length) {
                $max_length = $name_length;
                $canonical_name = $name;
            }
        }

        // use an alias, so when updated, reflects in all arrays
        $this->canonical_names[$canonical_name] = &$value;
    }

    /**
     * Remove the key, all its aliases, and the value from dictionary.
     *
     * @param string $offset OID or case-insensitive name to remove.
     */
    public function offsetUnset($offset)
    {
        // this doesn't have to be efficient, it will be rarely used

        $old_value = $this->offsetGet($offset);

        foreach ($this->oids as $oid => $value) {
            if ($value === $old_value) {
                unset($this->oids[$oid]);
                break; // values by OID are unique
            }
        }

        foreach ($this->canonical_names as $cname => $value) {
            if ($value === $old_value) {
                unset($this->canonical_names[$cname]);
                break; // values by canonical name are unique
            }
        }

        foreach ($this->names as $name => $value) {
            if ($value === $old_value) {
                unset($this->names[$name]);
            }
        }
    }

    /**
     * Add an item to the array, detecting key automatically.
     *
     * @param mixed $value The item to add.
     */
    public function append($value)
    {
        $offset = self::offsetMake($value);
        $this->offsetSet($offset, $value);
    }

    /**
     * Detect index for the item to add.
     *
     * @param mixed $value The item to add.
     * @throws \InvalidArgumentException If the item is not array or object.
     * @return array The new index.
     */
    protected static function offsetMake($value)
    {
        if (is_array($value)) {
            $offset = array_key_exists('name', $value) ?
                $value['name'] :
                [];
            $oid = $value['oid'];
        } elseif (is_object($value)) {
            $offset = $value->name;
            $oid = $value->oid;
        } else {
            throw new \InvalidArgumentException('Only arrays and objects allowed');
        }

        array_unshift($offset, $oid);

        return $offset;
    }

    /**
     * Build an instance from an array of values.
     *
     * @param array $array Values for the new instance.
     * @return OidArray New instance.
     */
    public static function fromArray($array)
    {
        $oid_array = new self();

        foreach ($array as $item) {
            $oid_array->append($item);
        }

        return $oid_array;
    }

    public function __get($name)
    {
        if ($this->offsetExists($name)) {
            $value = $this->offsetGet($name);
        } else {
            $value = null;
            trigger_error("Unknown property: $name");
        }

        return $value;
    }

    public function __set($name, $value)
    {
        if ($this->offsetExists($name)) {
        } else {
            throw new \RuntimeException("Unknown property: $name");
        }
    }

    public function __toString()
    {
        return json_encode($this->canonical_names, JSON_PRETTY_PRINT);
    }
}
