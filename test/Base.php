<?php
/**
 * Base Class for Test Cases
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test;

// class \OpenTHC\Test\UI_Base_Case extends \OpenTHC\Test\Base_Case {}
// class Base_Case extends \OpenTHC\Test\Base_Case // \PHPUnit\Framework\TestCase

class Base extends \PHPUnit\Framework\TestCase
{
	// Process ID
	protected $_pid = null;

	protected $type_expect = 'application/json';


	/**
	 *
	 */
	function __construct($name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->_pid = getmypid();

	}

	/**
	 *
	 */
	function getWebDriver()
	{
	}

	/**
	 *
	 */
	function getGuzzleClient(array $cfg1=[])
	{
		$cfg0 = [
			'base_uri' => '',
			'allow_redirects' => false,
			'cookies' => true,
			'debug' => defined('OPENTHC_TEST_HTTP_DEBUG'), // $_ENV['debug-http'],
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

	/**
	 *Intends to become an assert wrapper for a bunch of common response checks
	 * @param $res, Response Object
	 * @param int $code_expect=200 the status code desired
	 * @param $type_expect=application/json the mime type desired
	 * @return string body
	 */
	function assertValidResponse($res, $code_expect=200, $type_expect=null, $dump=null) : mixed {
		// var_dump($this->testHandler->getRecords());
		// $this->assertNotEmpty($res);

		if (empty($type_expect)) {
			$type_expect = $this->type_expect;
		}

		// $res could be Response Object
		// $res could be an already cleaned array?
		// This was from BONG
		// if (is_object($res)) {
		// 	// $this->assertTrue($res instanceof \)
		// 	$ret_code = $res->getStatusCode();
		// 	$this->assertEquals($want_code, $ret_code);

		// 	$res = json_decode($res->getBody()->getContents(), true);

		// } else {

		// 		$this->assertIsArray($res);
		// 		$this->assertArrayHasKey('code', $res);
		// 		$this->assertArrayHasKey('data', $res);
		// 		$this->assertArrayHasKey('meta', $res);

		// 		$this->assertEquals($want_code, $res['code']);
		// }

		$this->raw = null;

		$code_actual = 0;
		$type_actual = '';

		if (is_array($res)) {
			$code_actual = $res['code'];
			$type_actual = 'application/json';
		} elseif (is_object($res)) {

			$this->raw = $res->getBody()->getContents();

			$code_actual = $res->getStatusCode();

			$type_actual = $res->getHeaderLine('content-type');
			$type_actual = strtok($type_actual, ';');
			$type_actual = strtolower($type_actual);

		}

		if ($code_expect != $code_actual) {
			$dump = "HTTP $code_expect != $code_actual";
		}
		if ($type_expect != $type_actual) {
			$dump = "MIME $type_expect != $type_actual";
		}

		if ( ! empty($dump)) {
			echo "\n<<< $dump <<< $code_actual <<<\n{$this->raw}\n###\n";
		}

		$this->assertEquals($code_expect, $code_actual);
		$this->assertEquals($type_expect, $type_actual);

		if (is_array($res)) {
			return $res;
		}

		switch ($type_expect) {
		case 'application/json':
			$ret = \json_decode($this->raw, true);
			$this->assertIsArray($ret);
			// $this->assertArrayHasKey('data', $ret);
			// $this->assertArrayHasKey('meta', $ret);
			break;
		default:
			$ret = $this->raw;
			break;
		}

		return $ret;

	}

	/**
	 * Authenticate via SSO
	 * @param string username
	 * @param string password
	 * @param string company_id
	 */
	protected function authViaSSO(string $username, string $password, string $company_id = null) {

		$res = $this->httpClient->get('/auth/open');
		$this->assertEquals(302, $res->getStatusCode());
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');

		//
		$tmp = parse_url($loc);
		$this->assertNotEmpty($tmp['scheme']);
		$this->assertNotEmpty($tmp['host']);
		$sso_origin = sprintf('%s://%s', $tmp['scheme'], $tmp['host']);
		$sso_client = $this->getGuzzleClient([
			'base_uri' => $sso_origin,
		]);

		$tmp = parse_url($loc);

		// echo "\nget1($loc)\n";
		$res = $sso_client->get($loc);
		$this->assertEquals(302, $res->getStatusCode());
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');

		// echo "\nget2($loc)\n";
		// resolve to full URL based on above $loc
		$res = $sso_client->get($loc);
		$this->assertEquals(200, $res->getStatusCode());

		$res_html = $this->assertValidResponse($res, 200, 'text/html');
		// echo "\nres_html:$res_html\n";

		$tmp_csrf = preg_match('/name="CSRF".+?hidden.+?value="([\w\-]+)">/', $res_html, $m) ? $m[1] : '';
		// var_dump($tmp_csrf);

		$res = $sso_client->post($loc, [ 'form_params' => [
			'CSRF' => $tmp_csrf,
			'username' => $username,
			'password' => $password,
			'a' => 'account-open',
		]]);

		$this->assertValidResponse($res, 302, 'text/html');
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');
		// var_dump($loc);

		// echo "\nget3($loc)\n";
		$this->assertMatchesRegularExpression('/auth\/init/', $loc);
		$res = $sso_client->get($loc);
		$res_html = $this->assertValidResponse($res, 300, 'text/html');
		$tmp_csrf = preg_match('/name="CSRF".+?hidden.+?value="([\w\-]+)">/', $res_html, $m) ? $m[1] : '';

		$res = $sso_client->post($loc, [ 'form_params' => [
			'CSRF' => $tmp_csrf,
			'company_id' => $company_id,
		]]);
		$this->assertValidResponse($res, 302, 'text/html');
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');

		// $this->assertNotEmpty($res->getHeaderLine('location'));
		// $loc = $res->getHeaderLine('location');

		$this->assertMatchesRegularExpression('/oauth2\/authorize/', $loc);
		// echo "\nget4($loc)\n";
		$res = $sso_client->get($loc);
		$res_html = $this->assertValidResponse($res, 200, 'text/html');
		$loc = preg_match('/id="oauth2-authorize-permit".+?href="(.+?)".+title="Yes/ms', $res_html, $m) ? $m[1] : '';
		$this->assertNotEmpty($loc);

		// echo "\nget5($loc)\n";
		$res = $sso_client->get($loc);
		$res_html = $this->assertValidResponse($res, 200, 'text/html');
		$loc = preg_match('/id="oauth2-permit-continue".+?href="(.+?)"/ms', $res_html, $m) ? $m[1] : '';
		$this->assertNotEmpty($loc);

		// Should be back to Lab
		// echo "\nget6($loc)\n";
		$res = $this->httpClient->get($loc);
		$this->assertValidResponse($res, 302, 'text/html');
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');
		$this->assertNotEmpty($loc);

		// echo "\nget7($loc)\n";
		$res = $this->httpClient->get($loc);
		$this->assertValidResponse($res, 302, 'text/html');
		$this->assertNotEmpty($res->getHeaderLine('location'));
		$loc = $res->getHeaderLine('location');
		$this->assertNotEmpty($loc);

		// echo "\nget8($loc)\n";
		$res = $this->httpClient->get($loc);
		$this->assertValidResponse($res, 200, 'text/html');

	}


}
