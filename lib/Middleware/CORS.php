<?php
/**
 * CORS Headers
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Middleware;

class CORS extends \OpenTHC\Middleware\Base
{
	/**
	 * @todo low-risk interface
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	 */
	public function __invoke($REQ, $RES, $NMW)
	{
		// no origin? pass-thru
		if (empty($_SERVER['HTTP_ORIGIN'])) {
			return $NMW($REQ, $RES);
		}

		$RES = $RES->withHeader('access-control-allow-headers', 'Authorization')
			->withHeader('access-control-allow-methods', 'GET,POST,PUT')
			->withHeader('access-control-max-age', 86400)
			//->withHeader('content-type', 'text/plain')
			->withHeader('vary', 'accept-encoding');

		// withHeader('access-control-allow-credentials', 'true')

		// No Response on OPTIONS
		if ($REQ->isOptions()) {
			return $RES->withHeader('content-length', '0')
				->withHeader('content-type', 'text/plain');
		}

		// @todo lookup origin, if we've got a whitelist
		$o = strtolower($_SERVER['HTTP_ORIGIN']);
		$cfg = \OpenTHC\Config::get('cors_origin');
		if (!empty($cfg)) {
			$key_list = array_keys($cfg);
			if (!in_array('*', $key_list)) { // no star
				if (!in_array($o, $key_list)) { // no host
					return $RES->withStatus(403);
				}
			}
		}

		// Allow this Origin
		$RES = $RES->withHeader('access-control-allow-origin', $o);


		return $NMW($REQ, $RES);

	}
}
