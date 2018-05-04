VERSION=$(shell cat .version 2>/dev/null || git describe | sed s/^v//)
DIST=learnosity_sdk-$(VERSION)

COMPOSER=composer
COMPOSER_INSTALL_FLAGS=--no-suggest --no-interaction

PHPUNIT=./vendor/bin/phpunit

all: test dist

devbuild: install-vendor-dev

prodbuild: dist

test: test-unit

test-unit:
	$(PHPUNIT)

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
	cp -R * .$(DIST)
	mv .$(DIST) $(DIST)
	echo $(VERSION) > $(DIST)/.version # save the version so we can rerun the same tag
	$(MAKE) -C $(DIST) install-vendor # install the composer vendor inside the dist dir
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
	$(COMPOSER) install $(COMPOSER_INSTALL_FLAGS)

###
# cleaning rules
###
clean: clean-dist clean-test clean-vendor
	test ! -e $(DIST).zip || rm $(DIST).zip

clean-dist:
	test ! -d .$(DIST)/ || rm -r .$(DIST)/
	test ! -d $(DIST)/ || rm -r $(DIST)/

clean-test:
	rm -f src/tests/junit.xml

clean-vendor:
	test ! -d vendor || rm -r vendor
