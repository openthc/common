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
	function getGuzzleClient(string $origin)
	{
		$c = new \GuzzleHttp\Client(array(
			'base_uri' => $origin,
			'allow_redirects' => false,
			'debug' => $_ENV['debug-http'],
			'request.options' => array(
				'exceptions' => false,
			),
			'http_errors' => false,
			'cookies' => true,
		));

		return $c;
	}

	/**
	 *Intends to become an assert wrapper for a bunch of common response checks
	 * @param $res, Response Object
	 * @param int $code_expect=200 the status code desired
	 * @param $type_expect=application/json the mime type desired
	 * @return string body
	 */
	function assertValidResponse($res, $code_expect=200, $type_expect=null, $dump=null)
	{
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

		$this->raw = $res->getBody()->getContents();

		$code_actual = $res->getStatusCode();

		$type_actual = $res->getHeaderLine('content-type');
		$type_actual = strtok($type_actual, ';');
		$type_actual = strtolower($type_actual);

		if ($code_expect != $code_actual) {
			$dump = "HTTP $code_expect != $code_actual";
		}
		if ($type_expect != $type_actual) {
			$dump = "MIME $type_expect != $type_actual";
		}

		if ( ! empty($dump)) {
			echo "\n<<< $dump <<< $code_actual <<<\n{$this->raw}\n###\n";
		}

		$this->assertEquals($code_expect, $res->getStatusCode());
		$this->assertEquals($type_expect, $type_actual);

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

}
