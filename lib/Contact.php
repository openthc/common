<?php
/**
 * Contact Model
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

use Edoceo\Radix\DB\SQL;

class Contact extends \OpenTHC\SQL\Record
{
	const FLAG_EMAIL_GOOD = 0x00000001;
	const FLAG_PHONE_GOOD = 0x00000002;
	const FLAG_EMAIL_WANT = 0x00000004;
	const FLAG_PHONE_WANT = 0x00000008;

	const FLAG_ROOT     = 0x00000010;
	// const FLAG_PRIMARY  = 0x00000100; // Primary Contact ALT?

	const FLAG_BILL     = 0x00000020;
	// const FLAG_BILLING // ALT?

	const FLAG_B2B_VENDOR = 0x00001000;
	const FLAG_B2B_CLIENT = 0x00002000;
	const FLAG_B2C_VENDOR = 0x00004000;
	const FLAG_B2C_CLIENT = 0x00008000;

	const FLAG_DISABLED = 0x01000000;

	const FLAG_MUTE     = 0x04000000;
	const FLAG_DELETED  = 0x08000000;

	const STAT_INIT = 100;
	const STAT_LIVE = 200;
	const STAT_GONE = 410;

	const TABLE = 'contact';

	protected $_table = 'contact';

	static function findByEmail($x)
	{
		$x = strtolower(trim($x));
		$res = SQL::fetch_row('SELECT * FROM contact WHERE email = ?', array($x));
		if (!empty($res)) {
			return new self($res);
		}

		return false;
	}

}
