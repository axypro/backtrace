php vendor/bin/phpcs --standard=PSR2 --encoding=utf-8 --ignore=vendor .
mkdir -p build/logs; &&
phpenv config-add travis/xdebug.ini &&
phpunit --coverage-clover build/logs/clover.xml;
