SOURCE_FILES := $(wildcard *.php)
.PHONY: test

all: composer.lock | test docs

test:
	phpunit --bootstrap tests/bootstrap.php tests

docs: ./vendor/bin/phpdoc $(SOURCE_FILES)
	rm -rf $@
	$< -t $@ $(foreach file,$(SOURCE_FILES),-f $(file))

composer.lock: composer.json
	composer update
