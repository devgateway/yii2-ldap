<?php
namespace devgateway\ldap;

use devgateway\ldap\Connection;

class PagedResults implements \Iterator
{
    protected $conn;
    protected static $functions = [
        Connection::BASE =>     'ldap_read',
        Connection::ONELEVEL => 'ldap_list',
        Connection::SUBTREE =>  'ldap_search'
    ];

    public function __construct(
        $conn,
        $scope,
        string $base,
        string $filter,
        array $attrs = [],
        int $sizelimit = 0,
        int $timelimit = 0,
        int $deref = LDAP_DEREF_NEVER
    ) {
        $this->conn = $conn;

        // validate search scope
        if (array_key_exists($scope, self::functions)) {
            $function = self::functions[$scope];
        } else {
            $valid_scopes = implode(', ', array_keys(self::functions));
            $message = "Scope must be one of: $valid_scopes, not $scope";
            throw new \OutOfRangeException($message);
        }

        // send pagination control
        if ($this->page_size) {
        }

        // call appropriate search function
        $result = @$function(
            $this->conn,
            $base,
            $filter,
            $attrs,
            $sizelimit,
            $timelimit,
            $deref
        );
        if (!$result) {
            throw new LdapException();
        }

        return new Results($this->conn, $result);
    }
}

