TEST_FILES := $(wildcard tests/*Test.php)
.PHONY: test

test: $(TEST_FILES)
	set -e; for test in $(TEST_FILES); do phpunit --bootstrap vendor/autoload.php $$test; done
