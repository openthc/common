<?php
/**
 * Simple Redis Based Cache
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC;

class Cache
{
	private $pre;

	private $rdb;

	private $ttl = 420;

	/**
	 *
	 */
	function __construct(string $pre)
	{
		$this->pre = sprintf('/%s', trim($pre ,'/'));

		$this->rdb = new \Redis();
		$this->rdb->connect(\OpenTHC\Config::get('redis/hostname'));

	}

	/**
	 *
	 */
	function get($key)
	{
		$key = sprintf('/%s/%s', $this->pre, trim($key, '/'));
		$ret = $this->rdb->get($key);
		return json_decode($ret);
	}

	/**
	 *
	 */
	function set($key, $val)
	{
		$key = sprintf('/%s/%s', $this->pre, trim($key, '/'));
		$ret = $this->rdb->set($key, json_encode($val), [ 'ex' => $this->ttl ]);
		return $ret;
	}

}
