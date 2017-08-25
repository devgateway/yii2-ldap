<?php
/**
 * Connection class
 *
 * @link https://github.com/devgateway/yii-com-ldap
 * @copyright 2017, Development Gateway, Inc
 * @license GPL, version 3
 */

namespace devgateway\ldap;
use devgateway\ldap\Search;
use yii\base\Component;

/**
 * Encapsulates a connection to LDAP server.
 *
 * Requires LDAPv3. Supports implicit paginated search, and falls back to
 * normal search, unless critical flag requested.
 */
class Connection extends Component
{
    const BASE =     0;
    const ONELEVEL = 1;
    const SUBTREE =  2;

    const MOD =         0;
    const MOD_ADD =     1;
    const MOD_DEL =     2;
    const MOD_REPLACE = 3;

    /** @var array $mod_functions Wrapped native LDAP functions. */
    protected static $mod_functions = [
        self::MOD =>         'ldap_modify',
        self::MOD_ADD =>     'ldap_mod_add',
        self::MOD_DEL =>     'ldap_mod_del',
        self::MOD_REPLACE => 'ldap_mod_replace'
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

    /** Unbind from LDAP server, and invalidate connection handle. */
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
     * @param string $filter Properly escaped search filter; default: '(objectClass=*)'.
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
        string $filter = '(objectClass=*)',
        array $attrs = [],
        int $size_limit = 0,
        int $time_limit = 0,
        int $deref = LDAP_DEREF_NEVER,
        int $page_size = 500,
        bool $page_critical = false
    ) {
        $this->bind();

        if ($filter == '') {
            $filter = '(objectClass=*)';
        }

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

    /**
     * Add an entry to the directory.
     *
     * @param string $dn Entry distinguished name.
     * @param mixed[] $entry Array of attributes: name => value.
     * @throws LdapException If add operation failed.
     * @return void
     */
    public function add($dn, $entry)
    {
        $this->bind();

        $success = ldap_add($this->conn, $dn, $entry);
        if (!$success) {
            throw new LdapException($this->conn);
        }
    }

    /**
     * Deletes an entry from the directory.
     *
     * @param string $dn Entry distinguished name.
     * @throws LdapException If delete operation failed.
     */
    public function delete($dn)
    {
        $this->bind();

        $success = ldap_delete($this->conn, $dn);
        if (!$success) {
            throw new LdapException($this->conn);
        }
    }

    /**
     * Modifies an object or an object attribute depending on $op
     *
     * @param string $op One of: MOD, MOD_ADD, MOD_DEL, MOD_REPLACE.
     * @param string $dn Distinguished name to be modified.
     * @param mixed[] $entry Array of attributes: name => value.
     * @throws OutOfRangeException If $op not one of predefined constants.
     * @throws LdapException If modify operation failed.
     */
    public function modify($op, $dn, $entry)
    {
        $this->bind();

        if (array_key_exists($op, self::$mod_functions)) {
            $modify_function = self::$mod_functions[$op];
        } else {
            $validOps = implode(', ', array_keys(self::$mod_functions));
            $message = "Scope must be one of: $validOps, not $op";
            throw new \OutOfRangeException($message);
        }

        $success = $modify_function($this->conn, $dn, $entry);
        if (!$success) throw new LdapException($this->conn);
    }

    /**
     * Renames or moves an entry.
     *
     * @param string $dn Entry distinguished name.
     * @param string $new_rdn New relative distinguished name.
     * @param string $new_parent New parent or superior entry.
     * @param boolean $delete_old_rdn Move if true, copy if false.
     * @throws LdapException If rename operation failed.
     */
    public function rename($dn, $new_rdn, $new_parent, $delete_old_rdn )
    {
        $this->bind();

        $success = ldap_rename($this->conn, $dn, $new_rdn, $new_parent, $delete_old_rdn);
        if (!$success) {
            throw new LdapException($this->conn);
        }
    }
}

