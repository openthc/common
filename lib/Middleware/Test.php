<?php
/**
 * Enables Test Mode, Sets Cookie Too
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Middleware;

class Test extends Base
{
	/**
	 * @todo low-risk interface
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	 */
	function __invoke($REQ, $RES, $NMW) {

		$test = false;

		if (!empty($_COOKIE['test'])) {
			$test = true;
		}

		if (!empty($_GET['_t'])) {
			$key = \OpenTHC\Config::get('test/secret');
			if ($_GET['_t'] == $key) {
				$test = true;
				setcookie('test', 'true', 0, '/', '', true, true);
			}
		}

		if ($test) {
			$_ENV['test'] = $test;
		}

		$RES = $NMW($REQ, $RES);

		if ($test) {
			$RES = $RES->withHeader('openthc-test-mode', '1');
		}

		return $RES;

	}
}
