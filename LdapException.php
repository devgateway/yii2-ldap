<?php
namespace devgateway\ldap;

class LdapException extends \RuntimeException
{
    public function __construct($connection)
    {
        $code = @ldap_errno($connection);
        $message = @ldap_err2str($code);
        parent::__construct($message, $code);
    }
}

