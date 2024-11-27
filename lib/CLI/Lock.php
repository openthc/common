<?php
/**
 * Creates a Semaphore Lock
 * The lock will disappear when this object loses scope or is unset
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\CLI;

class Lock
{
	protected $ack = false;

	protected $key;

	protected $sem;

	protected $tag;

	function __construct($tag=null)
	{
		if (empty($tag)) {
			$tag = $_SERVER['SCRIPT_FILENAME'];
			$tag = realpath($tag);
		}
		$this->tag = $tag;
		$this->key = crc32($this->tag);
		$this->sem = sem_get($this->key, $max_acquire=1, 0666, true);
	}

	function create() : bool
	{
		$ret = sem_acquire($this->sem, true);

		// Set ACK on First Acquire
		if (!empty($ret)) {
			$this->ack = $ret;
		}

		return $ret;
	}

	function delete()
	{
		$ret = false;

		if ($this->ack) {
			$ret = sem_release($this->sem);
			$this->ack = false;
		}

		return $ret;

	}

}
