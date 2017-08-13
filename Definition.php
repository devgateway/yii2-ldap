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
    protected $desc = '';
    protected $obsolete = false;
    protected $sup = null;
    protected $short_name;

    abstract public static function parse(string $definition);

    public function __construct(
        string $oid,
        array $name,
        string $desc = '',
        $sup = null,
        bool $obsolete = false
    ) {
    }

    protected function getShortName()
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
}

