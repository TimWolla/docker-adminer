FROM php:7.3-fpm-alpine

RUN	echo "upload_max_filesize = 128M" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini \
&&	echo "post_max_size = 128M" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini \
&&	echo "memory_limit = 1G" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini \
&&	echo "max_execution_time = 600" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini \
&&	echo "max_input_vars = 5000" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini

RUN	addgroup -S adminer \
&&	adduser -S -G adminer adminer \
&&	mkdir -p /var/www/html \
&&	mkdir -p /var/www/html/plugins-enabled \
&&	chown -R adminer:adminer /var/www/html

RUN	apk add --no-cache libpq

RUN	set -x \
&&	apk add --no-cache --virtual .build-deps \
	postgresql-dev \
	sqlite-dev \
&&	docker-php-ext-install pdo_mysql pdo_pgsql pdo_sqlite \
&&	apk del .build-deps

COPY	*.php /var/www/html/

ENV	ADMINER_VERSION 4.7.4
ENV	ADMINER_DOWNLOAD_SHA256 b8c4e0c6f7dfd9b7a7421be17ec5d6b1259e180d5ff4b79645621ce1d5575e66
ENV	ADMINER_SRC_DOWNLOAD_SHA256 aa28890a1c1832c81329d18b72f05145fb9f78b13062129319256c3231b50e85

RUN	set -x \
&&	curl -fsSL https://github.com/vrana/adminer/releases/download/v$ADMINER_VERSION/adminer-$ADMINER_VERSION.php -o adminer.php \
&&	echo "$ADMINER_DOWNLOAD_SHA256  adminer.php" |sha256sum -c - \
&&	curl -fsSL https://github.com/vrana/adminer/archive/v$ADMINER_VERSION.tar.gz -o source.tar.gz \
&&	echo "$ADMINER_SRC_DOWNLOAD_SHA256  source.tar.gz" |sha256sum -c - \
&&	tar xzf source.tar.gz --strip-components=1 "adminer-$ADMINER_VERSION/designs/" "adminer-$ADMINER_VERSION/plugins/" \
&&	rm source.tar.gz

COPY	entrypoint.sh /usr/local/bin/
ENTRYPOINT	[ "entrypoint.sh", "docker-php-entrypoint" ]

USER	adminer
CMD	[ "php-fpm" ]
