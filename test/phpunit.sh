#!/bin/bash
#
# expects $OUTPUT_BASE, $OUTPUT_MAIN, $SOURCE_LIST to exist
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

SCRIPT_PATH=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

declare OUTPUT_BASE
declare OUTPUT_MAIN

#
# PHPUnit
xsl_file="test/phpunit.xsl"

echo '<h1>PHPUnit...</h1>' > "$OUTPUT_MAIN"
vendor/bin/phpunit \
	--configuration "test/phpunit.xml" \
	--log-junit "$OUTPUT_BASE/phpunit.xml" \
	--testdox-html "$OUTPUT_BASE/testdox.html" \
	--testdox-text "$OUTPUT_BASE/testdox.txt" \
	--testdox-xml "$OUTPUT_BASE/testdox.xml" \
	"$@" 2>&1 | tee "$OUTPUT_BASE/phpunit.txt"

# Transform
"$SCRIPT_PATH/phpunit-xml2html.php" \
	"$OUTPUT_BASE/phpunit.xml" "$OUTPUT_BASE/phpunit.html"
