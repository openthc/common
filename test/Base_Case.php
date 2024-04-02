<?php
/**
 * Base Class for Test Cases
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test;

// class \OpenTHC\Test\UI_Base_Case extends \OpenTHC\Test\Base_Case {}
// class Base_Case extends \OpenTHC\Test\Base_Case // \PHPUnit\Framework\TestCase

class Base_Case extends \PHPUnit\Framework\TestCase
{
	// Process ID
	protected $_pid = null;


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
	 *
	 */
	function assertValidResponse($res, $want_code=200, $want_type='application/json', $dump=null)
	{
		$this->raw = $res->getBody()->getContents();

		$hrc = $res->getStatusCode();

		if (empty($dump)) {
			if ($want_code != $hrc) {
				$dump = "HTTP $hrc != $want_code";
			}
		}

		if (!empty($dump)) {
			echo "\n<<< $dump <<< $hrc <<<\n{$this->raw}\n###\n";
		}

		$ret = \json_decode($this->raw, true);

		$this->assertEquals($want_code, $res->getStatusCode());
		$type = $res->getHeaderLine('content-type');
		$type = strtok($type, ';');
		$this->assertEquals($want_type, $type);
		if ('application/json' == $want_type) {
				$this->assertIsArray($ret);
		}

		return $ret;

	}

}


