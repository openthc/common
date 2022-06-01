#!/bin/bash
#
# expects $OUTPUT_BASE, $OUTPUT_MAIN, $SOURCE_LIST to exist
#

set -o errexit
set -o nounset

declare OUTPUT_BASE
declare OUTPUT_MAIN
declare SOURCE_LIST

if [ ! -f "$OUTPUT_BASE/phpcpd.txt" ]
then

	echo '<h1>CPD Check</h1>' > "$OUTPUT_MAIN"

	vendor/bin/phpcpd \
		--fuzzy \
		"${SOURCE_LIST[@]}" \
		> "$OUTPUT_BASE/phpcpd.txt" \
		2>&1 \
		|| true

		# --log-pmd="$output_base/phpcpd.xml" \
		# --no-ansi \

fi
