THIS_FILE := $(lastword $(MAKEFILE_LIST))

include .env.local

## ---------
##	Testing
## ---------

test: test_php

test_php: phpunit phpcs phpstan ## Run all PHP tests (PHPCS, PHPUnit, and PHPStan)

## ---------
##	Unit tests
## ---------

phpunit: ## Check that PHP unit tests pass
	./vendor/bin/phpunit tests/php

## ---------
##	Coding standards
## ---------

phpcs: ## Check that PHP complies with stylistic rules
	./vendor/bin/phpcs -p --encoding=utf-8 --standard=PSR12 --error-severity=1 src/php tests/php

## ---------
##	Static analysis
## ---------

phpstan: ## Check that PHP passes static analysis
	./vendor/bin/phpstan analyse --memory-limit=-1 src/php tests/php --level 8

## ---------
##	Make setup
## ---------

_PHONY: help

.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(THIS_FILE) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
