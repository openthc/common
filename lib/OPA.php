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

		$res = $opa->chkPolicy($path, $ctx2);

		return ('permit' == $res->access);

	}

	function getData(string $path) {

		$url = $this->makeUrl('/v1/data', $path);
		$req = _curl_init($url);
		$res = curl_exec($req);
		$inf = curl_getinfo($req);
		curl_close($req);

		$res = json_decode($res);

		return (object)[
			'code' => $inf['http_code'],
			'data' => $res
		];

	}

	function delData(string $path) {

		$url = $this->makeUrl('/v1/data', $path);
		$req = _curl_init($url);
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($req, CURLOPT_TIMEOUT, 4);

		$res = curl_exec($req);
		$inf = curl_getinfo($req);
		curl_close($req);

		$res = json_decode($res);

		return (object)[
			'code' => $inf['http_code'],
			'data' => $res
		];

	}

	/**
	 *
	 */
	function setData(string $path, $data) {

		if (is_array($data) || is_object($data)) {
			$data = json_encode($data);
		}

		$url = $this->makeUrl('/v1/data', $path);
		$res = $this->_curl_put($url, $data, 'application/json');

		return $res;

	}

	/**
	 *
	 */
	function getPolicy(string $path) {

		$url = $this->makeUrl('/v1/policies', $path);
		$req = _curl_init($url);
		$res = curl_exec($req);
		$inf = curl_getinfo($req);
		curl_close($req);

		return (object)[
			'code' => $inf['http_code'],
			'data' => json_decode($res),
		];

	}

	/**
	 * Check Policy Return Policy Result
	 */
	function chkPolicy(string $path, $ctx) {

		$url = $this->makeUrl('/v1/data', $path);
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
		$inf = curl_getinfo($req);
		curl_close($req);

		switch ($inf['http_code']) {
		case 200:
			// OK
			break;
		default:
			throw new \Exception(sprintf('Invalid Response "%d" from OPA [CLO-131]', $inf['http_code']));
		}

		$res = json_decode($res);

		if ( ! empty($res->result)) {
			$res = $res->result;
		} else {
			// if result is empty we default to not permitted
			$res = new \stdClass();
			$res->permit = false;
		}

		// if (empty($res->access)) {
		// 	$res->access = 'reject';
		// }

		// // @deprecated
		// if (empty($res->reason)) {
		// 	$res->reason = new \stdClass();
		// }

		return $res;
	}

	function delPolicy(string $path)
	{
		$url = $this->makeUrl('/v1/policies', $path);
		$req = _curl_init($url);
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'PUT');

		$res = curl_exec($req);
		$inf = curl_getinfo($req);

	}

	/**
	 *
	 */
	function setPolicy(string $rego) {

		if ( ! preg_match('/^package (.+)$/m', $rego, $m)) {
			throw new \Exception('Invalid REGO [OLO-053]');
		}
		$path = str_replace('.', '/', $m[1]);

		$url = $this->makeUrl('/v1/policies', $path);
		$res = $this->_curl_put($url, $rego, 'text/plain');

		return $res;

	}

	/**
	 * PUT Wrapper
	 */
	protected function _curl_put(string $url, string $data, string $type) {

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
			case 301:
			case 400:
				// OK
				break;
			default:
				var_dump($inf);
				echo "raw:$res\n";
				throw new \Exception(sprintf('Invalid Response "%d" from OPA [OLO-149]', $inf['http_code']));
		}

		return (object)[
			'code' => $inf['http_code'],
			'data' => json_decode($res),
		];

	}

	/**
	 * Return a Full URL to OPA
	 */
	protected function makeUrl(string $base, string $path) : string {

		$base = trim($base, '/');
		$path = ltrim($path, '/');
		$url = sprintf('%s/%s/%s', $this->_url, $base, $path);
		$url = rtrim($url, '/');
		return $url;
	}

}
