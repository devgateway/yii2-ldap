<?php
/**
 * LexingException
 *
 * @link https://tools.ietf.org/html/rfc4512
 * @link https://github.com/devgateway/yii2-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

/** Thrown when tokenizing schema definition fails. */
class LexingException extends \RuntimeException
{
    /**
     * Format the error message with error position and description.
     *
     * @param string $description Schema element definition.
     * @param int $position Tokenizer position where the error occured.
     * @param string $msg Additional error information.
     */
    public function __construct(&$description, $position, $msg)
    {
        $desc = substr($description, 0, 47) . '...';
        parent::__construct("$msg at position $position in: $desc");
    }
}
