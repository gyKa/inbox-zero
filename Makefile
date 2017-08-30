phpcs:
	vendor/bin/phpcs --standard=PSR2 src/

phpmd:
	vendor/bin/phpmd src/ text codesize,unusedcode,naming
