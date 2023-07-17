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
	// @todo figure out how to make this EC25519
	const ALGO = 'HS256'; // EdDSA

	private $_request = null;

	private $_service_sk = null;

	/**
	 * Construct a new JWT from parameters
	 */
	function __construct(array $cfg)
	{
		// Little Error Check
		if (empty($cfg['iss'])) {
			throw new \Exception('Invalid Issuer [CLJ-028]');
		}

		// Service Secret Key
		$this->_service_sk = $cfg['service-sk'];
		if (empty($this->_service_sk)) {
			if (empty($cfg['service'])) {
				throw new \Exception('Invalid Service [CLJ-035]');
			}
			$this->_service_sk = \OpenTHC\Config::get(sprintf('openthc/%s/secret', $cfg['service']));
		}
		if (empty($this->_service_sk)) {
			throw new \Exception('Invalid Service Secret [CLJ-040]');
		}
		if ( ! is_string($this->_service_sk)) {
			throw new \Exception('Invalid Service Secret [CLJ-043]');
		}

		unset($cfg['service']);
		unset($cfg['service-sk']);

		$this->_request = $cfg;

	}

	/**
	 * Create a Return a String Token
	 */
	function __toString() : string
	{
		$arg = $this->_request;

		$arg['iat'] = time();
		$arg['nbf'] = $arg['iat'];
		$arg['exp'] = $arg['iat'] + 60 * 15; // 15 minutes

		return \Firebase\JWT\JWT::encode($arg, $this->_service_sk, self::ALGO);

	}

	/**
	 * Allow to pass a Key
	 */
	static function decode($jwt, $key=null) : array
	{
		// @deprecated application/secret
		if (empty($key)) {
			$key = \OpenTHC\Config::get('application/secret');
		}
		if (is_string($key)) {
			$key = new \Firebase\JWT\Key($key, self::ALGO);
		}

		$decode = \Firebase\JWT\JWT::decode($jwt, $key);

		return (array)$decode;
	}

	/**
	 * Only Decode, No Verification
	 */
	static function decode_only($jwt_source) : \stdClass
	{
		$jwt_source = explode('.', $jwt_source);

		$jwt_output = new \stdClass();
		$jwt_output->head = json_decode(sodium_base642bin($jwt_source[0], SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING));
		$jwt_output->body = json_decode(sodium_base642bin($jwt_source[1], SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING));
		$jwt_output->hash = sodium_base642bin($jwt_source[2], SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);

		return $jwt_output;
	}

}
