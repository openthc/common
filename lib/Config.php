<?php
/**
	Configuration Helper
*/

namespace OpenTHC;

class Config
{
	private static $_cfg;

	/**
		Get Config from '.' separated path
		@param $k Key to Get
	*/
	static function get($k)
	{
		if (!empty($_ENV[$k])) {
			return $_ENV[$k];
		}

		// New Path Based
		$k_want = str_replace('.', '/', $k);
		$k_want = trim($k_want, '/');
		$k_path = sprintf('%s/etc/%s', APP_ROOT, $k_want);
		if (is_file($k_path)) {
			$ret = file_get_contents($k_path);
			return trim($ret);
		}

		// Asking for a Directory returns all it's files
		if (is_dir($k_path)) {
			$ret = [];
			$k_want_list = glob(sprintf('%s/*', $k_path));
			foreach ($k_want_list as $k_file) {
				if (is_file($k_file)) {
					$key = basename($k_file);
					$val = file_get_contents($k_file);
					$ret[$key] = trim($val);
				}
			}
			if (count($ret)) {
				return $ret;
			}
		}

		// Legacy Way from INI file
		self::_load();

		$k_path = explode('.', $k);

		$r = self::$_cfg;
		while ($k = array_shift($k_path)) {
			$r = $r[$k];
		}

		return $r;

	}

	/**
		Load Config from INI file
	*/
	private static function _load()
	{
		if (!empty(self::$_cfg)) {
			return(0);
		}

		// App Defaults
		$file = sprintf('%s/etc/app.ini', APP_ROOT);
		if (!is_file($file)) {
			return(null);
			//die('Application Config Missing');
		}

		$cfg = parse_ini_file($file, true, INI_SCANNER_RAW);
		$cfg = array_change_key_case($cfg);

		// Reduce to Singular Values
		foreach ($cfg as $k0=>$opt) {
			foreach ($opt as $k1=>$x) {
				if (is_array($cfg[$k0][$k1])) {
					die("Key: $k0 has $k1 as Big");
					$cfg[$k0][$k1] = array_pop($cfg[$k0][$k1]);
				}
			}
		}

		self::$_cfg = $cfg;

	}

}
