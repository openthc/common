<?php
/**
 * Variety
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Variety extends \OpenTHC\SQL\Record
{
	const TABLE = 'variety';

	const FLAG_MUTE       = 0x04000000;

	protected $_table = 'variety';

	static function countActive()
	{
		$sql = 'SELECT count(id)';
		$sql.= ' FROM variety';
		$sql.= ' WHERE variety.license_id = :l AND variety.flag = 0';
		$arg = array(':l' => $_SESSION['License']['id']);
		$res = SQL::fetch_one($sql, $arg);
		return $res;
	}

	public static function findAll()
	{
		$sql = 'SELECT variety.*';
		$sql.= ' , license.guid AS license_guid';
		$sql.= ' , license.name AS license_name';
		$sql.= ' FROM variety';
		$sql.= ' JOIN license ON variety.license_id = license.id';
		$sql.= ' ORDER BY variety.name';
		//$sql.= ' WHERE variety.name = ?';
		$arg = array();
		$res = SQL::fetch_all($sql, $arg);
		return $res;
	}

	public static function findByGUID($x)
	{
		$sql = 'SELECT * FROM variety WHERE guid = ?';
		$arg = array($x);
		$res = SQL::fetch_row($sql, $arg);
		if (!empty($res)) {
			$r = new Variety($res);
			return $r;
		}
	}

	public static function findByName($x, $l=null)
	{
		$sql = 'SELECT * FROM variety WHERE name = ?';
		$arg = array($x);

		if (!empty($l)) {
			$sql.= ' AND license_id = ?';
			$arg[] = $l;
		}

		$sql.= ' ORDER BY id ASC';

		$res = SQL::fetch_row($sql, $arg);
		if (!empty($res)) {
			$r = new Variety($res);
			return $r;
		}
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
		$media_path = Company::getPath('/variety');

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
