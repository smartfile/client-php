document:
	phpdoc -d . -t docs

test:
	(cd tests && phpunit --verbose .)

verify:
	phpcs --standard=PEAR Services/SmartFile/*.php
