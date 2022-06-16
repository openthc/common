#!/bin/bash
#
# expects $OUTPUT_BASE, $OUTPUT_MAIN, $SOURCE_LIST to exist
#

set -o errexit
set -o nounset

declare OUTPUT_BASE
declare OUTPUT_MAIN
declare SOURCE_LIST

if [ ! -f "$OUTPUT_BASE/phplint.txt" ]
then

	echo '<h1>Linting...</h1>' > "$OUTPUT_MAIN"

	find "${SOURCE_LIST[@]}" -type f -name '*.php' -exec php -l {} \; \
		| grep -v 'No syntax' \
		>"$OUTPUT_BASE/phplint.txt" \
		2>&1 \
		|| true

	[ -s "$OUTPUT_BASE/phplint.txt" ] || echo "Linting OK" >"$OUTPUT_BASE/phplint.txt"

fi
