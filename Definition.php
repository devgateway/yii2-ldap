<?php
/**
 * Definition class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

abstract class Definition
{
    protected $oid;
    protected $name = [];
    protected $desc = '';
    protected $obsolete = false;
    protected $sup = null;

    protected $properties;
    protected $short_name;

    abstract protected function parseSchema(string $schema);

    public function __construct(string $schema, OidArray &$definitions)
    {
        $properties = $this->parseSchema($schema);

        foreach ($properties as $name => $value) {
            $this->properties[$name] = $value;
        }

        $this->short_name = self::getShortName(
            $this->properties['oid'],
            $this->properties['name']
        );
    }

    public function getShortName()
    {
        $max_length = 0;
        foreach ($self->name as $name) {
            $length = strlen($name);
            if ($length > $max_length) {
                $max_length = $length;
                $short_name = $name;
            }
        }

        return isset($short_name) ? $short_name : $self->oid;
    }
    public function __get(string $name)
    {
        return property_exists($this, $name) ? $this->$name : null;
    }
}

