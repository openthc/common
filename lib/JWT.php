<?php
/**
 * OpenTHC Common JWT library
 * @todo specify supported algorithms for application https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 */

namespace OpenTHC;

class JWT
{
	const ALGO = 'HS256';

	static function encode($service, $payload)
	{
		$cfg = sprintf('openthc/%s/secret', $service);
		$key = \OpenTHC\Config::get($cfg);
		$jwt = \Firebase\JWT\JWT::encode($payload, $key, self::ALGO);
		return $jwt;
	}

	static function decode($jwt)
	{
		$key = \OpenTHC\Config::get('application/secret');
		$key = new \Firebase\JWT\Key($key, self::ALGO);
		$decode = \Firebase\JWT\JWT::decode($jwt, $key);
		return $decode;
	}

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
