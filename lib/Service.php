<?php
/**
 * OpenTHC Service
 * Helper to interfaces w/API to DIR, PDB, VDB, SSO
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Service
{
	private $_api_base = '';

	private $_api_secret = '';

	private $_ghc;

	private $_raw;

	/**
	 *
	 */
	function __construct($svc)
	{
		$cfg = \OpenTHC\Config::get(sprintf('openthc/%s', $svc));
		$this->_api_base = $cfg['origin'];
		if (empty($this->_api_base)) {
			$this->_api_base = rtrim($cfg['base'], '/');
		}
		if (empty($this->_api_base)) {
			$this->_api_base = sprintf('https://%s/', $cfg['hostname']);
		}
		if (empty($this->_api_base)) {
			throw new \Exception('Invalid Service Origin [CLS-031]');
		}

		$this->_api_secret = $cfg['secret'];

		$this->_ghc = new \GuzzleHttp\Client([
			'base_uri' => $this->_api_base,
			'headers' => [
				'user-agent' => 'OpenTHC/420.23.333',
				'openthc-client-pk' => $cfg['client-pk'], // '',
				'openthc-contact' => '',
				'openthc-company' => '',
				'openthc-license' => '',
				// 'authorization' => sprintf('Bearer Token %s', $this->_api_auth),
			],
			'http_errors' => false
		]);
	}

	/**
	 *
	 */
	function get($url)
	{
		$opt = [
			'headers' => [
				'authorization' => sprintf('Bearer Token %s', $this->_client_pk),
			]
		];
		$opt = $this->signRequest('GET', $url, $opt);
		$res = $this->_ghc->get($url, $opt);
		return $this->_res_to_ret($res);
	}

	/**
	 *
	 */
	function post($url, $arg)
	{
		$opt = [
			'form_params' => $arg,
			'headers' => [
				'authorization' => sprintf('Bearer Token %s', $this->_client_pk),
			]
		];
		$opt = $this->signRequest('POST', $url, $opt);
		$res = $this->_ghc->post($url, $opt);
		return $this->_res_to_ret($res);
	}

	/**
	 *
	 */
	function postJSON($url, $arg)
	{
		$opt = [
			'json' => $arg,
			'headers' => [
				'authorization' => sprintf('Bearer Token %s', $this->_client_pk),
			]
		];
		$opt = $this->signRequest('POST', $url, $opt);
		$res = $this->_ghc->post($url, $opt);
		return $this->_res_to_ret($res);

		// $head = [
		// 	'authorization' => sprintf('Bearer %s', $ck),
		// 	'date' => $dt->format(\DateTime::RFC3339),
		// 	'signature' => $sig_hash,
		// ];

		// $req = new \GuzzleHttp\Psr7\Request($verb, $path, $head, $body);
		// $res = $this->ghc->send($req);

	}

	/**
	 *
	 */
	function _res_to_ret($res)
	{
		$this->_raw = $res->getBody()->getContents();

		$ret = [
			'code' => null,
			'data' => null,
			'meta' => [],
		];

		$mime_type = $res->getHeaderLine('content-type');
		$mime_type = strtok($mime_type, ';');
		$mime_type = strtolower($mime_type);

		switch ($mime_type) {
			case 'application/json':
				$ret = json_decode($this->_raw, true);
				break;
			case 'applicaiton/pdf':
			default:
				$ret['data'] = $this->_raw;
				$ret['meta']['mime_type'] = $mime_type;
				break;
		}

		if (empty($ret['code'])) {
			$ret['code'] = $res->getStatusCode();
		}

		return $ret;

	}

	/**
	 * Put the Signature on the Request
	 */
	protected function signRequest(string $verb, string $path, $opt) : array // $body_hash, $head_hash)
	{
		$dt = new \DateTime();

		$sig_data = [];
		$sig_data[] = strtoupper($verb);
		$sig_data[] = $path;
		$sig_data[] = $dt->format(\DateTime::RFC3339);
		// Body Hash? from $opt['json'] or $opt['form_params']?
		// $sig_data[] = $pk; // Why have my Public Key in the Signature?
		$sig_data = implode("\n", $sig_data);
		$sig_hash = hash_hmac('sha256', $sig_data, $sk);

		$opt['headers']['date'] = $dt->format(\DateTime::RFC3339);

		// $ret = hash_hmac('sha256', "$verb\n$payload_hash\n$headers_hash", $Company['secret']);
		$opt['headers']['signature'] = hash_hmac('sha256', $sig_data, $this->sk);

		return $opt;
	}
}
