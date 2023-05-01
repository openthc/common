<?php
/**
 * OpenTHC Service Adapter
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Service;

class OpenTHC
{
	private $_api_base = '';
	private $_api_auth = '';
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
			throw new \Exception('Invalid Service Origin [LSO-031]');
		}

		$this->_api_auth = $cfg['secret'];

		$this->_ghc = new \GuzzleHttp\Client([
			'base_uri' => $this->_api_base,
			'headers' => [
				'user-agent' => 'OpenTHC/420.20.170',
				'authorization' => sprintf('Bearer %s', $this->_api_auth),
			],
			'http_errors' => false
		]);
	}

	/**
	 *
	 */
	function get($url)
	{
		$res = $this->_ghc->get($url);
		return $this->_res_to_ret($res);
	}

	/**
	 *
	 */
	function post($url, $arg)
	{
		$res = $this->_ghc->post($url, $arg);
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
