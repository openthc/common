<?php
/**
 * Find a License or Throw
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Traits;

trait FindLicense
{
	/**
	 *
	 */
	function findLicense($dbc, string $c0)
	{
		$sql = <<<SQL
		SELECT id, name, code, guid, stat
		FROM license
		WHERE id = :c0
		SQL;

		$arg = [
			':c0' => $c0
		];

		$License = $dbc->fetchRow($sql, $arg);
		if (empty($License['id'])) {
			throw new \Exception('Authentication Box Invalid Authentication [TFL-027]', 403);
		}

		return $License;

	}
}
