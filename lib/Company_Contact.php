<?php
/**
 * Company-Contact common class
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Company_Contact extends \OpenTHC\SQL\Record
{

	const STAT_PROC = 102;
	const STAT_LIVE = 200;
	const STAT_DEAD = 401;
	const STAT_FBID = 403;
	const STAT_GONE = 410;

	protected $_table = 'company_contact';

}