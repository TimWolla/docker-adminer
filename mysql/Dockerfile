FROM php:fpm
MAINTAINER Tim Düsterhus

RUN	docker-php-ext-install pdo_mysql

COPY	index.php .

ENV	ADMINER_VERSION 4.2.5
ENV	ADMINER_DOWNLOAD_SHA256 1e1813745f670c469a1c368c45d159ec55656f0a31ed966065a9ca6edd27acc1

RUN	curl -fsSL https://www.adminer.org/static/download/$ADMINER_VERSION/adminer-$ADMINER_VERSION-en.php -o adminer.php \
&&	echo "$ADMINER_DOWNLOAD_SHA256 adminer.php" |sha256sum -c -
