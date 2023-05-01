<?php
/**
 * An Authentication Context Ticket
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Auth_Context_Ticket extends \OpenTHC\SQL\Record
{
	protected $_table = 'auth_context_ticket';

	/**
	 *
	 */
	function create($d)
	{
		$rec = [];
		$rec['id'] = _random_hash();

		if (!empty($d['expires_at'])) {
			$rec['expires_at'] = $d['expires_at'];
			unset($d['expires_at']);
		}

		$rec['meta'] = json_encode($d);

		$this->_pk = $this->_dbc->insert($this->_table, $rec);
		$this->_data['id'] = $this->_pk;

		return $rec['id'];

	}

	/**
	 * Determine if the Auth_Context_Ticket is valid
	 */
	function isValid()
	{
		$val = $this->_data['meta'];
		if (empty($val)) {
			return false;
		}

		$val = json_decode($val, true);
		if (empty($val)) {
			return false;
		}

		// @todo Check the expires_at Time
		$dt0 = new \DateTime($this->_data['expires_at']);
		$dt1 = new \DateTime();
		if ($dt0 < $dt1) {
			return false;
		}

		return true;

	}

}
