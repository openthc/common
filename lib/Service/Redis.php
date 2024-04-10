<?php
/**
 * A Basic Redis Class
 */

namespace OpenTHC\Service;

use OpenTHC\Config;

class Redis
{
	protected static $_r;

	private function __construct()
	{
		/* No */
	}

	protected static function _init()
	{
		if (empty($url['host'])) {
			$url['host'] = Config::get('redis/hostname');
		}
		if (empty($url['host'])) {
			$url['host'] = '127.0.0.1';
		}

		if (empty(self::$_r)) {
			self::$_r = new \Redis();
			self::$_r->connect($url['host']);
		}
	}

	public static function factory()
	{
		static::_init();
		return self::$_r;
	}

	public static function del($key)
	{
		static::_init();
		return self::$_r->del($key);
	}

	/**
		Set Expires on a Key
		@param $key Key
		@param $ttl Time to Live in Seconds
	*/
	public static function expire($key, $ttl=240)
	{
		static::_init();
		return self::$_r->expire($key, $ttl);
	}

	public static function find($key)
	{
		static::_init();
		return self::$_r->keys($key);
	}

	public static function get($key)
	{
		static::_init();
		return self::$_r->get($key);
	}
	public static function get_list($key)
	{
		static::_init();
		return self::$_r->lrange($key, 0, -1);
	}

	public static function set($key, $val, $opt=null)
	{
		static::_init();

		if (empty($opt)) {
			return self::$_r->set($key, $val);
		}

		return self::$_r->set($key, $val, $opt);
	}

	/**
		Set a Lock type Key
		@see https://github.com/ronnylt/redlock-php a more robust installation
	*/
	public static function setLock($key, $ttl=3600)
	{
		$opt = array('nx', 'ex' => $ttl);
		return self::set($key, 'lock', $opt);
	}

	/**
		Hash Interactions
		@param $rk Redis Resource Key
		@param $hk Hash Key
		@param $hv Hash Value
	*/
	public static function hset($rk, $hk, $hv=null)
	{
		static::_init();
		if (empty($hv) && is_array($hk)) {
			return self::$_r->hMset($rk, $hk);
		}
		return self::$_r->hset($rk, $hk, $hv);
	}

	public static function hget($rk, $hk=null)
	{
		static::_init();
		if (!empty($hk)) {
			return self::$_r->hGet($rk, $hk);
		}
		return self::$_r->hGetAll($rk);
	}

	public static function lpop($k)
	{
		static::_init();
		return self::$_r->lPop($k);
	}

	public static function rpush($k, $v)
	{
		static::_init();
		return self::$_r->rPush($k, $v);
	}

	static function incr($rk)
	{
		static::_init();
		return self::$_r->incr($rk);
	}

	static function ttl($k, $t=null)
	{
		static::_init();

		if ($t) {
			self::$_r->expire($k, $t);
		}

		return self::$_r->ttl($k);

	}

}
