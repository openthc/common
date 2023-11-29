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
		$opt = [];
		$res = $this->_ghc->get($url, $opt);
		return $this->_res_to_ret($res);
	}

	/**
	 *
	 */
	function post($url, $arg)
	{
		$opt = [];
		$res = $this->_ghc->post($url, [ 'form_params' => $arg ], $opt);
		return $this->_res_to_ret($res);
	}

	/**
	 *
	 */
	function postJSON($url, $arg)
	{
		$opt = [];
		$res = $this->_ghc->post($url, [ 'json' => $arg ], $opt);
		return $this->_res_to_ret($res);
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
}
