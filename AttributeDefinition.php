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
    public function __construct(array $properties) {
        $self->properties['single_value'] =         $properties['single_value'];
        $self->properties['no_user_modification'] = $properties['no_user_modification'];

        if ($properties['syntax']) {
        } else {
            // sup
        }

        parent::__construct($properties);
    }
}

