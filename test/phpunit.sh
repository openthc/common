#!/bin/bash
#
# expects $OUTPUT_BASE, $OUTPUT_MAIN, $SOURCE_LIST to exist
#

set -o errexit
set -o nounset

declare OUTPUT_BASE
declare OUTPUT_MAIN
# declare SOURCE_LIST

#
# PHPUnit
if [ ! -f "$OUTPUT_BASE/phpunit.html" ]
then

	xsl_file="test/phpunit.xsl"

	echo '<h1>PHPUnit...</h1>' > "$OUTPUT_MAIN"

	vendor/bin/phpunit \
		--configuration "test/phpunit.xml" \
		--log-junit "$OUTPUT_BASE/phpunit.xml" \
		--testdox-html "$OUTPUT_BASE/testdox.html" \
		--testdox-text "$OUTPUT_BASE/testdox.txt" \
		--testdox-xml "$OUTPUT_BASE/testdox.xml" \
		"$@" 2>&1 | tee "$OUTPUT_BASE/phpunit.txt"


	[ -f "$xsl_file" ] || curl -qs 'https://openthc.com/pub/phpunit/report.xsl' > "$xsl_file"

	xsltproc \
		--nomkdir \
		--output "$OUTPUT_BASE/phpunit.html" \
		"$xsl_file" \
		"$OUTPUT_BASE/phpunit.xml"

fi
