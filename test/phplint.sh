#!/bin/bash
#
# expects $OUTPUT_BASE, $OUTPUT_MAIN, $SOURCE_LIST to exist
#

set -o errexit
set -o nounset

declare OUTPUT_BASE
declare OUTPUT_MAIN
declare SOURCE_LIST

out_file="${OUTPUT_BASE}/phplint.txt"

IFS=" "
read -r -a src_list <<< "${SOURCE_LIST}"


if [ ! -f "${out_file}" ]
then

	echo '<h1>Linting...</h1>' > "$OUTPUT_MAIN"

	find "${src_list[@]}" -type f -name '*.php' -exec php -l {} \; \
		| grep -v 'No syntax' \
		>"${out_file}" \
		2>&1 \
		|| true

	[ -s "${out_file}" ] || echo "Linting OK" >"${out_file}"

fi
