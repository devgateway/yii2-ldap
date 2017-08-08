<?php
namespace devgateway\ldap;

use devgateway\ldap\Search;
use yii\base\Component;

/**
 * Encapsulates a connection to LDAP server.
 *
 * Requires LDAPv3 support.
 */
class Connection extends Component
{
    const BASE = 0;
    const ONELEVEL = 1;
    const SUBTREE = 2;

    const MOD = 0;
    const MOD_ADD = 1;
    const MOD_DEL = 2;
    const MOD_REPLACE = 3;

    protected static $modFunctions = [
        MOD =>     'ldap_modify',
        MOD_ADD => 'ldap_mod_add',
        MOD_DEL => 'ldap_mod_del',
        MOD_REPLACE => 'ldap_mod_replace'
    ];

    /** @var resource|bool $conn LDAP connection handle. */
    protected $conn = false;
    /** @var bool $bound Flag indicating whether connection is in bound state. */
    protected $bound = false;

    /** @var string|null $host LDAP server URI, may include port number.
     * Can be multiple space-delimited URIs.
     */
    public $host = null;
    /** @var int $port Port number. Only used if $host is a hostname or an IP address.
     * IGNORED if $host is a URI.
     */
    public $port = 389;
    /** @var string|null Distinguished name for default bind. Anonymous bind used if null. */
    public $bind_dn = null;
    /** @var string|null Password for default bind.
     * Anonymous bind ALWAYS forced if null, even if $bind_dn is set.
     */
    public $bind_pw = null;

    /**
     * Initializes LDAP structures, or does nothing if already initialized.
     *
     * @throws \RuntimeException if settings are invalid.
     * @throws LdapException if LDAPv3 not supported.
     * @return void
     */
    protected function connect()
    {
        if ($this->conn !== false) {
          return;
        }

        $this->conn = ldap_connect($this->host, $this->port);
        if (!$this->conn) {
            throw new \RuntimeException("LDAP settings invalid");
        }

        $success = ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!$success) {
            throw new LdapException($this->conn);
        }
    }

    /**
     * Binds to LDAP server, if not already bound.
     *
     * @throws LdapException if bind failed.
     * @return void
     */
    protected function bind()
    {
        if ($this->bound) {
            return;
        }

        $this->connect();

        $success = ldap_bind($this->conn, $this->bind_dn, $this->bind_pw);
        if ($success) {
            $this->bound = true;
        } else {
            throw new LdapException($this->conn);
        }
    }

    /**
     * Binds to LDAP under another DN within the existing connection.
     *
     * @param string|null $bind_dn Distinguished name to bind with.
     * @param string|null $bind_pw Password to bind with.
     * @return void
     */
    public function rebind($bind_dn, $bind_pw)
    {
        $this->bind_dn = $bind_dn;
        $this->bind_pw = $bind_pw;

        $this->bound = false;
        $this->bind();
    }

    public function __destruct()
    {
        if ($this->conn !== false) {
            ldap_unbind($this->conn);
        }
    }

    /**
     * Escapes LDAP filter string as per RFC 4515.
     *
     * @param string Filter string.
     * @return string
     */
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

    /**
     * Return a Search object for lazy search.
     *
     * @see http://php.net/manual/en/function.ldap-search.php LDAP subtree search.
     * @see http://php.net/manual/en/function.ldap-list.php LDAP one level search.
     * @see http://php.net/manual/en/function.ldap-read.php LDAP base search.
     * @param int $scope Search scope, one of Connection::BASE, ONELEVEL, or SUBTREE.
     * @param string $base Search base.
     * @param string $filter Search filter. Must be properly escaped.
     * @param array $attrs Array of attributes to request from LDAP.
     * @param int $size_limit Limit search to this many results; 0 for no limit.
     * @param int $time_limit Limit search duration, in seconds; 0 for no limit.
     * @param int $deref Dereference aliases.
     * @param int $page_size Request paginated results, if supported.
     * @param bool $page_critical Raise an exception if pagination is not supported.
     * @return Search Iterator for lazy search.
     */
    public function search(
        int $scope,
        string $base,
        string $filter,
        array $attrs = [],
        int $size_limit = 0,
        int $time_limit = 0,
        int $deref = LDAP_DEREF_NEVER,
        int $page_size = 500,
        bool $page_critical = false
    ) {
        $this->connect();

        return new Search(
            $this->conn,
            $scope,
            $base,
            $filter,
            $attrs,
            $size_limit,
            $time_limit,
            $deref,
            $page_size,
            $page_critical
        );
    }

    public function add($dn, $entry, $bind_dn=null, $bind_pw=null)
    {
        if ($bind_dn && $bind_pw) {
            $this->rebind($bind_dn, $bind_pw);
        } else {
            $this->bind();
        }

        $success = ldap_add($this->conn, $dn, $entry);
        if (!$success) return LdapException($this->conn);
    }

    public function delete($dn, $bind_dn=null, $bind_pw=null)
    {
        if ($bind_dn && $bind_pw) {
            $this->rebind($bind_dn, $bind_pw);
        } else {
            $this->bind();
        }

        $success = ldap_delete($this->conn, $dn);
        if (!$success) return LdapException($this->conn);
    }

    public function modify($scope, $dn, $entry, $bind_dn=null, $bind_pw=null)
    {
        if ($bind_dn && $bind_pw) {
            $this->rebind($bind_dn, $bind_pw);
        } else {
            $this->bind();
        }

        $modifyFunction;
        if (array_key_exists($scope, self::$modFunctions)) {
            $modifyFunction = self::$functions[$scope];
        } else {
            $validScopes = implode(', ', array_keys(self::$modFunctions));
            $message = "Scope must be one of: $validScopes, not $scope";
            throw new \OutOfRangeException($message);
        }

        $success = ($modifyFunction)($this->conn, $dn, $entry);
        if (!$success) return LdapException($this->conn);
    }
}

