#!/usr/bin/env bash
set -Eeuo pipefail

declare -A aliases=(
	[4]='latest'
)

self="$(basename "$BASH_SOURCE")"
cd "$(dirname "$(readlink -f "$BASH_SOURCE")")"

if [ "$#" -eq 0 ]; then
	versions=( */ )
	versions=( "${versions[@]%/}" )
	eval "set -- $versions"
fi

# sort version numbers with highest first
IFS=$'\n'; set -- $(sort -rV <<<"$*"); unset IFS

# get the most recent commit which modified any of "$@"
fileCommit() {
	git log -1 --format='format:%H' HEAD -- "$@"
}

# get the most recent commit which modified "$1/Dockerfile" or any file COPY'd from "$1/Dockerfile"
dirCommit() {
	local dir="$1"; shift
	(
		cd "$dir"
		fileCommit \
			Dockerfile \
			$(git show HEAD:./Dockerfile | awk '
				toupper($1) == "COPY" {
					for (i = 2; i < NF; i++) {
						print $i
					}
				}
			')
	)
}

getArches() {
	local repo="$1"; shift
	local officialImagesBase="${BASHBREW_LIBRARY:-https://github.com/docker-library/official-images/raw/HEAD/library}/"

	local parentRepoToArchesStr
	parentRepoToArchesStr="$(
		find -name 'Dockerfile' -exec awk -v officialImagesBase="$officialImagesBase" '
				toupper($1) == "FROM" && $2 !~ /^('"$repo"'|scratch|.*\/.*)(:|$)/ {
					printf "%s%s\n", officialImagesBase, $2
				}
			' '{}' + \
			| sort -u \
			| xargs -r bashbrew cat --format '["{{ .RepoName }}:{{ .TagName }}"]="{{ join " " .TagEntry.Architectures }}"'
	)"
	eval "declare -g -A parentRepoToArches=( $parentRepoToArchesStr )"
}
getArches 'adminer'

cat <<-EOH
# this file is generated via https://github.com/TimWolla/docker-adminer/blob/$(fileCommit "$self")/$self

Maintainers: Tim Düsterhus <tim@bastelstu.be> (@TimWolla)
GitRepo: https://github.com/TimWolla/docker-adminer.git
EOH

# prints "$2$1$3$1...$N"
join() {
	local sep="$1"; shift
	local out; printf -v out "${sep//%/%%}%s" "$@"
	echo "${out#$sep}"
}

for version; do
	export version

	commit="$(dirCommit "$version")"

	fullVersion="$(git show "$commit":"$version/Dockerfile" | awk '
		$1 == "ENV" { env=1 }
		env {
			for (i=1; i <= NF; i++) {
				split($i, a, "=");
				if (a[1] == "ADMINER_VERSION") { print a[2]; exit }
			}
		}
		env && !/\\$/ { env=0 }
	')"

	versionAliases=(
		$fullVersion
		$version
		${aliases[$version]:-}
	)

	for variant in '' fastcgi; do
		export variant
		dir="$version${variant:+/$variant}"
		if [ ! -d "$dir" ]; then
			continue
		fi

		commit="$(dirCommit "$dir")"

		if [ -n "$variant" ]; then
			variantAliases=( "${versionAliases[@]/%/-$variant}" )
			variantAliases=( "${variantAliases[@]//latest-/}" )
		else
			variantAliases=( "${versionAliases[@]}" )
			variantAliases+=( "${versionAliases[@]/%/-standalone}" )
			variantAliases=( "${variantAliases[@]//latest-/}" )
		fi

		parent="$(awk 'toupper($1) == "FROM" { print $2 }' "$dir/Dockerfile")"
		arches="${parentRepoToArches[$parent]}"

		echo
		cat <<-EOE
			Tags: $(join ', ' "${variantAliases[@]}")
			Architectures: $(join ', ' $arches)
			GitCommit: $commit
			Directory: $dir
		EOE
	done
done
