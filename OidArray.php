<?php
namespace ldap;

class OidArray implements \ArrayAccess
{
    protected $oids;
    protected $names;
    protected $canonical_names;

    private static function validateOid($oid)
    {
        // OIDs start with 0, 1, or 2, and consist of dot-separated numbers
        if (preg_match('/^[0-2](\.\d+)+$/', $oid)) {
            return true;
        } else {
            return false;
        }
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
        if (! $this->validateOid($oid) {
            throw new \UnexpectedValueException("Invalid OID: $oid");
        }
        $self->oids[$oid] = $value;
        // canonical name defaults to OID
        $this->canonical_names[$oid] = $oid;

        $max_length = 0;
        foreach ($offset as $name) {
            $idx = strtolower($name);
            $self->names[$idx] = $value;

            // make the longest name the canonical name
            $name_length = strlen($name);
            if ($name_length > $max_length) {
                $max_length = $name_length;
                $self->canonical_names[$oid] = $name;
            }
        }
    }

    public function offsetUnset($offset) {
        $old_value = $self->offsetGet($offset);

        foreach ($this->oids as $oid => $value) {
            if ($value === $old_value) {
                unset($this->oids[$oid]);
                unset($this->canonical_names[$oid]);
                break; // values by OID are unique
            }
        }

        foreach ($this->names as $name => $value) {
            if ($value === $old_value) {
                unset($this->names[$name]);
            }
        }
    }
}

