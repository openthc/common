<?php
/**
 *
 */

namespace OpenTHC\Test\Feature;

trait GuzzleClient
{
	/**
	 *
	 */
	function getGuzzleClient(array $cfg1=[])
	{
		$cfg0 = [
			'base_uri' => '',
			'allow_redirects' => false,
			'cookies' => true,
			'debug' => $_ENV['OPENTHC_TEST_HTTP_DEBUG'] ?: false,
			'headers' => [
				'openthc-service-id' => '',
				'openthc-contact-id' => '',
				'openthc-company-id' => '',
				'openthc-license-id' => '',
			],
			'http_errors' => false,
			'request.options' => [
				'exceptions' => false,
			],
		];

		$cfg2 = array_merge($cfg0, $cfg1);

		$c = new \GuzzleHttp\Client($cfg2);

		return $c;
	}

}
