TEST_FILES := $(wildcard *Test.php)
.PHONY: test

test: $(TEST_FILES)
	set -e; for test in $(TEST_FILES); do phpunit --bootstrap autoload.php $$test; done
