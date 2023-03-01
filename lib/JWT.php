<?php
/**
 * OpenTHC Common JWT library
 *
 * SPDX-License-Identifier: MIT
 *
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
		if (empty($this->_service_id)) {
			throw new \Exception('Invalid Service ID [OCJ-028]');
		}

		$this->_service_sk = \OpenTHC\Config::get(sprintf('openthc/%s/secret', $cfg['service']));
		if (empty($this->_service_sk)) {
			throw new \Exception('Invalid Service Secret [OCJ-033]');
		}

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
			$arg['iss'] = $this->_service_id;
		}
		if (empty($arg['iss'])) {
			// From Config is deprecated
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
	 * Allow to pass a Key
	 */
	static function decode($jwt, $key=null) : array
	{
		if (empty($key)) {
			// application/secret is deprecated
			$key = \OpenTHC\Config::get('application/secret');
		}
		if (is_string($key)) {
			$key = new \Firebase\JWT\Key($key, self::ALGO);
		}

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
