#!/bin/bash
#
#
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

function _have_app_root
{
	if [ -z "$APP_ROOT" ]
	then
		echo "\$APP_ROOT must be defined"
	        return 1
	fi

	return 0
}




function copy_bootstrap()
{
	_have_app_root || return

	cd "$APP_ROOT"

	mkdir -p "webroot/vendor/bootstrap"

	cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js        webroot/vendor/bootstrap/
	cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map    webroot/vendor/bootstrap/
	cp node_modules/bootstrap/dist/css/bootstrap.min.css             webroot/vendor/bootstrap/
	cp node_modules/bootstrap/dist/css/bootstrap.min.css.map         webroot/vendor/bootstrap/

	cd -

}

function copy_fontawesome()
{
	_have_app_root || return

	cd "$APP_ROOT"

	mkdir -p \
		"webroot/vendor/fontawesome/css" \
		"webroot/vendor/fontawesome/webfonts"

	cp node_modules/@fortawesome/fontawesome-free/css/all.min.css \
		webroot/vendor/fontawesome/css/

	cp node_modules/@fortawesome/fontawesome-free/webfonts/* \
		webroot/vendor/fontawesome/webfonts/

	cd -

}


function copy_jquery()
{
	_have_app_root || return

	cd "$APP_ROOT"

	mkdir -p "webroot/vendor/jquery"

	cp node_modules/jquery/dist/jquery.min.js \
		webroot/vendor/jquery/

	cd -
}

