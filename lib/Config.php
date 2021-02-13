<?php
/**
 * Configuration Helper
 * Application and System Level Configuration Variables
 */

namespace OpenTHC;

class Config
{
	private static $_cfg;

	/**
	 * Get Config from '/' separated path
	 * @param $k Key to Get
	 */
	static function get($k)
	{
		// Patch Names
		// $k = str_replace('.', '/', $k);
		// $k = str_replace('_', '/', $k);

		// Per Request Caching
		// Or Maybe No Caching at all?
		if (empty($_ENV['OPENTHC_CONFIG'])) {
			$_ENV['OPENTHC_CONFIG'] = [];
		}
		if (!empty($_ENV['OPENTHC_CONFIG'][$k])) {
			return $_ENV['OPENTHC_CONFIG'][$k];
		}

		self::_load();
		$ret = self::$_cfg;

		// Legacy Way from INI file
		$k_path = explode('/', $k);
		while ($k1 = array_shift($k_path)) {
			$ret = $ret[$k1];
		}

		return $ret;

		// Try Local Shared Memory First
		// $ret = self::shm_get($k);
		// if ($ret) {
		// 	$_ENV['OPENTHC_CONFIG'][$k] = $ret;
		// 	return $ret;
		// }

		// // Local Configuration Path
		// $ret = self::etc_path_get($k);
		// if ($ret) {
		// 	// Promote Caching Layer
		// 	$_ENV['OPENTHC_CONFIG'][$k] = $ret;
		// 	// self::shm_set($k, $ret);
		// 	return $ret;
		// }

	}

	/**
	 * @deprecated
	 * Read Local Config Path
	 */
	static function etc_path_get($k)
	{
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

		return(null);
	}


	static function shm_open()
	{
		static $shm;
		if (empty($shm)) {
			$key = ftok(__FILE__, 'o');
			$shm = shm_attach($key, 16384);
		}
		return($shm);
	}
	/**
	 * Get SHM
	 */
	static function shm_get($key)
	{
		$shm = self::shm_open();
		if ($shm) {
			$key = crc32($key);
			if (shm_has_var($shm, $key)) {
				$ret = shm_get_var($shm, $key);
				return($ret);
			}
		}

		return(null);

	}

	/**
	 * Set SHM
	 */
	static function shm_set($key, $val)
	{
		$shm = self::shm_open();
		if ($shm) {
			return shm_put_var($shm, crc32($key), $val);
		}

		return(false);

	}

	/**
	 * Load Config from INI file
	 */
	private static function _load()
	{
		if (!empty(self::$_cfg)) {
			return(true);
		}

		// Base Path
		$base = '';
		if (defined('APP_ROOT')) {
			$base = APP_ROOT;
		} elseif (!empty($_SERVER['DOCUMENT_ROOT'])) {
			// DOCUMENT_ROOT is the Webroot, Go one UP from there
			$base = dirname($_SERVER['DOCUMENT_ROOT']);
		} elseif (!empty($_SERVER['PWD'])) {
			$base = $_SERVER['PWD'];
		}

		$file = sprintf('%s/etc/app.ini', $base);
		if (!is_file($file)) {
			_exit_text(sprintf('Invalid Configuration "%s" [OLC-159]', $file), 500);
		}

		$cfg_source = parse_ini_file($file, true, INI_SCANNER_RAW);
		$cfg_source = array_change_key_case($cfg_source);

		// $key_list = array_keys($cfg_source);
		// foreach ($key_list as $i => $k) {
		// 	$k = explode('/', $k);
		// }

		// Reduce to Singular Values
		// foreach ($cfg as $k0 => $opt) {
		// 	foreach ($opt as $k1 => $x) {
		// 		if (is_array($cfg[$k0][$k1])) {
		// 			die("Key: $k0 has $k1 as Big");
		// 			$cfg[$k0][$k1] = array_pop($cfg[$k0][$k1]);
		// 		}
		// 	}
		// }

		self::$_cfg = $cfg_source;

		return(true);

	}
}
