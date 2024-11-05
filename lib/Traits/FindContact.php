<?php
/**
 * Find a Contact or Throw
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\Traits;

trait FindContact
{
	function findContact($dbc, string $c0)
	{
		$sql = <<<SQL
		SELECT id, username
		FROM auth_contact
		WHERE id = :c0
		SQL;

		$arg = [ ':c0' => $c0 ];

		$Contact = $dbc->fetchRow($sql, $arg);
		if (empty($Contact['id'])) {
			throw new \Exception('Authentication Box Invalid Authentication [PCB-095]', 403);
		}

		return $Contact;

	}
}
