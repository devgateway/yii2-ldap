SOURCE_FILES := $(wildcard *.php)
.PHONY: test

test:
	phpunit --bootstrap vendor/autoload.php tests

docs: ./vendor/bin/phpdoc $(SOURCE_FILES)
	rm -rf $@
	$< -t $@ $(foreach file,$(SOURCE_FILES),-f $(file))
