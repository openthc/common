#!/bin/bash
#
# expects $OUTPUT_BASE, $OUTPUT_MAIN, $SOURCE_LIST to exist
#

set -o errexit
set -o nounset

declare OUTPUT_BASE
declare OUTPUT_MAIN
declare SOURCE_LIST

# upscale to array
IFS=" "
read -r -a src_list <<< "${SOURCE_LIST}"

#
# PHPCPD
if [ ! -f "$OUTPUT_BASE/phpcpd.txt" ]
then

	echo '<h1>CPD Check</h1>' > "$OUTPUT_MAIN"

	vendor/bin/phpcpd \
		--fuzzy \
		"${src_list[@]}" \
		> "$OUTPUT_BASE/phpcpd.txt" \
		2>&1 \
		|| true

fi
