<?php
/**
 * LDAPException class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;

/** Retrieves LDAP last error. */
class LdapException extends \RuntimeException
{
    /**
     * Get exit code and description of the last LDAP operation.
     *
     * @param resource $connection LDAP connection handle.
     */
    public function __construct($connection)
    {
        $code = @ldap_errno($connection);
        $message = @ldap_err2str($code);
        parent::__construct($message, $code);
    }
}

