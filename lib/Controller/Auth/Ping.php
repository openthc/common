<?php
/**
 * Output Details about the Session
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Controller\Auth;

class Ping extends \OpenTHC\Controller\Base
{
	/**
	 * @todo low-risk interface
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	*/
	function __invoke($REQ, $RES, $ARG)
	{
		ksort($_COOKIE);
		ksort($_SESSION);

		$code = 200;

		$data = [
			'data' => [
				'_COOKIE' => $_COOKIE,
				'_SESSION' => $_SESSION,
			],
			'meta' => []
		];

		$flag = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

		return $RES->withJSON($data, $code, $flag);

	}
}
