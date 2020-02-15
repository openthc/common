<?php
/**
 * Common Session
 * Configure via PHP Settings (or apache2.conf)
 */

namespace OpenTHC\Middleware;

class Session extends \OpenTHC\Middleware\Base
{
	public function __invoke($REQ, $RES, $NMW)
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
			$_SESSION['crypt-key'] = sha1(openssl_random_pseudo_bytes(256));
		}

	}
}
