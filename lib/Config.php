<?php
/**
 * Configuration Helper
 * Application and System Level Configuration Variables
 */

namespace OpenTHC;

class Config
{
	private static $conf = [];
	private static $data = [];
	private static $path;

	function dump()
	{
		return [
			'conf' => self::$conf,
			'data' => self::$data,
			'path' => self::$path,
		];
	}

	/**
	 *
	 */
	static function init($p=null)
	{
		self::$conf = [];
		self::$data = [];

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

		$key_list = explode('/', $k0);

		if (empty(self::$data)) {
			$cfg_file = sprintf('%s/etc/config.php', self::$path);
			if (is_file($cfg_file)) {
				$x = include($cfg_file);
				if (is_array($x)) {
					self::$data = $x;
				}
			}
		}
		if (!empty(self::$data)) {
			$ret = self::$data;
			while ($key = array_shift($key_list)) {
				$ret = $ret[$key];
			}
			return $ret;
		}


	}

}
