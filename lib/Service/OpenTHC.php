<?php
/**
 * OpenTHC Service Adapter
 */

namespace OpenTHC\Service;

class OpenTHC
{
	private $_api_base = '';
	private $_api_auth = '';
	private $_ghc;
	private $_raw;

	function __construct($svc)
	{
		$cfg = \OpenTHC\Config::get(sprintf('openthc/%s', $svc));
		$this->_api_base = sprintf('https://%s/', $cfg['hostname']);
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

	function get($url)
	{
		$res = $this->_ghc->get($url);
		$this->_raw = $res->getBody()->getContents();
		$ret = json_decode($this->_raw, true);
		if (empty($ret['code'])) {
			$ret['code'] = $res->getStatusCode();
		}
		return $ret;
	}

	function post($url, $arg)
	{
		$res = $this->_ghc->post($url, $arg);
		$this->_raw = $res->getBody()->getContents();
		$ret = json_decode($this->_raw, true);
		if (empty($ret['code'])) {
			$ret['code'] = $res->getStatusCode();
		}
		return $ret;
	}
}
