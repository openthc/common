<?php
/**
 * Contact Model
 */

namespace OpenTHC;

use Edoceo\Radix\DB\SQL;

class Contact extends \OpenTHC\SQL\Record
{
	const TABLE = 'contact';

	const FLAG_EMAIL_GOOD = 0x00000001;
	const FlAG_PHONE_GOOD = 0x00000002;
	const FLAG_EMAIL_WANT = 0x00000004;
	const FLAG_PHONE_WANT = 0x00000008;

	const FLAG_ROOT     = 0x00000010;
	const FLAG_BILL     = 0x00000020;

	const FLAG_DISABLED = 0x01000000;

	const FLAG_MUTE     = 0x04000000;
	const FLAG_DELETED  = 0x08000000;

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
