FROM debian:bullseye

STOPSIGNAL SIGINT

RUN	export DEBIAN_FRONTEND="noninteractive" \
&&	set -x \
&&	apt-get update \
&&	apt-get install -y \
		php7.4-cli \
		php7.4-fpm \
		php7.4-mbstring \
		php7.4-mysql \
		php7.4-odbc \
		php7.4-pdo-dblib \
		php7.4-pgsql \
		php7.4-sqlite3 \
&&	rm -rf /var/lib/apt/lists/*

RUN	echo "upload_max_filesize = 128M" >> /etc/php/7.4/cli/conf.d/0-upload_large_dumps.ini \
&&	echo "post_max_size = 128M" >> /etc/php/7.4/cli/conf.d/0-upload_large_dumps.ini \
&&	echo "memory_limit = 1G" >> /etc/php/7.4/cli/conf.d/0-upload_large_dumps.ini \
&&	echo "max_execution_time = 600" >> /etc/php/7.4/cli/conf.d/0-upload_large_dumps.ini \
&&	echo "max_input_vars = 5000" >> /etc/php/7.4/cli/conf.d/0-upload_large_dumps.ini \
&&	echo "variables_order = \"EGPCS\"" >> /etc/php/7.4/cli/conf.d/0-env.ini \
&&	cp /etc/php/7.4/cli/conf.d/0-upload_large_dumps.ini /etc/php/7.4/fpm/conf.d/0-upload_large_dumps.ini

RUN	set -ex;\
	{ \
		echo '[global]'; \
		echo 'error_log = /proc/self/fd/2'; \
		echo; echo '; https://github.com/docker-library/php/pull/725#issuecomment-443540114'; echo 'log_limit = 8192'; \
		echo; \
		echo '[www]'; \
		echo '; if we send this to /proc/self/fd/1, it never appears'; \
		echo 'access.log = /proc/self/fd/2'; \
		echo; \
		echo 'clear_env = no'; \
		echo; \
		echo '; Ensure worker stdout and stderr are sent to the main error log.'; \
		echo 'catch_workers_output = yes'; \
		echo 'decorate_workers_output = no'; \
	} | tee /etc/php/7.4/fpm/pool.d/docker.conf; \
	{ \
		echo '[global]'; \
		echo 'daemonize = no'; \
		echo; \
		echo '[www]'; \
		echo 'listen = 9000'; \
	} | tee /etc/php/7.4/fpm/pool.d/zz-docker.conf; \
	sed -i '/^pid =/d' /etc/php/7.4/fpm/php-fpm.conf

RUN	groupadd -r adminer \
&&	useradd -r -g adminer adminer \
&&	mkdir -p /var/www/html \
&&	mkdir /var/www/html/plugins-enabled \
&&	chown -R adminer:adminer /var/www/html

WORKDIR /var/www/html

COPY	*.php /var/www/html/

ENV	ADMINER_VERSION 4.8.1
ENV	ADMINER_DOWNLOAD_SHA256 2fd7e6d8f987b243ab1839249551f62adce19704c47d3d0c8dd9e57ea5b9c6b3
ENV	ADMINER_COMMIT 1f173e18bdf0be29182e0d67989df56eadea4754

RUN	export DEBIAN_FRONTEND="noninteractive" \
&&	set -x \
&&	buildDeps='git curl ca-certificates' \
&&	apt-get update \
&&	apt-get install -y $buildDeps --no-install-recommends \
&&	rm -rf /var/lib/apt/lists/* \
&&	curl -fsSL "https://github.com/vrana/adminer/releases/download/v$ADMINER_VERSION/adminer-$ADMINER_VERSION.php" -o adminer.php \
&&	echo "$ADMINER_DOWNLOAD_SHA256  adminer.php" |sha256sum -c - \
&&	git clone --recurse-submodules=designs --depth 1 --shallow-submodules --branch "v$ADMINER_VERSION" https://github.com/vrana/adminer.git /tmp/adminer \
&&	commit="$(git -C /tmp/adminer/ rev-parse HEAD)" \
&&	[ "$commit" = "$ADMINER_COMMIT" ] \
&&	cp -r /tmp/adminer/designs/ /tmp/adminer/plugins/ . \
&&	rm -rf /tmp/adminer/ \
&&	apt-get purge -y --auto-remove $buildDeps

COPY	entrypoint.sh /usr/local/bin/
ENTRYPOINT	[ "entrypoint.sh" ]

USER	adminer
CMD	[ "php-fpm7.4" ]
