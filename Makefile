install:
	composer install --prefer-dist --no-dev
	cp .env.example .env

dev-install:
	composer install --prefer-dist
	cp .env.example .env

phpcs:
	vendor/bin/phpcs --standard=PSR2 src/

phpmd:
	vendor/bin/phpmd src/ text codesize,unusedcode,naming
