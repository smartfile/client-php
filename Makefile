document:
	phpdoc -d . -t docs

test:
	phpunit --verbose smartfiletest.php

verify:
	phpcs --standard=PEAR smartfileapi.php
