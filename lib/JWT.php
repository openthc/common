<?php
/**
 * OpenTHC Common JWT library
 * @todo specify supported algorithms for application https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 */

namespace OpenTHC;

class JWT
{
	const ALGO = 'HS256';

	private $_request = null;

	private $_service_id = null;
	private $_service_sk = null;

	/**
	 * Construct a new JWT from parameters
	 */
	function __construct($cfg)
	{
		$this->_service_id = \OpenTHC\Config::get(sprintf('openthc/%s/id', $cfg['service']));
		$this->_service_sk = \OpenTHC\Config::get(sprintf('openthc/%s/secret', $cfg['service']));

		unset($cfg['service']);

		$this->_request = $cfg;

	}

	/**
	 * Create a Return a String Token
	 */
	function __toString()
	{
		// $arg = self::base_claims();
		$arg = $this->_request;

		// We require this one
		if (empty($arg['iat'])) {
			$arg['iat'] = time();
		}

		// And this too
		if (empty($arg['iss'])) {
			$arg['iss'] = \OpenTHC\Config::get('application/id');
		}

		return \Firebase\JWT\JWT::encode($arg, $this->_service_sk, self::ALGO);
	}

	/**
	 *
	 */
	static function encode($service, $payload) : string
	{
		$cfg = sprintf('openthc/%s/secret', $service);
		$key = \OpenTHC\Config::get($cfg);
		$jwt = \Firebase\JWT\JWT::encode($payload, $key, self::ALGO);
		return $jwt;
	}

	/**
	 *
	 */
	static function decode($jwt) : array
	{
		$key = \OpenTHC\Config::get('application/secret');
		$key = new \Firebase\JWT\Key($key, self::ALGO);
		$decode = \Firebase\JWT\JWT::decode($jwt, $key);
		return (array)$decode;
	}

	/**
	 *
	 */
	static function base_claims(): array
	{
		$tz = new \DateTimeZone($_SESSION['tz']);
		$expire = new \DateTime(date(\DateTime::RFC3339, $_SERVER['REQUEST_TIME']), $tz);
		$expire->add(new \DateInterval('PT24H'));
		$expire = $expire->getTimestamp();
		return [
			'iat'  => $_SERVER['REQUEST_TIME'],
			'iss'  => \OpenTHC\Config::get('application/id'),
			'nbf'  => $_SERVER['REQUEST_TIME'],
			'exp'  => $expire,
		];
	}
}
