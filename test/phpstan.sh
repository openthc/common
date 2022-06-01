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
# PHPStan
if [ ! -f "$OUTPUT_BASE/phpstan.html" ]
then

	xsl_file="test/phpstan.xsl"
	out_file="$OUTPUT_BASE/phpstan.xml"
	out_html="$OUTPUT_BASE/phpstan.html"

	echo '<h1>PHPStan...</h1>' > "$OUTPUT_MAIN"

	vendor/bin/phpstan \
		analyze \
		--configuration=test/phpstan.neon \
		--error-format=junit \
		--level=2 \
		--no-ansi \
		--no-progress \
		"${SOURCE_LIST[@]}" \
		> "${out_file}" \
		|| true

	[ -f "${xsl_file}" ] || curl -qs 'https://openthc.com/pub/phpstan.xsl' > "${xsl_file}"

	xsltproc \
		--nomkdir \
		--output "${out_html}" \
		"${xsl_file}" \
		"${out_file}"

fi
