<?php
/**
 * Configuration Helper
 * Application and System Level Configuration Variables
 */

namespace OpenTHC;

class Config
{
	private static $conf = [];

	private static $path;

	function dump()
	{
		return [
			'conf' => self::$conf,
			'path' => self::$path,
		];
	}

	static function init($p=null)
	{
		self::$conf = [];

		if (empty($p)) {
			if (defined('APP_ROOT')) {
				$p = APP_ROOT;
			} elseif (!empty($_SERVER['DOCUMENT_ROOT'])) {
				// assumes our context is in a webroot
				$p = dirname($_SERVER['DOCUMENT_ROOT']);
			}
		}

		self::$path = rtrim($p, '/');

	}

	/**
	 * Get Config from '/' separated path
	 * @param $k Key to Get
	 */
	static function get($k0)
	{
		// Patch Names
		// $k0 = str_replace('.', '/', $k0);
		// $k0 = str_replace('_', '/', $k0);
		$k0 = strtolower($k0);
		$k0 = trim($k0, '/');

		// Per Request Caching
		if (!empty(self::$conf[$k0])) {
			return self::$conf[$k0];
		}

		$k_path = explode('/', $k0);

		// Specific Config File?
		$file = sprintf('%s/etc/%s.ini', self::$path, $k_path[0]);
		if (is_file($file)) {
			array_shift($k_path);
		} else {
			if (!is_file($file)) {
				$file = sprintf('%s/etc/main.ini', self::$path);
			}
			if (!is_file($file)) {
				$file = sprintf('%s/etc/app.ini', self::$path);
			}
		}

		$v = parse_ini_file($file, true, INI_SCANNER_RAW);
		if (empty($v)) {
			return null;
		}

		$v = array_change_key_case($v);

		// Shift Out the Desired Key?
		while ($k1 = array_shift($k_path)) {
			$v = $v[$k1];
		}

		self::$conf[$k0] = $v;

		return $v;

		// Try Local Shared Memory First
		// $ret = self::shm_get($k);
		// if ($ret) {
		// 	self::$conf[$k] = $ret;
		// 	return $ret;
		// }

	}

	/**
	 * Open Shared Memory
	 */
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

}
