<?php
/**
 * Common Session
 * Configure via PHP Settings (or apache2.conf)
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Middleware;

class Session
{
	/**
	 * @todo low-risk interface
	 * @param \Slim\Http\Request $request
	 * @param \Slim\Http\RequestHandler $handler
	 * @param array $ARG
	 * use Psr\Http\Message\ServerRequestInterface as Request;
		// use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
	*/
	public function __invoke($request, $handler)
	{
		$this->open();

		return $handler->handle($request);

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
