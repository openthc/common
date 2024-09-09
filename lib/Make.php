<?php
/**
 * Make Helper
 */

namespace OpenTHC;

class Make {

	/**
	 * Composer Helper
	 */
	static function composer($fast=false) {

		$cmd = [];
		$cmd[] = 'composer';

		// ?composer update --no-ansi --no-dev

		if (empty($fast)) {
			$cmd[] = 'install';
			$cmd[] = '--quiet';
			// $cmd[] = '--no-dev';
			$cmd[] = '--no-progress';
			$cmd[] = '--classmap-authoritative';
			$cmd[] = '2>&1';
		} else {
			$cmd[] = 'dump-autoload';
			$cmd[] = '--classmap-authoritative';
			$cmd[] = '2>&1';
		}

		$ret = null;

		echo "Composer:\n";
		passthru(implode(' ', $cmd), $ret);
		var_dump($ret);

	}

	/**
	 *
	 */
	static function npm() {

		$cmd = [];
		$cmd[] = 'npm';
		$cmd[] = 'install';
		$cmd[] = '--quiet';
		$cmd[] = '2>&1';

		$ret = null;

		echo "NPM:\n";
		passthru(implode(' ', $cmd), $ret);
		var_dump($ret);


	}

	/**
	 * Install Bootstrap
	 */
	static function install_bootstrap() {

		$output_path = sprintf('%s/webroot/vendor/bootstrap', APP_ROOT);
		@mkdir($output_path, 0755, true);

		$source_path = sprintf('%s/node_modules/bootstrap/dist', APP_ROOT);

		copy("$source_path/js/bootstrap.bundle.min.js",     "$output_path/bootstrap.bundle.min.js");
		copy("$source_path/js/bootstrap.bundle.min.js.map", "$output_path/bootstrap.bundle.min.js.map");
		copy("$source_path/css/bootstrap.min.css",          "$output_path/bootstrap.min.css");
		copy("$source_path/css/bootstrap.min.css.map",      "$output_path/bootstrap.min.css.map");

	}

	/**
	 * Install FontAwesome
	 */
	static function install_fontawesome() {

		$output_path = sprintf('%s/webroot/vendor/fontawesome', APP_ROOT);
		$source_path = sprintf('%s/node_modules/@fortawesome/fontawesome-free', APP_ROOT);

		@mkdir("$output_path/css", 0755, true);
		@mkdir("$output_path/webfonts", 0755, true);

		copy("$source_path/css/all.min.css", "$output_path/css/all.min.css");
		$source_list = glob("$source_path/webfonts/*");
		foreach ($source_list as $source_file) {
			$source_base = basename($source_file);
			copy($source_file, "$output_path/webfonts/$source_base");
		}

	}

	/**
	 * Install jQuery
	 */
	static function install_jquery() {

		$output_path = sprintf('%s/webroot/vendor/jquery', APP_ROOT);
		@mkdir($output_path, 0755, true);

		$source_path = sprintf('%s/node_modules', APP_ROOT);

		copy("$source_path/jquery/dist/jquery.min.js", "$output_path/jquery.min.js");
		copy("$source_path/jquery/dist/jquery.min.map", "$output_path/jquery.min.map");

		// SSO, POS, WIKI
		// If jQuery-UI is installed then copy to webroot
		$source_file = "$source_path/jquery-ui/dist/jquery-ui.min.js";
		if (is_file($source_file)) {
			copy($source_file, "$output_path/jquery-ui.min.js");
		}

		$source_file = "$source_path/jquery-ui/dist/themes/base/jquery-ui.min.css";
		if (is_file($source_file)) {
			copy($source_file, "$output_path/jquery-ui.min.css");
		}

	}

	/**
	 *
	 */
	function create_homepage(string $svc)
	{
		$key = sprintf('openthc/%s/origin', $svc);
		$cfg = \OpenTHC\Config::get($key);
		$url = sprintf('%s/home', $cfg);
		$req = _curl_init($url);
		$res = curl_exec($req);
		$inf = curl_getinfo($req);
		if (200 == $inf['http_code']) {
			$file = sprintf('%s/webroot/index.html', APP_ROOT);
			$data = $res;
			$ret = file_put_contents($file, $data);
			return $ret;
		}

	}
}
