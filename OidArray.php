<?php
namespace devgateway\ldap;

class OidArray implements \ArrayAccess, \IteratorAggregate
{
    protected $oids;
    protected $names;
    protected $canonical_names;

    private static function validateOid($oid)
    {
        // OIDs start with 0, 1, or 2, and consist of dot-separated numbers
        if (preg_match('/^[0-2](\.\d+)*$/', $oid)) {
            return true;
        } else {
            return false;
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->canonical_names);
    }

    public function offsetExists($offset)
    {
        $idx = strtolower($offset);
        return isset($this->names[$idx]) || isset($this->oids[$idx]);
    }

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

    public function offsetSet($offset, $value) {
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
            $this->names[$idx] = $value;

            // make the longest name the canonical name
            $name_length = strlen($name);
            if ($name_length > $max_length) {
                $max_length = $name_length;
                $canonical_name = $name;
            }
        }

        $this->canonical_names[$canonical_name] = $value;
    }

    public function offsetUnset($offset) {
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
}

