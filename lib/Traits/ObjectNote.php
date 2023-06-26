<?php
/**
 * Object Note
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Traits;

trait ObjectNote
{
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

		if ( ! empty($pin)) {

			$pin = 1;

			// Update all other Notes on this Object to be !PIN
			$sql = 'UPDATE object_note SET flag = (flag & ~ :f0::int) WHERE link = :l0';
			$arg = array(
				':l0' => $link,
				':f0' => $pin,
			);
			//  SQL::query($sql, $arg);
			$this->_dbc->query($sql, $arg);

		}

		 // Add Note
		$sql = 'INSERT INTO object_note (auth_contact_id, flag, link, note) VALUES (?, ?, ?, ?)';
		$arg = array($_SESSION['Contact']['id'], $pin, $link, $note);
		//  $res = SQL::query($sql, $arg);
		$res = $this->_dbc->query($sql, $arg);

		// Tag Object
		// $sql = sprintf('UPDATE %s SET flag = flag | :f1 WHERE id = :id', $this->_table);
		// $arg = array(':f1' => self::FLAG_HAS_NOTE, ':id' => $this->_data['id']);
		// SQL::query($sql, $arg);

		// SQL::query('COMMIT');

		$this->_data['flag'] = ($this->_data['flag'] | self::FLAG_HAS_NOTE);

		return $res;

	}

	 /**
	  * @return Array of Note Objects
	  */
	function getNotes()
	{
		$link = sprintf('%s:%s', $this->_table, $this->_data['id']);
		$sql = 'SELECT * FROM object_note WHERE link = ?';
		$arg = array($link);
		$res = $this->_dbc->fetchAll($sql, $arg);
		return $res;
	}
}
