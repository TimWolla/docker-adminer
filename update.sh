#!/usr/bin/env bash
set -Eeuo pipefail

cd "$(dirname "$(readlink -f "$BASH_SOURCE")")"

versions=( "$@" )
if [ ${#versions[@]} -eq 0 ]; then
	versions=( */ )
fi
versions=( "${versions[@]%/}" )

read -r commit_hash fullVersion << EOF
$(git ls-remote --tags https://github.com/vrana/adminer.git \
	| awk '{gsub(/refs\/tags\/v/, "", $2); print}' \
	| sort -rVk2 \
	| head -1)
EOF

for version in "${versions[@]}"; do
	if [[ "$fullVersion" != $version* ]]; then
		echo >&2 "error: cannot determine full version for '$version'"
	fi

	echo "$version: $fullVersion"

	downloadSha256="$(
		curl -fsSL "https://github.com/vrana/adminer/releases/download/v${fullVersion}/adminer-${fullVersion}.php" \
			| sha256sum \
			| cut -d' ' -f1
	)"
	echo "  - adminer-${fullVersion}.php: $downloadSha256"

	sed -ri \
		-e 's/^(ENV\s+ADMINER_VERSION\s+).*/\1'"$fullVersion"'/' \
		-e 's/^(ENV\s+ADMINER_DOWNLOAD_SHA256\s+).*/\1'"$downloadSha256"'/' \
		-e 's/^(ENV\s+ADMINER_COMMIT\s+).*/\1'"$commit_hash"'/' \
		"$version/fastcgi/Dockerfile" \
		"$version/Dockerfile"
done
