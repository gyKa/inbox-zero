phpcs:
	vendor/bin/phpcs --standard=PSR2 app.php

phpmd:
	vendor/bin/phpmd app.php text codesize,unusedcode,naming
