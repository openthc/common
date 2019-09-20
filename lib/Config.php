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
