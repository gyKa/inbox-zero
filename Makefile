install: environment
	composer install --prefer-dist --no-dev

dev-install: environment
	composer install --prefer-dist

environment:
	cp .env.example .env

qa: parallel-lint phpcs phpmd phpcpd

parallel-lint:
	vendor/bin/parallel-lint -e php src/

phpcs:
	vendor/bin/phpcs --standard=PSR2 src/

phpmd:
	vendor/bin/phpmd src/ text codesize,unusedcode,naming

phpcpd:
	vendor/bin/phpcpd src/
