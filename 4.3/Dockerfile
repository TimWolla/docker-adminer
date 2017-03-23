FROM php:7.0-alpine

RUN	addgroup -S adminer \
&&	adduser -S -G adminer adminer \
&&	mkdir -p /var/www/html

WORKDIR /var/www/html

RUN	apk add --no-cache libpq

RUN	set -x \
&&	apk add --no-cache --virtual .build-deps \
	postgresql-dev \
	sqlite-dev \
&&	docker-php-ext-install pdo_mysql pdo_pgsql pdo_sqlite \
&&	apk del .build-deps

COPY	index.php .

ENV	ADMINER_VERSION 4.3.0
ENV	ADMINER_DOWNLOAD_SHA256 3584e1b3c7f853118d66a92f9c0996c8db39b05ac2a2b2623ff1676ec09bc08e

RUN	curl -fsSL https://www.adminer.org/static/download/$ADMINER_VERSION/adminer-$ADMINER_VERSION-en.php -o adminer.php \
&&	echo "$ADMINER_DOWNLOAD_SHA256  adminer.php" |sha256sum -c -

USER	adminer
CMD	[ "php", "-S", "0.0.0.0:8080", "-t", "/var/www/html" ]

EXPOSE 8080
