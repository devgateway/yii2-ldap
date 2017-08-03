<?php
namespace ldap;

class LdapConnectionError extends \Exception
{
}

class LdapAuthError extends \Exception
{
}

class LdapConnection
{
    protected $base;
    protected $conn;

    public function __construct()
    {
        require('settings.php');
        $this->base = $base;
        $this->conn = ldap_connect($host, $port);
        if (!$this->conn) throw new LdapConnectionError();

        $result = ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!$result) throw new LdapConnectionError();

        # bind anonymously
        $result = ldap_bind($this->conn);
        if (!$result) throw new LdapAuthError();
    }

    public function __destruct()
    {
        ldap_unbind($this->conn);
    }

    protected function fetchOneDN($filter, $base=null)
    {
        $base = $base ? $base : $this->base; # default to self

        # bind anonymously first and search for the RDN
        $result = ldap_bind($this->conn);
        if (!$result) throw new LdapConnectionError();

        $result = ldap_search($this->conn, $base, $filter,
        array("dn"), 1, 1, 0, LDAP_DEREF_ALWAYS);
        if (!$result) throw new LdapConnectionError();

        # get first entry from the search result
        $first = ldap_first_entry($this->conn, $result);
        if (!$first) throw new LdapConnectionError();

        # extract dn from the first entry in the result
        $dn = ldap_get_dn($this->conn, $first);
        if (!$dn) throw new LdapConnectionError();

        return $dn;
    }

    # escapes dangerous characters from the input string
    public static function escapeFilter($string)
    {
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

    protected function search($filter, $base = null, $attrs = null) {
        $base = $base ? $base : $this->base; # default to self

        $result = ldap_search($this->conn, $base, $filter);
        if (!$result) throw new LDAPAuthError();

        $first = ldap_first_entry($this->conn, $result);

        #TODO: change to return a LdapSearchResult
	return $first;
    }
}
