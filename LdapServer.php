<?php
namespace ldap;

class LdapServerError extends \Exception
{
}

class LdapAuthError extends \Exception
{
}
class LdapServer
{
    private $base;
    private $conn;
    private $filter;
    private $dn;

    public function __construct($user, $pass)
    {
        require('settings.php');
        $this->base = $base;
        $safeUser = self::escapeFilter($user);
        $this->filter = sprintf($filter, $safeUser);

        $this->conn = ldap_connect($host, $port);
        if (!$this->conn) throw new LdapServerError();

        $result = ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!$result) throw new LdapServerError();

        # search ldap tree for the user's DN
        $this->dn = $this->fetchDN($this->filter);

        # re-bind with the DN found
        $result = ldap_bind($this->conn, $this->dn, $pass);
        if (!$result) throw new LdapAuthError();
    }

    public function __destruct()
    {
        ldap_unbind($this->conn);
    }

    private function fetchDN($filter)
    {
        # bind anonymously first and search for the RDN
        $result = ldap_bind($this->conn);
        if (!$result) throw new LdapServerError();

        $result = ldap_search($this->conn, $this->base, $this->filter,
        array("dn"), 1, 1, 0, LDAP_DEREF_ALWAYS);
        if (!$result) throw new LdapServerError();

        # get first entry from the search result
        $first = ldap_first_entry($this->conn, $result);
        if (!$first) throw new LdapServerError();

        # extract dn from the first entry in the result
        $dn = ldap_get_dn($this->conn, $first);
        if (!$dn) throw new LdapServerError();

        return $dn;
    }

    # escapes dangerous characters from the input string
    private static function escapeFilter($string) {
        if (function_exists('ldap_escape')) {
            return ldap_escape($string, '', LDAP_ESCAPE_FILTER);
        } else {
            $map = array(
                '\\' => '\\5c', # gotta be first, see str_replace info
                '*' => '\\2a',
                '(' => '\\28',
                ')' => '\\29',
                "\0" => '\\00'
            );
            return str_replace(array_keys($map), $map, $string);
        }
    }
}
