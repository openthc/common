<?php
/**
 * Close the Session
 */

namespace OpenTHC\Controller\Auth;

class Shut extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$sid = session_id();
		$snm = session_name();

		if (!empty($sid)) {

			$scp = session_get_cookie_params();

			// Clear Session
			session_unset();
			session_gc();

			// Clear Cookies Manually
			//foreach ($_COOKIE as $k => $v) {
			//	if ($k == $snm) {
			//		setcookie($k, '', time() - 3600);
			//	}
			//}

			// Rewrite this array key for PHP
			$scp['expires'] = $scp['lifetime'];
			unset($scp['lifetime']);
			setcookie($snm, '', $scp);

			// this will set a new cookie
			// session_regenerate_id(true);

			session_destroy();
			session_write_close();

		}

		return $RES->withJSON([
			'meta' => [ 'detail' => 'Session Closed' ],
			'data' => [],
		]);
	}
}
