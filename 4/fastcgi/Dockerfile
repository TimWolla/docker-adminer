FROM php:8.0-rc-fpm-alpine

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

RUN	set -x \
&&	apk add --no-cache --virtual .build-deps \
	postgresql-dev \
	sqlite-dev \
	unixodbc-dev \
	freetds-dev \
&&	docker-php-ext-configure pdo_odbc --with-pdo-odbc=unixODBC,/usr \
&&	docker-php-ext-install \
	pdo_mysql \
	pdo_pgsql \
	pdo_sqlite \
	pdo_odbc \
	pdo_dblib \
&&	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)" \
&&	apk add --virtual .phpexts-rundeps $runDeps \
&&	apk del .build-deps

COPY	*.php /var/www/html/

ENV	ADMINER_VERSION 4.7.7
ENV	ADMINER_DOWNLOAD_SHA256 566152a2d86186dcfb28856b4ed0dfdb60e355d93ab693f7931201f75868fff0
ENV	ADMINER_SRC_DOWNLOAD_SHA256 5d28c03739ce647a48ebe77e8db247b7f5ec7a2ee66ccabe00743c117d50e319

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
