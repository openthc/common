<?php
/**
 * Application Data Model
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SQL;

class Record implements \ArrayAccess, \JsonSerializable
{
	const FLAG_HAS_NOTE  = 0x00100000;

	const FLAG_CRE_SYNC_WANT = 0x00200000; // Want to Sync w/CRE
	const FLAG_CRE_SYNC_GOOD = 0x00400000; // In SYNC
	const FLAG_CRE_SYNC_FAIL = 0x00800000; // v1
	const FLAG_CRE_SYNC_LOST = 0x00800000; // v0 alias

	const STAT_INIT = 100;
	const STAT_PROC = 102;

	const STAT_LIVE = 200;
	// const STAT_XXXX = 202; // Accepted
	const STAT_DUPE = 208;

	const STAT_VOID = 410;

	const STAT_DEAD = 666;


	protected $_dbc; // An Edoceo\Radix\DB\SQL object

	protected $_pk = null;
	protected $_table;
	protected $_sequence;

	protected $_data; // Object Data
	protected $_diff = [];  // Array of Changed Properties

	/**
	 * Record Constructor
	 */
	function __construct($dbc=null, $obj=null)
	{
		// Detect Sequence Name
		if (strlen($this->_sequence) == 0) {
			$this->_sequence = $this->_table . '_id_seq';
		}

		// First Parameter is DBC?
		if (!empty($dbc) && is_object($dbc) && ($dbc instanceof \Edoceo\Radix\DB\SQL)) {
			$this->_dbc = $dbc;
			$dbc = null;
		}

		// If Single Parameter
		// Promote first parameter to second parameter
		// Since the caller is not specifying the DB Connection to use
		if (!empty($dbc) && empty($obj)) {
			$obj = $dbc;
			$dbc = null;
		}

		// Detect Object Properties from Table if not Specified
		//		if (!isset($this->_properties)) {
		//			$this->_properties = array();
		//			$d = $this->_db->describeTable($this->_table);
		//			foreach ($d as $k=>$v) {
		//				$this->_properties[] = $k;
		//				if (!isset($this->$k)) {
		//					$this->$k = null;
		//				}
		//			}
		//		}

		// Do Nothing
		if (empty($obj)) {
			return;
		}

		// Load Database Record
		if (is_string($obj) || is_numeric($obj)) {

			$sql = sprintf('SELECT * FROM %s where id = ?', $this->_table);
			// Class or Static?
			if ( ! empty($this->_dbc)) {
				$obj = $this->_dbc->fetchRow($sql, array($obj));
			} else {
				$obj = \Edoceo\Radix\DB\SQL::fetch_row($sql, array($obj));
			}

		}

		$this->setData($obj);

	}

	/**
	 *
	 */
	// function __get($k)
	// {
	// 	if (isset($this->_data[$k])) {
	// 		return $this->_data[$k];
	// 	}

	// 	throw new \Exception('Oh Shit');
	// }

	/**
	 *
	 */
	// function __set($k, $v)
	// {
	// 	if ( ! isset($this->_data[$k])) {
	// 		throw new \Exceptin('Invoid Pproperty');
	// 	}

	// 	$this->offsetSet($k, $v);
	// }

	/**
	 * Add a Note to this Object
	 * @param string $note [description]
	 * @param bool pin to top
	 */
	function addNote($note, $pin=0)
	{
		$link = sprintf('%s:%s', $this->_table, $this->_data['id']);
		$pin = intval($pin);

		// SQL::query('BEGIN');
		$this->_dbc->query('BEGIN');

		if (!empty($pin)) {

			$pin = 1;

			// Update all other Notes on this Object to be !PIN
			$sql = 'UPDATE object_note SET flag = (flag & ~ :f0::int) WHERE link = :l0';
			$arg = array(
				':l0' => $link,
				':f0' => $pin,
			);
			// SQL::query($sql, $arg);
			$this->_dbc->query($sql, $arg);

		}

		// Add Note
		$sql = 'INSERT INTO object_note (auth_contact_id, flag, link, note) VALUES (?, ?, ?, ?)';
		$arg = array($_SESSION['Contact']['id'], $pin, $link, $note);
		// $res = SQL::query($sql, $arg);
		$res = $this->_dbc->query($sql, $arg);

		// Tag Object
		$sql = sprintf('UPDATE %s SET flag = flag | :f1 WHERE id = :id', $this->_table);
		$arg = array(':f1' => self::FLAG_HAS_NOTE, ':id' => $this->_data['id']);
		// SQL::query($sql, $arg);
		$this->_dbc->query($sql, $arg);

		// SQL::query('COMMIT');
		$this->_dbc->query('COMMIT');

		$this->_data['flag'] = ($this->_data['flag'] | self::FLAG_HAS_NOTE);

		return $res;

	}

	/**
	 * Load from Database to this Object Instance
	 */
	function loadBy($key, $val=null) : bool
	{
		$arg = [];
		$sql = '';

		if (is_array($key)) {

			$col_list = $key;

			$sql = sprintf('SELECT * FROM %s WHERE {WHERE}', $this->_table);
			$tmp = [];
			$idx = __LINE__;
			foreach ($col_list as $col => $val) {
				$idx++;
				$key = sprintf(':v%d', $idx);
				$tmp[] = sprintf('"%s" = %s', $col, $key);
				$arg[$key] = $val;
			}
			$tmp = implode(' AND ', $tmp);
			$sql = str_replace('{WHERE}', $tmp, $sql);

		} else {
			$sql = sprintf('SELECT * FROM %s WHERE "%s" = :v0', $this->_table, $key);
			$arg = [ ':v0' => $val ];
		}

		$rec = $this->_dbc->fetchRow($sql, $arg);
		$this->setData($rec);
		if ( ! empty($this->_data['id'])) {
			return true;
		}

		return false;

	}

	/**
		AppModel delete
		Destroy this object and it's index
	*/
	function delete()
	{
		$ret = null;

		$sql = sprintf('DELETE FROM %s WHERE id = :pk', $this->_table);
		$arg = [ ':pk' => $this->_data['id'] ];

		if ( ! empty($this->_dbc)) {
			$ret = $this->_dbc->query($sql, $arg);
		} else {
			$ret = \Edoceo\Radix\DB\SQL::query($sql, $arg);
		}

		return $ret;
	}

	/**
		AppModel Save
		@todo use the _data interface, check for dirty
	*/
	function save()
	{
		// Set Sane Defaults
		// if (empty($this->_data['hash'])) $this->_data['hash'] = $this->hash();

		if (empty($this->_data['id'])) {
			unset($this->_data['id']);
		}

		if (!empty($this->_data['json'])) { // @deprecated
			if (is_array($this->_data['json'])) {
				$this->_data['json'] = json_encode($this->_data['json']);
			}
		}

		if ($this->_pk) {

			// Record Delta?
			$rec = [];
			foreach ($this->_diff as $k => $v) {
				$rec[$k] = $this->_data[$k];
			}

			if ( ! empty($rec)) {
				if ( ! empty($this->_dbc)) {
					$res = $this->_dbc->update($this->_table, $rec, array('id' => $this->_pk));
				} else {
					$res = \Edoceo\Radix\DB\SQL::update($this->_table, $rec, array('id' => $this->_pk));
				}
			}

		} else {

			$rec = [];
			foreach ($this->_data as $k=>$v) {
				$rec[$k] = $v;
			}

			if ( ! empty($this->_dbc)) {
				$this->_pk = $this->_dbc->insert($this->_table, $rec);
			} else {
				$this->_pk = \Edoceo\Radix\DB\SQL::insert($this->_table, $rec);
			}

			$this->_data['id'] = $this->_pk;

		}

		return true;
	}

	/**
	 *
	 */
	function getDiff()
	{
		if (empty($this->_diff)) return null;
		if (!is_array($this->_diff)) return null;
		if (count($this->_diff) == 0) return null;

		$diff = $this->_diff;
		ksort($diff);

		return $diff;
	}

	/**
	 * @deprecated?
	 */
	function getHash()
	{
		$data = $this->_data;
		 _ksort_r($data);
		$hash = md5(json_encode($data));
		return $hash;
	}

	/**
	 *
	 */
	function getMeta()
	{
		$x = $this->_data['meta'];
		if (!is_array($x)) {
			return json_decode($x, true);
		}
		return $x;

	}

	/**
	 * Set Data on this Object
	 */
	protected function setData($rec)
	{
		$this->_data = [];
		$this->_diff = [];

		if (is_object($rec)) {
			$p = get_object_vars($rec);
			foreach ($p as $k=>$v) {
				$this->_data[$k] = $rec->$k;
			}
		} elseif (is_array($rec)) {
			$this->_data = $rec;
		}

		if (!empty($this->_data['id'])) {
			$this->_pk = $this->_data['id'];
		}

	}

	/**
		Flag Handling
	*/
	function delFlag($f)
	{
		$this->offsetSet('flag', intval($this->_data['flag']) & ~$f);
	}
	function hasFlag($f)
	{
		return (intval($this->_data['flag']) & $f);
	}

	function getFlag($fmt='d')
	{
		switch($fmt) {
		case 'b': // Binary
			return sprintf('0b%032s',decbin($this->_data['flag']));
		case 'd': // Decimal
			return sprintf('%u',$this->_data['flag']);
		case 's': // String
			$rc = new \ReflectionClass($this);
			$set = $rc->getConstants();
			$ret = array();
			foreach ($set as $k=>$v) {
			  if ((preg_match('/^FLAG_/',$k)) && ($this->hasFlag($v))) {
				$ret[] = $k;
			  }
			}
			return implode(', ',$ret);
		case 'x': // Hex
			return sprintf('0x%08x',$this->_data['flag']);
		}
	}
	function setFlag($f)
	{
		$this->offsetSet('flag', intval($this->_data['flag']) | $f);
	}

	/**
	 *
	 */
	function delJtag(string $key)
	{
		$jt0 = json_decode($this->_data['jtag'], true);
		unset($jt0[$key]);
		return $this->offsetSet('jtag', json_encode($jt0));
	}

	/**
	 *
	 */
	function getJtag(string $key)
	{
		$jt0 = json_decode($this->_data['jtag'], true);
		return $jt0[$key];
	}

	/**
	 *
	 */
	function setJtag(string $key, $val)
	{
		$jt0 = json_decode($this->_data['jtag'], true);
		$jt0[$key] = $val;
		return $this->offsetSet('jtag', json_encode($jt0));
	}

	/**
	 * Array Accessors
	 */
	public function toArray()
	{
		$ret = $this->_data;

		if ( ! empty($ret['meta'])) {
			$ret['meta'] = json_decode($ret['meta'], true);
		}

		return $ret;
	}

	/**
		@return Boolean
	*/
	public function offsetExists($k) { return isset($this->_data[$k]); }

	/**
		@return Data
	*/
	public function offsetGet($k) { return $this->_data[$k]; }

	/**
		@return void
	*/
	public function offsetSet($k, $v)
	{
		// If Different than current value
		//$old = !empty($this->_data[$k]) ? $this->_data[$k] : null;
		//if ($old) {
		if ($v != $this->_data[$k]) {
			// Track this Change
			if (empty($this->_diff[$k])) {
				$this->_diff[$k] = array(
					'old' => $this->_data[$k],
					'new' => $v,
				);
			} else {
				$this->_diff[$k]['new'] = $v;
			}
		}

		$this->_data[$k] = $v;
	}

	/**
		@return void
	*/
	public function offsetUnset($k) { unset($this->_data[$k]); }

	function jsonSerialize()
	{
		return $this->toArray();
	}

}
