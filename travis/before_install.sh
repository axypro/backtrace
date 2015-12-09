if [ ! "$NOXDEBUG" = "yes" ]; then phpenv config-rm xdebug.ini; fi;
composer self-update
