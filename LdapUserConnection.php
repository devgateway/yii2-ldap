<?php
namespace ldap;

class LdapUserConnection extends LdapConnection
{
    private $filter;
    private $dn;

    public function __construct($user, $pass)
    {
        require('settings.php');
        parent::__construct();
        $safeUser = self::escapeFilter($user);
        $this->filter = sprintf($filter, $safeUser);

        # search ldap tree for the user's DN
        $this->dn = $this->fetchOneDN($this->filter);

        # re-bind with the DN found
        $result = ldap_bind($this->conn, $this->dn, $pass);
        if (!$result) throw new LdapAuthError();
    }
}

