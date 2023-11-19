<?php
/**
 * Section
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Section extends \OpenTHC\SQL\Record
{
	const TABLE = 'section';

	const FLAG_MUTE       = 0x04000000;

	protected $_table = 'section';

	static function countActive()
	{
		$sql = 'SELECT count(id)';
		$sql.= sprintf(' FROM %s', self::TABLE);
		$sql.= sprintf(' WHERE %s.license_id = :l AND %s.stat = 200', self::TABLE, self::TABLE);
		$arg = array(':l' => $_SESSION['License']['id']);
		$res = SQL::fetch_one($sql, $arg);
		return $res;
	}

	/**
	 *
	 */
	function save($note = null)
	{
		$this->_data['name'] = trim($this->_data['name']);

		return parent::save($note);
	}

	/**
	 * Add the File to this thing
	 */
	function addMedia($x) : bool
	{
		$media_path = Company::getPath('/section');
	}

	/**
	 *
	 */
	function getMediaList() : array
	{
		// $img_list

		return [];
	}
}
