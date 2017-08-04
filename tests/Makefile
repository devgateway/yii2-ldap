.PHONY: test connection

test: OidArrayTest.php
	phpunit --bootstrap autoload.php $^

connection: LdapConnectionTest.php
	phpunit --bootstrap autoload.php $^
