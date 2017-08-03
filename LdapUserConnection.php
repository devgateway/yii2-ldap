<?php
namespace ldap;

class LdapUserConnection extends LdapConnection
{
    private $base;
    private $filter;
    private $dn;

    public function __construct($user, $pass, $base, $filter, $host, $port = null)
    {
        $this->base = $base;
        parent::__construct($host, $port);
        $safeUser = self::escapeFilter($user);
        $this->filter = sprintf($filter, $safeUser);

        # search ldap tree for the user's DN
        $this->dn = $this->fetchOneDN($this->base, $this->filter);

        # re-bind with the DN found
        $result = ldap_bind($this->conn, $this->dn, $pass);
        if (!$result) throw new LdapAuthError();
    }
}

