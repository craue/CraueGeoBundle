#!/bin/sh

export COMPOSER_NO_INTERACTION=1
composer self-update
composer config -g preferred-install source

if [ -n "${MIN_STABILITY:-}" ]; then
	sed -i -e "s/\"minimum-stability\": \"stable\"/\"minimum-stability\": \"${MIN_STABILITY}\"/" composer.json
fi

composer remove --no-update symfony/framework-bundle
composer require --no-update --dev symfony/symfony:${SYMFONY_VERSION}
composer update
