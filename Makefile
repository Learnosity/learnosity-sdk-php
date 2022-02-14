SED=$(shell which gsed || which sed)
VERSION=$(shell git describe | $(SED) s/^v//)
DIST_PREFIX=learnosity_sdk-
DIST=$(DIST_PREFIX)$(VERSION)

COMPOSER=composer
COMPOSER_INSTALL_FLAGS=--no-interaction --optimize-autoloader --classmap-authoritative

PHPUNIT=./vendor/bin/phpunit

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
devbuild: clean install-vendor-dev

prodbuild: clean install-vendor

release:
	@./release.sh

test: install-vendor-dev
	$(PHPUNIT) --do-not-cache-result

test-coverage: install-vendor-dev
	XDEBUG_MODE=coverage $(PHPUNIT) --do-not-cache-result

test-unit: install-vendor-dev
	$(PHPUNIT) --do-not-cache-result --testsuite unit

test-integration-env: install-vendor-dev
	$(PHPUNIT) --do-not-cache-result --testsuite integration

###
# dist rules
#
# build a dist zip file from the distdir, THEN run the tests in the dist dir,
# to avoid polluting the distfile with dev dependencies
###
dist: dist-test

# We want to clean first before copying into the .distdir so that we have a clean copy
dist-zip: clean-test clean-dist
	mkdir -p .$(DIST) # use a hidden directory so that it doesn't get copied into itself
	cp -R * .version .$(DIST)
	mv .$(DIST) $(DIST)
	rm -rf $(DIST)/vendor/
	$(COMPOSER) install --working-dir=$(DIST) $(COMPOSER_INSTALL_FLAGS) --no-dev
	rm -rf $(DIST)/release.sh
	zip -qr $(DIST).zip $(DIST)

# run tests in the distdir
dist-test: dist-zip install-vendor-dev
	$(PHPUNIT) --do-not-cache-result --no-logging --configuration=$(DIST)/phpunit.xml

###
# install vendor rules
###
install-vendor:
	if [[ ! -f vendor/autoload.php && ! -f ../../../vendor/autoload.php ]]; then \
		$(COMPOSER) install $(COMPOSER_INSTALL_FLAGS) --no-dev
	fi

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

clean-vendor:
	test ! -d vendor || rm -r vendor
	test ! -f composer.lock || rm -f composer.lock

###

.PHONY: quickstart check-quickstart devbuild prodbuild release test \
	test-coverage test-unit test-integration-env \
	dist dist-zip dist-test install-vendor install-vendor-dev \
	clean clean-test clean-dev clean-vendor clean-dist
