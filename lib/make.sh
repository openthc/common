#
# OpenTHC Make Helper Library
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

action="$1"

function install_bootstrap()
{
	source_path="node_modules/bootstrap/dist"
	target_path="webroot/vendor/bootstrap/"
	mkdir -p "$target_path"

	cp "$source_path/js/bootstrap.bundle.min.js"      "$target_path/bootstrap.bundle.min.js"
	cp "$source_path/js/bootstrap.bundle.min.js.map"  "$target_path/bootstrap.bundle.min.js.map"
	cp "$source_path/css/bootstrap.min.css"           "$target_path/bootstrap.min.css"
	cp "$source_path/css/bootstrap.min.css.map"       "$target_path/bootstrap.min.css.map"

}

function install_fontawesome()
{
	source_path="node_modules/@fortawesome/fontawesome-free"
	target_path="webroot/vendor/fontawesome"

	mkdir -p "$target_path/css"
	mkdir -p "$target_path/webfonts"

	cp "$source_path/css/all.min.css"  "$target_path/css/all.min.css"

	# $source_list = glob("$source_path/webfonts/*");
	# foreach ($source_list as $source_file) {
	# 	$source_base = basename($source_file);
	# 	copy($source_file, "$output_path/webfonts/$source_base");
	# }

}


function install_htmx()
{
	source_path="node_modules/htmx.org/dist/"
	target_path="webroot/vendor/htmx/"

	cp "$source_path/htmx.min.js"  "$target_path"
}

function install_jquery()
{
	source_path="node_modules/jquery/dist/"
	target_path="webroot/vendor/jquery/"
	mkdir -p "$target_path"

	# $output_path = sprintf('%s/webroot/vendor/jquery', APP_ROOT);
	# @mkdir($output_path, 0755, true);

	cp "$source_path/jquery.min.js"    "$target_path/jquery.min.js"
	cp "$source_path//jquery.min.map"  "$target_path/jquery.min.map"

	# // SSO, POS, WIKI
	# // If jQuery-UI is installed then copy to webroot
	source_path="node_modules/jquery-ui/dist/"
	if [ -f "$source_path/jquery-ui.min.js" ]
	then
		cp "$source_path/jquery-ui.min.js" $target_path
	fi

	if [ -f "$source_path/themes/base/jquery-ui.min.css" ]
	then
		cp "$source_path/themes/base/jquery-ui.min.css" "$target_path"
	fi

}

function install_lodash()
{
	source_path="node_modules/lodash"
	target_path="webroot/vendor/lodash/"

	mkdir -p "$target_path"

	cp "$source_path/lodash.min.js" "$target_path"

}

function install_zxing()
{
	source="node_modules/@zxing/browser/umd/"
	target="webroot/vendor/zxing/"

	cp "node_modules/@zxing/browser/umd/zxing-browser.min.js" "$target/zxing-browser.min.js"
}

case "$action" in
	install_bootstrap)
		install_bootstrap
		;;
	install_fontawesome)
		install_fontawesome
		;;
	install_htmx)
		install_htmx
		;;
	install_jquery)
		install_jquery
		;;
	install_lodash)
		install_lodash
		;;
esac
