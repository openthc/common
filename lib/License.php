<?php
/**
 * A License, part of a Company
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

use Edoceo\Radix\DB\SQL;

class License extends \OpenTHC\SQL\Record
{
	use OpenTHC\Traits\StatIcon;

	const FLAG_LIVE    = 0x00000001;
	const FLAG_GROWER  = 0x00000002;
	const FLAG_PROCESS = 0x00000004;
	const FLAG_RETAIL  = 0x00000008;

	const FLAG_MEDICAL = 0x00000010;

	const FLAG_SNAIL_GOOD = 0x00000400;
	const FLAG_SNAIL_SHOW = 0x00000800;
	const FLAG_EMAIL_GOOD = 0x00001000;
	const FLAG_EMAIL_SHOW = 0x00002000;
	const FLAG_PHONE_GOOD = 0x00004000;
	const FLAG_PHONE_SHOW = 0x00008000;

	const FLAG_MINE    = 0x01000000;

	const FLAG_SYNC    = 0x01000000;

	const FLAG_DEAD    = 0x08000000;
	const FLAG_DELETED = 0x08000000; // @deprecated

	const FLAG_TEST    = 0x10000000;

	const STAT_PROC = 102;

	const STAT_LIVE = 200;

	const STAT_MUTE = 204;

	const STAT_HOLD = 307;
	const STAT_MOVE = 308;

	const STAT_GONE = 410;

	const STAT_SUSPENDED = 451;

	protected $_table = 'license';

	static function findByGUID($x)
	{
		syslog(LOG_NOTICE, '@deprecated License::findByGUID');

		$sql = 'SELECT license.*';
		$sql.= ' FROM company';
		$sql.= ' JOIN license ON company.id = license.company_id';
		$sql.= ' WHERE license.guid = ?';
		$arg = array($x);
		$res = SQL::fetch_row($sql, $arg);
		if (!empty($res)) {
			$r = new License($res);
			return $r;
		}
	}

	function addType($t)
	{
		//$x = License_Type::map($t);

		$mix = array();

		// Current Types
		$tmp = explode(',', $this->_data['type']);
		$mix = array_merge($mix, $tmp);

		// New Types Being Added
		$tmp = explode(',', $t);
		$mix = array_merge($mix, $tmp);

		$mix = array_filter($mix);

		$ret = [];
		foreach ($mix as $x) {

			$y = License_Type::map($x);

			if (empty($y)) {
				throw new \Exception("Cannot Map: $x");
			}
			$ret[] = $y;
		}

		$ret = array_unique($ret);
		sort($ret);

		$ret = implode(',', $ret);
		$this->offsetSet('type', $ret);

		return true;
	}

	/**
	 * Return Icon(s) based on status
	 */
	function getIcon() : string
	{
		$icon = $this->getStatIcon([]);
		// More icons here?
		return implode(' ', $icon);
	}

}
