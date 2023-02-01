<?php
/**
 * Base Option Helper
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Base;

class Option
{
	protected $_dbc; // An Edoceo\Radix\DB\SQL object

	/**
	 *
	 */
	function __construct($dbc=null)
	{
		$this->_dbc = $dbc;
	}

	/**
	 * Set a Base Option
	 */
	function get(string $k)
	{
		$v = $this->_dbc->fetchOne('SELECT val FROM base_option WHERE key = :k', [ ':k' => $k ]);

		if ( ! empty($v)) {
			$v = json_decode($v, true);
		}

		return $v;

	}

	/**
	 * Get a Base Option
	 */
	function set(string $k, $v)
	{
		$sql = 'INSERT INTO base_option (key, val) VALUES (:k, :v) ON CONFLICT (key) DO UPDATE SET val = EXCLUDED.val';

		$arg = [];
		$arg[':k'] = $k;
		$arg[':v'] = json_encode($v);

		return $this->_dbc->query($sql, $arg);

	}

}
