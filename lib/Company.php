<?php
/**
 * An OpenTHC Company
 */

namespace OpenTHC;

use Edoceo\Radix\DB\SQL;

class Company extends \OpenTHC\SQL\Record
{
	const TABLE = 'company';

	const FLAG_LIVE       = 0x00000001;
	const FLAG_PARENT     = 0x00000002;

	const FLAG_EMAIL_GOOD = 0x00001000;
	const FLAG_EMAIL_SHOW = 0x00002000;
	const FLAG_PHONE_GOOD = 0x00004000;
	const FLAG_PHONE_SHOW = 0x00008000;

	const FLAG_DEAD       = 0x08000000;
	const FLAG_DELETED    = 0x08000000;

	const FLAG_TEST       = 0x10000000;
	const FLAG_MUTE       = 0x20000000;

	protected $_table = 'company';

	static function findByGUID($x)
	{
		$sql = 'SELECT * FROM company WHERE guid = ?';
		$arg = array($x);
		$res = SQL::fetch_row($sql, $arg);
		if (!empty($res)) {
			$x = new Company($res);
			return $x;
		}
	}


	/**
		Get or Set Options with Caching
	*/
	static function opt($k, $v=null)
	{
		if ($v !== null) {
			self::setOption($k, $v);
			//$_SESSION['company'][$k] = $v;
			return $v;
		}

		// Use or Load Cache
		//$r = $_SESSION['company'][$k];

		if (empty($r)) {
			$r = self::getOption($k);
			//$_SESSION['company'][$k] = $r;
		}

		return $r;
	}


	/**
		Delete Option, No Cache
	*/
	static function delOption($key)
	{
		$key = strtolower(trim($key));
		$sql = 'DELETE FROM company_option WHERE company_id = ? AND key = ?';
		$arg = array($_SESSION['gid'], $key);
		$res = SQL::fetch_one($sql, $arg);
		return $res;
	}


	/**
		Get Option, No Cache
	*/
	static function getOption($key)
	{
		$key = strtolower(trim($key));
		$sql = 'SELECT val FROM company_option WHERE company_id = ? AND key = ?';
		$arg = array($_SESSION['gid'], $key);
		$res = SQL::fetch_one($sql, $arg);
		if (!empty($res)) {
			$res = json_decode($res, true);
		}
		return $res;
	}


	/**
		Set Option, No Cache
	*/
	static function setOption($key, $val=null)
	{
		$key = strtolower(trim($key));

		if (empty($key)) {
			throw new \Exception('Invalid Key [OLC#104]');
		}

		SQL::query('BEGIN');

		$sql = 'SELECT key FROM company_option WHERE company_id = ? AND key = ? FOR UPDATE';
		$arg = array($_SESSION['gid'], $key);
		$chk = SQL::fetch_one($sql, $arg);

		if (empty($chk)) {
			$sql = 'INSERT INTO company_option (company_id, key, val) VALUES (:c, :k, :v)';
		} else {
			$sql = 'UPDATE company_option SET val = :v WHERE company_id = :c AND key = :k';
		}

		$arg = array(
			':c' => $_SESSION['gid'],
			':k' => $key,
			':v' => json_encode($val)
		);

		SQL::query($sql, $arg);

		SQL::query('COMMIT');

		// Invalidate Cache
		// unset($_SESSION['company'][$key]);

	}


	/**
	*/
	static function getAllRooms($kind=null)
	{
		// Show Users
		$sql = 'SELECT room.*';
		// $sql.= ', asset_tag.id AS asset_id, asset_tag.govt AS asset_govt';
		$sql.= ' FROM room';
		// $sql.= ' JOIN asset_tag ON room.asset_id = asset_tag.id';
		$sql.= ' WHERE room.gid = ?';
		$sql.= ' ORDER BY room.kind, room.name';
		$arg = array($_SESSION['gid']);
		$res = SQL::fetch_all($sql, $arg);
		return $res;
	}


	/**
	*/
	function findContact($x=null)
	{
		$sql = 'SELECT * FROM auth_contact WHERE company_id = ? ORDER BY id ASC';
		$arg = array($this->_data['id']);
		$res = SQL::fetch_all($sql, $arg);
		switch (count($res)) {
		case 0:
			// Fail
			return null;
			break;
		case 1:
			return new Contact($res[0]);
			break;
		default:
			return new Contact($res[0]);
			foreach ($res as $rec) {
				// @todo Somehow Match the X?
			}
		}
	}


	/**
	*/
	function save()
	{
		$this->_data['flag'] = intval($this->_data['flag']);

		if (empty($this->_data['address_meta'])) {
			unset($this->_data['address_meta']);
		} elseif (is_array($this->_data['address_meta'])) {
			$this->_data['address_meta'] = json_encode($this->_data['address_meta']);
		}

		if (empty($this->_data['contact_meta'])) {
			unset($this->_data['contact_meta']);
		} elseif (is_array($this->_data['contact_meta'])) {
			$this->_data['contact_meta'] = json_encode($this->_data['contact_meta']);
		}

		if (empty($this->_data['profile_meta'])) {
			unset($this->_data['profile_meta']);
		} elseif (is_array($this->_data['profile_meta'])) {
			$this->_data['profile_meta'] = json_encode($this->_data['profile_meta']);
		}

		return parent::save();
	}

}
