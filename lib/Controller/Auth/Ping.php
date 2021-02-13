<?php
/**
 * Output Details about the Session
 */

namespace OpenTHC\Controller\Auth;

class Ping extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		ksort($_COOKIE);
		ksort($_SESSION);

		return $RES->withJSON([
			'data' => [
				'_COOKIE' => $_COOKIE,
				'_SESSION' => $_SESSION,
			],
			'meta' => []
		]);

	}
}
