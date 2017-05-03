#!/bin/sh
set -e

if [ -n "$ADMINER_DESIGN" ]; then
	ln -sf "designs/$ADMINER_DESIGN/adminer.css" .
fi

number=1
for PLUGIN in $ADMINER_PLUGINS; do
	php plugin-loader.php "$PLUGIN" > plugins-enabled/$(printf "%03d" $number)-$PLUGIN.php
	number=$(($number+1))
done

exec "$@"
