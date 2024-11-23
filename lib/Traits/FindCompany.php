<?php
/**
 * Find a Company or Throw
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Traits;

trait FindCompany
{
	function findCompany($dbc, string $c0)
	{
		$sql = <<<SQL
		SELECT id, name, stat, cre, dsn, iso3166, iso3166 AS region, tz
		FROM auth_company
		WHERE id = :c0
		SQL;

		$arg = [
			':c0' => $c0
		];

		$Company = $dbc->fetchRow($sql, $arg);
		if (empty($Company['id'])) {
			throw new \Exception('Authentication Box Invalid Authentication [PCB-095]', 403);
		}

		if (empty($Company['dsn'])) {
			throw new \Exception('Authentication Box Invalid Configuration [PCB-072]', 501);
		}

		return $Company;

	}
}
