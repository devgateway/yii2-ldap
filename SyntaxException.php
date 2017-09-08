<?php
/**
 * SyntaxException class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

/** Thrown when a value doesn't conform to syntax rules. */
class SyntaxException extends \RuntimeException
{
    /**
     * Build a message with an optional list of permitted values.
     *
     * @param string $value The value that violates the syntax rules.
     * @param mixed[] $expected List of permitted values.
     */
    public function __construct($value, $expected = [])
    {
        if (empty($expected)) {
            $msg = "Value '$value' invalid per syntax";
        } else {
            $values = implode(', ', $expected);
            $msg = "Value '$value' invalid. Expected one of: $values";
        }

        parent::__construct($msg);
    }
}
