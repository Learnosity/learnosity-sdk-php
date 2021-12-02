SED=$(shell which gsed || which sed)
VERSION=$(shell git describe | $(SED) s/^v//)
DIST_PREFIX=learnosity_sdk-
DIST=$(DIST_PREFIX)$(VERSION)

COMPOSER=composer
COMPOSER_INSTALL_FLAGS=--no-interaction --optimize-autoloader --classmap-authoritative

PHPUNIT=./vendor/bin/phpunit

all: test dist

###
# quickstart rules
###
quickstart: check-quickstart
	cd docs/quickstart && php -S localhost:8000

check-quickstart:
	if [[ ! -f vendor/autoload.php && ! -f ../../../vendor/autoload.php ]]; then \
		$(COMPOSER) install $(COMPOSER_INSTALL_FLAGS) --no-dev; \
	fi

###
# internal tooling rules
###
devbuild: install-vendor-dev

prodbuild: dist

release:
	@./release.sh

test: install-vendor-dev
	$(PHPUNIT)

test-coverage: install-vendor-dev
	XDEBUG_MODE=coverage $(PHPUNIT)

test-unit: install-vendor-dev
	$(PHPUNIT) --testsuite unit

test-integration-env: install-vendor-dev
	$(PHPUNIT) --testsuite integration

test-dist: dist-test

build-clean: clean

###
# dist rules
#
# build a dist zip file from the distdir, THEN run the tests in the dist dir,
# to avoid polluting the distfile with dev dependencies
###
dist: dist-zip dist-test

# We want to clean first before copying into the .distdir so that we have a clean copy
dist-zip: clean
	mkdir -p .$(DIST) # use a hidden directory so that it doesn't get copied into itself
	cp -R * .version .$(DIST)
	mv .$(DIST) $(DIST)
	$(MAKE) -C $(DIST) install-vendor # install the composer vendor inside the dist dir
	rm $(DIST)/release.sh
	rm $(DIST)/Makefile # this step needs to be the last step before zipping
	zip -qr $(DIST).zip $(DIST)

# run tests in the distdir
dist-test: dist-zip install-vendor-dev
	$(PHPUNIT) -c $(DIST)/phpunit.xml

###
# install vendor rules
###
install-vendor:
	$(COMPOSER) install $(COMPOSER_INSTALL_FLAGS) --no-dev

install-vendor-dev:
	if [[ ! -f vendor/autoload.php && ! -f ../../../vendor/autoload.php ]]; then \
		$(COMPOSER) install $(COMPOSER_INSTALL_FLAGS); \
	fi

###
# cleaning rules
###
clean: clean-dist clean-test clean-vendor
	rm -rf $(DIST_PREFIX)*.zip

clean-dist:
	rm -rf $(DIST_PREFIX)*/

clean-test:
	test ! -f tests/junit.xml || rm -f tests/junit.xml
	test ! -f tests/coverage.xml || rm -f tests/coverage.xml
	test ! -d tests/coverage || rm -rf tests/coverage
	test ! -f .phpunit.result.cache || rm -f .phpunit.result.cache

clean-vendor:
	test ! -d vendor || rm -r vendor
	test ! -f composer.lock || rm -f composer.lock

###

.PHONY: quickstart check-quickstart devbuild prodbuild release test \
	test-coverage test-unit test-integration-env test-dist build-clean \
	dist dist-zip dist-test install-vendor install-vendor-dev \
	clean clean-test clean-dev clean-vendor
