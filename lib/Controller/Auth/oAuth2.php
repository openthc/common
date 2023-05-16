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
	protected function getProvider($ret=null)
	{
		$cfg = \OpenTHC\Config::get('openthc/sso');
		$cfg['client_id'] = $cfg['public'] ?: $cfg['id'];
		$cfg['client_sk'] = $cfg['secret'];

		if (empty($cfg['client_id'])) {
			throw new \Exception('Invalid Client Configuration [CAO-040]');
		}
		if (empty($cfg['client_sk'])) {
			throw new \Exception('Invalid Client Configuration [CAO-043]');
		}

		$sso_origin = $cfg['origin'];
		if (empty($sso_origin)) {
			$sso_origin = $cfg['base'];
			if (empty($sso_origin)) {
				$sso_origin = sprintf('https://%s', $cfg['hostname']);
			}
		}
		$sso_origin = rtrim($sso_origin, '/');

		$url = sprintf('%s/auth/back?%s', OPENTHC_SERVICE_ORIGIN, http_build_query([ 'r' => $ret ]));
		$url = trim($url, '?');
		$loc = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId' => $cfg['client_id'],
			'clientSecret' => $cfg['client_sk'],
			'redirectUri' => $url,
			'urlAuthorize' => sprintf('%s/oauth2/authorize', $sso_origin),
			'urlAccessToken' => sprintf('%s/oauth2/token', $sso_origin),
			'urlResourceOwnerDetails' => sprintf('%s/oauth2/profile', $sso_origin),
			'verify' => true
		]);

		return $loc;

	}

}
