#!/bin/bash
#
# expects $output_base and $output_main to exist
#

set -o errexit
set -o nounset

#
# PHPStan
if [ ! -f "$output_base/phpstan.html" ]
then

	xsl_file="test/phpstan.xsl"

	echo '<h1>PHPStan...</h1>' > "$output_main"

	vendor/bin/phpstan \
		analyze \
		--configuration=test/phpstan.neon \
		--error-format=junit \
		--level=2 \
		--no-ansi \
		--no-progress \
		"${code_list[@]}" \
		> "$output_base/phpstan.xml" \
		|| true

	[ -f "${xsl_file}" ] || curl -qs 'https://openthc.com/pub/phpstan.xsl' > "${xsl_file}"

	xsltproc \
		--nomkdir \
		--output "$output_base/phpstan.html" \
		"$xsl_file" \
		"$output_base/phpstan.xml"

fi

