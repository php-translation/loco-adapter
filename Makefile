.PHONY: ${TARGETS}

DIR := ${CURDIR}
QA_IMAGE := jakzal/phpqa:php8.1-alpine

cs-fix:
	@docker run --rm -v $(DIR):/project -w /project $(QA_IMAGE) php-cs-fixer fix -vvv

cs-diff:
	@docker run --rm -v $(DIR):/project -w /project $(QA_IMAGE) php-cs-fixer fix --dry-run -vvv

phpstan:
	@docker run --rm -v $(DIR):/project -w /project $(QA_IMAGE) phpstan analyze

phpunit:
	@vendor/bin/phpunit

static: cs-diff phpstan

test: static phpunit
