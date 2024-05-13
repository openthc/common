<?php
/**
 * OpenTHC OPA Client library
 *
 * SPDX-License-Identifier: MIT
 *
 * We interact with running OPA service over HTTP
 */

namespace OpenTHC;

class OPA
{
	private $_url = '';

	/**
	 *
	 */
	function __construct($origin=null)
	{
		if (empty($origin)) {
			$origin = \OpenTHC\Config::get('opa/origin');
		}
		if (empty($origin)) {
			// $origin = 'http://127.0.0.1:6000';
			throw new \Exception('Invalid Configuration for OPA [CLO-023]');
		}

		$this->_url = $origin;

	}

	/**
	 * Easy OPA Query Helper
	 */
	static function permit(string $path, $ctx1=[]) : bool {

		$opa = new self();

		$ctx0 = [
			'service' => [ 'id' => $_SESSION['Service']['id'], ],
			'contact' => [ 'id' => $_SESSION['Contact']['id'], ],
			'company' => [ 'id' => $_SESSION['Company']['id'], ],
			'license' => [ 'id' => $_SESSION['License']['id'], ],
		];

		// Array Merge Recursive?
		$ctx2 = array_merge($ctx0, $ctx1);

		$res = $opa->policy_get($path, $ctx2);

		return ('permit' == $res->access);

	}

	function delData(string $path) {

		$url = sprintf('%s/v1/data/%s', $this->_url, $path);
		$url = rtrim($url, '/');
		$req = _curl_init($url);
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($req, CURLOPT_TIMEOUT, 4);

		$res = curl_exec($req);
		$res = json_decode($res);

		return $res;

	}

	/**
	 *
	 */
	function setData(string $path, $data) {

		$path = sprintf('/v1/data/%s', $path);
		$path = rtrim($path, '/');

		if (is_array($data) || is_object($data)) {
			$data = json_encode($data);
		}

		$res = $this->_curl_put($path, $data, 'application/json');
		$res = json_decode($res);

		return $res;

	}

	/**
	 * Maybe chkPolicy?  and getPolicy would return the /policiies/$path?
	 */
	function getPolicy(string $path, $ctx) {

		$url = sprintf('%s/v1/data/%s', $this->_url, $path);
		$req = _curl_init($url);
		// POST JSON
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');
		// curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 4); // Allowed Connect Time (s)
		// curl_setopt($req, CURLOPT_CONNECTTIMEOUT_MS, 4000); // Allowed Connect Time (ms)
		curl_setopt($req, CURLOPT_TIMEOUT, 4); // Allowed Total Time (s)
		// curl_setopt($req, CURLOPT_TIMEOUT_MS, 8000); // Allowed Total Time (ms)
		curl_setopt($req, CURLOPT_POSTFIELDS, json_encode([ 'input' => $ctx ]));
		curl_setopt($req, CURLOPT_HTTPHEADER, [
			'content-type: application/json'
		]);

		$res = curl_exec($req);
		$res = json_decode($res);

		if (empty($res)) {
			return new \stdClass();
		}

		if (empty($res->result)) {
			return new \stdClass();
		}

		return $res->result;

	}

	/**
	 *
	 */
	function setPolicy(string $rego) {

		if ( ! preg_match('/^package (.+)$/m', $rego, $m)) {
			throw new \Exception('Invalid REGO [OLO-053]');
		}
		$path = str_replace('.', '/', $m[1]);

		$path = sprintf('/v1/policies/%s', $path);
		$res = $this->_curl_put($path, $rego, 'text/plain');
		$res = json_decode($res);

		return $res;

	}

	function query()
	{

	}

	/**
	 * PUT Wrapper
	 */
	function _curl_put(string $path, string $data, string $type) {

		$path = ltrim($path, '/');
		$url = sprintf('%s/%s', $this->_url, $path);

		$req = _curl_init($url);
		// POST TEXT
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($req, CURLOPT_TIMEOUT, 8); // Allowed Total Time (s)
		curl_setopt($req, CURLOPT_POSTFIELDS, $data);
		curl_setopt($req, CURLOPT_HTTPHEADER, [
			sprintf('content-type: %s', $type)
		]);

		$res = curl_exec($req);
		$inf = curl_getinfo($req);

		switch ($inf['http_code']) {
			case 200:
			case 204:
				// OK
				break;
			default:
				// var_dump($inf);
				// echo "raw:$res\n";
				throw new \Exception(sprintf('Invalid Response "%d" from OPA [OLO-149]', $inf['http_code']));
		}

		return $res;

	}

}
