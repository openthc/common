<?php
/**
 * Application Data Model
 */

namespace OpenTHC\SQL;

use Edoceo\Radix\DB\SQL;

class Record implements \ArrayAccess
{
	protected $_pk = null;
	protected $_data; // Object Data
	protected $_diff = array();  // Array of Changed Properties
	protected $_table;
	protected $_sequence;

	/**
		ImperiumBase Model Constructor
	*/
	function __construct($x=null)
	{
		// Detect Sequence Name
		if (strlen($this->_sequence) == 0) {
			$this->_sequence = $this->_table . '_id_seq';
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
		if (empty($x)) {
			return;
		}

		// Load Database Record
		if (is_string($x) || is_numeric($x)) {
			$sql = sprintf("SELECT * FROM \"%s\" where id = ?", $this->_table);
			$x = SQL::fetch_row($sql, array($x));
		}

		// Copy properties from Given object to me!
		if (is_object($x)) {
			$p = get_object_vars($x);
			foreach ($p as $k=>$v) {
				$this->_data[$k] = $x->$k;
			}
		} elseif (is_array($x)) {
			$this->_data = $x;
		}

		if (!empty($this->_data['id'])) {
			$this->_pk = $this->_data['id'];
		}

	}

	/**
		AppModel delete
		Destroy this object and it's index
	*/
	function delete()
	{
		SQL::query("DELETE FROM \"{$this->_table}\" WHERE id = ?", array($this->_data['id']));
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
		if (!empty($this->_data['json'])) {
			if (is_array($this->_data['json'])) {
				$this->_data['json'] = json_encode($this->_data['json']);
			}
		}

		$rec = array();
		foreach ($this->_data as $k=>$v) {
			$rec[$k] = $v;
		}

		if ($this->_pk) {
			// Record Delta?
			if (count($this->_diff)) {
				//Base_Diff::diff($this);
			}
			$res = SQL::update($this->_table, $rec, array('id' => $this->_pk));
		} else {
			$this->_pk = SQL::insert($this->_table, $rec);
			$this->_data['id'] = $this->_pk;
			// if (empty($this->_pk)) {
			// 	throw new \Exception('Unexpected error saving: ' . get_class($this), __LINE__, new Exception("SQL Error: " . SQL::lastError()));
			// }
		}

		return true;
	}

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
		Flag Handling
	*/
	function delFlag($f) { $this->_data['flag'] = (intval($this->_data['flag']) & ~$f); }
	function hasFlag($f) { return (intval($this->_data['flag']) & $f); }
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
	function setFlag($f) { $this->_data['flag'] = (intval($this->_data['flag']) | $f); }

	/*
		Array Accessors
	*/
	public function toArray()
	{
		return $this->_data;
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
}
