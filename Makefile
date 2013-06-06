document:
	phpdoc -d . -t docs

test:
	cd tests
	phpunit --verbose alltests.php
	cd ..

verify:
	phpcs --standard=PEAR Services/SmartFile/*.php
