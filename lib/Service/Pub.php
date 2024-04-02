<?php
/**
 * OpenTHC Publishing Service
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Service;

use OpenTHC\Sodium;

class Pub
{
	private $client_pk;

	private $client_sk;

	private $server_pk;

	private $pk;

	private $sk;

	/**
	 *
	 */
	function __construct(array $cfg) {

		$this->cfg = $cfg;

	}

	function setName(string $name) {

		$this->msg['name'] = $name;

	}

	function setPath(string $path) {

		$this->client_pk = \OpenTHC\Config::get('openthc/lab/public');
		$this->client_sk = \OpenTHC\Config::get('openthc/lab/secret');

		$this->server_origin = \OpenTHC\Config::get('openthc/pub/origin');
		$this->server_pk = \OpenTHC\Config::get('openthc/pub/public');

		// Construct Message
		$this->msg = [];
		// $this->msg['name'] = basename($path);
		$this->msg['path'] = $path;

		// Create Predictable Location
		$hkey = sodium_crypto_generichash($this->client_sk, '', SODIUM_CRYPTO_GENERICHASH_KEYBYTES);
		$seed = sodium_crypto_generichash($this->msg['path'], $hkey, SODIUM_CRYPTO_GENERICHASH_KEYBYTES);
		$kp0 = sodium_crypto_box_seed_keypair($seed);
		$this->pk = sodium_crypto_box_publickey($kp0);
		$this->sk = sodium_crypto_box_secretkey($kp0);

		$this->req_path_base = Sodium::b64encode($this->pk);

	}

	/**
	 *
	 */
	function getUrl() : string {

		$req_path = sprintf('%s/%s', $this->req_path_base, $this->msg['name']);
		$url_full = sprintf('%s/%s', $this->server_origin, $req_path);

		return $url_full;

	}

	/**
	 *
	 */
	function get($path) : array {

		$url = $path;
		if ( ! preg_match('/^http.+/', $url)) {
			$path = ltrim($path, '/');
			$url = sprintf('%s/%s', $this->server_origin, $path);
		}

		// GET
		$req = _curl_init($url);

		$res = curl_exec($req);
		// echo "<<<\n$res\n###\n";
		// $res = json_decode($res, true);
		$inf = curl_getinfo($req);

		$ret = [];
		$ret['code'] = $inf['http_code'];
		$ret['data'] = $res;
		$ret['meta'] = [
			'name' => '',
			'type' => $inf['content_type']
		];

		return $ret;

	}

	function put($body, $type) : array {

		$msg = $this->msg;

		$msg['type'] = $type;

		$msg['auth'] = Sodium::b64encode($this->pk);
		$msg['auth'] = Sodium::encrypt($msg['auth'], $this->sk, $this->server_pk);
		$msg['auth'] = Sodium::b64encode($msg['auth']);

		$req_auth = $this->cfg;
		$req_auth['message'] = $msg['auth'];
		$req_auth = json_encode($req_auth);

		$req_auth = Sodium::encrypt($req_auth, $this->client_sk, $this->server_pk);
		$req_auth = Sodium::b64encode($req_auth);

		$url = $this->getUrl();
		$req = _curl_init($url);
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($req, CURLOPT_POSTFIELDS, $body);
		curl_setopt($req, CURLOPT_HTTPHEADER, [
				sprintf('authorization: OpenTHC %s.%s', $this->client_pk, $req_auth),
				sprintf('content-type: %s', $msg['type']),
		]);

		$res = curl_exec($req);
		// echo "<<<\n$res\n###\n";
		$res = json_decode($res, true);
		$inf = curl_getinfo($req);

		$ret = [];
		$ret['code'] = $inf['http_code'];
		$ret['data'] = $res['data'];
		$ret['meta'] = $res['meta'];

		return $ret;

	}

}
