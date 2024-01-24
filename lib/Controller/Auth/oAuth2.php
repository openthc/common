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
		$client_cfg = [
			'clientId' => '',
			'clientSecret' => '',
			'redirectUri' => '',
			'urlAccessToken' => '',
			'urlAuthorize' => '',
			'urlResourceOwnerDetails' => '',
			'verify' => true
		];

		$cfg = \OpenTHC\Config::get('openthc/sso');

		// Find the oAuth Server Origin
		// From from two deprecated ways
		$sso_origin = $cfg['origin'];
		if (empty($sso_origin)) {
			$sso_origin = $cfg['base'];
			if (empty($sso_origin)) {
				$sso_origin = sprintf('https://%s', $cfg['hostname']);
			}
		}
		$sso_origin = rtrim($sso_origin, '/');

		$client_cfg['urlAccessToken'] = sprintf('%s/oauth2/token', $sso_origin);
		$client_cfg['urlAuthorize'] = sprintf('%s/oauth2/authorize', $sso_origin);
		$client_cfg['urlResourceOwnerDetails'] = sprintf('%s/oauth2/profile', $sso_origin);

		// Auth Keys
		// handles deprecated methods here too
		$client_cfg['clientId'] = $cfg['oauth-client-id'] ?: $cfg['client-id'] ?: $cfg['public'] ?: $cfg['id'];
		$client_cfg['clientSecret'] = $cfg['oauth-client-sk'] ?: $cfg['client-sk'] ?: $cfg['secret'];

		if (empty($client_cfg['clientId'])) {
			throw new \Exception('Invalid Client Configuration [CAO-040]');
		}
		if (empty($client_cfg['clientSecret'])) {
			throw new \Exception('Invalid Client Configuration [CAO-043]');
		}

		$url = sprintf('%s/auth/back?%s', OPENTHC_SERVICE_ORIGIN, http_build_query([ 'r' => $ret ]));
		$url = trim($url, '?');

		$client_cfg['redirectUri'] = $url;

		$loc = new \League\OAuth2\Client\Provider\GenericProvider($client_cfg);

		return $loc;

	}

	/**
	 * @param $p oAuth Provider
	 */
	protected function getProfileFromToken($p)
	{
		if (empty($_GET['code'])) {
			_exit_html_fail('<h1>Invalid Request [CAO-095]</h1>', 400);
		}

		$this->checkState();

		// Try to get an access token using the authorization code grant.
		$tok = null;
		try {
			$tok = $p->getAccessToken('authorization_code', [
					'code' => $_GET['code']
			]);
		} catch (\Exception $e) {
			_exit_html_fail('<h1>Invalid Access Token [CAO-107]</h1>', 400);
		}

		if (empty($tok)) {
			_exit_html_fail('<h1>Invalid Access Token [CAO-111]</h1>', 400);
		}

		// Token Data Verify
		$x = json_decode(json_encode($tok), true);
		if (empty($x['access_token'])) {
			_exit_html_fail('<h1>Invalid Access Token [CAO-117]</h1>', 400);
		}
		if (empty($x['token_type'])) {
			_exit_html_fail('<h1>Invalid Access Token [CAO-120],/h1>', 400);
		}

		try {
			$x = $p->getResourceOwner($tok);
			$Profile = $x->toArray();

			if (empty($Profile['Contact']['id'])) {
				_exit_html_fail('<h1>Invalid [CAO-128]</h1>', 403);
			}

			if (empty($Profile['Company']['id'])) {
				_exit_html_fail('<h1>Invalid [CAO-132]</h1>', 403);
			}

			if (is_string($Profile['scope'])) {
				$Profile['scope'] = explode(' ', $Profile['scope']);
			}

			return $Profile;

		} catch (\Exception $e) {
			_exit_html_fail(sprintf('<h1>%s [CAO-136]</h1>', __h($e->getMessage())), 500);
		}

	}

}
