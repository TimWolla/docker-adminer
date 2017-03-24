#!/bin/sh
set -e

if [ -n "$ADMINER_DESIGN" ]; then
	ln -s "designs/$ADMINER_DESIGN/adminer.css" .
fi

exec "$@"
