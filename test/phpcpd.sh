#!/bin/bash
#
# expects $OUTPUT_BASE, $OUTPUT_MAIN, $SOURCE_LIST to exist
#

set -o errexit
set -o nounset

declare OUTPUT_BASE
declare OUTPUT_MAIN
declare SOURCE_LIST

#
# PHPCPD
if [ ! -f "$OUTPUT_BASE/phpcpd.txt" ]
then

	# upscale to array
	src_list=($SOURCE_LIST)

	echo '<h1>CPD Check</h1>' > "$OUTPUT_MAIN"

	vendor/bin/phpcpd \
		--fuzzy \
		"${src_list[@]}" \
		> "$OUTPUT_BASE/phpcpd.txt" \
		2>&1 \
		|| true

		# --log-pmd="$output_base/phpcpd.xml" \
		# --no-ansi \

fi
