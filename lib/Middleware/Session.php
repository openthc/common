<?php
/**
 * Common Session
 * Configure via PHP Settings (or apache2.conf)
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Middleware;

class Session extends \OpenTHC\Middleware\Base
{
	/**
	 * @todo low-risk interface
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	*/
	function __invoke(\Psr\Http\Message\ServerRequestInterface $REQ, \Psr\Http\Message\ResponseInterface $RES, ?array $NMW = null)
	{
		$this->open();

		$RES = $NMW($REQ, $RES);

		return $RES;

	}

	protected function open()
	{
		$sn = session_name();

		if (!empty($_COOKIE[$sn])) {

			// Session ID provided here, use normal PHP methods

		} elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {

			$x = (preg_match('/^Bearer (.+)$/', $_SERVER['HTTP_AUTHORIZATION'], $m) ? $m[1] : null);
			if (!empty($x)) {
				session_id($x);
			}

		} elseif (!empty($_GET['sid'])) {

			$x = (preg_match('/^(\w+)$/', $_GET['sid'], $m) ? $m[1] : null);
			if (!empty($x)) {
				session_id($x);
			}

		}

		session_start();

		if (empty($_SESSION['crypt-key'])) {
			$_SESSION['crypt-key'] = sha1(random_bytes(128));
		}

	}
}
