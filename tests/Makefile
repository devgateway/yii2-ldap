TEST_FILES := $(wildcard *Test.php)
.PHONY: test

test: $(TEST_FILES)
	phpunit --bootstrap autoload.php $(TEST_FILES)
