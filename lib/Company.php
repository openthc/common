<?php
/**
 * An OpenTHC Company
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Company extends \OpenTHC\SQL\Record
{
	use \OpenTHC\Traits\StatIcon;

	const FLAG_LIVE       = 0x00000001;
	const FLAG_VERIFIED   = 0x00000002;

	const FLAG_EMAIL_GOOD = 0x00001000;
	const FLAG_EMAIL_SHOW = 0x00002000;
	const FLAG_PHONE_GOOD = 0x00004000;
	const FLAG_PHONE_SHOW = 0x00008000;

	const FLAG_DEAD       = 0x08000000;
	const FLAG_DELETED    = 0x08000000;

	const FLAG_TEST       = 0x10000000;
	const FLAG_MUTE       = 0x20000000;

	const STAT_PROC = 102;
	const STAT_LIVE = 200;
	const STAT_DEAD = 666;

	protected $_table = 'company';

	static function findByGUID($x)
	{
		syslog(LOG_NOTICE, '@deprecated Company::findByGUID');

		$sql = 'SELECT * FROM company WHERE guid = ?';
		$arg = array($x);
		$res = SQL::fetch_row($sql, $arg);
		if (!empty($res)) {
			$x = new Company($res);
			return $x;
		}
	}

	/**
	 * Return Icon(s) based on status
	 */
	function getIcon() : string
	{
		$icon = [];
		$icon = $this->getStatIcon($icon);
		// More icons here?
		return implode(' ', $icon);

	}

	/**
	 * Get a Path, for the Company
	 */
	function getPath($p0)
	{
		$p0 = trim($p0, './');

		$p2 = sprintf('%s/var/%s/%s', APP_ROOT, $this->_data['id'], $p0);
		if ( ! is_dir($p2)) {
			$x = mkdir($p2, 0755, true);
		}

		return $p2;

	}

	/**
	 * Get or Set Options with Caching
	 */
	function opt($k, $v=null)
	{
		if ($v !== null) {
			$this->setOption($k, $v);
			return $v;
		}

		if (empty($r)) {
			$r = $this->getOption($k);
		}

		return $r;
	}


	/**
	 * Delete Option, No Cache
	 */
	function delOption($key)
	{
		$sql = 'DELETE FROM base_option WHERE key = ?';
		$arg = array($key);
		$res = $this->_dbc->query($sql, $arg);
		return $res;
	}


	/**
	 * Get Option, No Cache
	 */
	function getOption($key)
	{
		$sql = 'SELECT val FROM base_option WHERE key = ?';
		$arg = array($key);
		$res = $this->_dbc->fetchOne($sql, $arg);
		if ( ! empty($res)) {
			$res = json_decode($res, true);
		}
		return $res;
	}


	/**
	 * Set Option, No Cache
	 */
	function setOption($key, $val=null)
	{
		if (empty($key)) {
			throw new \Exception('Invalid Key [OLC-104]');
		}

		$sql = 'INSERT INTO base_option (key, val) VALUES (:k, :v) ON CONFLICT (key) DO UPDATE SET val = EXCLUDED.val';

		$arg = [];
		$arg[':k'] = $key;
		$arg[':v'] = json_encode($val);

		$this->_dbc->query($sql, $arg);

	}

	/**
	*/
	function findContact($x=null)
	{
		$sql = 'SELECT * FROM auth_contact WHERE company_id = ? ORDER BY id ASC';
		$arg = array($this->_data['id']);
		$res = $this->_dbc->fetchAll($sql, $arg);
		switch (count($res)) {
		case 0:
			// Fail
			return null;
			break;
		case 1:
			return new Contact($this->_dbc, $res[0]);
			break;
		default:
			return new Contact($this->_dbc, $res[0]);
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
