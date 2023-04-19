<?php
/**
 * oAuth2 Base Controller
 */

namespace OpenTHC\Controller\Auth;

class oAuth2 extends \OpenTHC\Controller\Base
{
	/**
	 * Verify the State or DIE
	 */
	function checkState()
	{
		$a = $_SESSION['oauth2-state'];
		$b = $_GET['state'];

		unset($_SESSION['oauth2-state']);

		if (empty($a)) {
			_exit_html('<h1>Invalid State [CAO-021]</h1><p>Please try to <a href="/auth/shut">sign in</a> again.</p>', 400);
		}

		if (empty($b)) {
			_exit_html('<h1>Invalid State [CAO-025]</h1><p>Please try to <a href="/auth/shut">sign in</a> again.</p>', 400);
		}

		if ($a != $b) {
			_exit_html('<h1>Invalid State [CAO-029]</h1><p>Please try to <a href="/auth/shut">sign in</a> again.</p>', 400);
		}

	}

	/**
	 * Hard Coded Values for our SSO Service
	 */
	protected function getProvider($r=null)
	{
		$cfg = \OpenTHC\Config::get('openthc/sso');

		$u = sprintf('https://%s/auth/back?%s', $_SERVER['SERVER_NAME'], http_build_query([ 'r' => $r ]));
		$u = trim($u, '?');
		$p = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId' => ($cfg['public'] ?: $_SERVER['SERVER_NAME']),
			'clientSecret' => $cfg['secret'],
			'redirectUri' => $u,
			'urlAuthorize' => sprintf('https://%s/oauth2/authorize', $cfg['hostname']),
			'urlAccessToken' => sprintf('https://%s/oauth2/token', $cfg['hostname']),
			'urlResourceOwnerDetails' => sprintf('https://%s/oauth2/profile', $cfg['hostname']),
			'verify' => true
		]);

		return $p;

	}

}
