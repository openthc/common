<?php
/**
 * PSR7 Reponse w/Features
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\HTTP;

class Response extends \Slim\Http\Response
{
	private $_attr = [];

	/**
	 * Constructor
	 */
	function __construct($c=200, $h=null)
	{
		$h = new \Slim\Http\Headers([
			'content-type' => 'text/html; charset=utf-8'
		]);
		parent::__construct($c, $h);
	}

	/**
	 *
	 */
	function getAttribute(string $key)
	{
		return $this->_attr[$key];
	}

	/**
	 *
	 */
	function withAttribute(string $key, $val)
	{
		$obj1 = clone $this;
		$obj1->_attr[ $key ] = $val;
		return $obj1;
	}

	/**
	 * Update JSON
	 */
	function withJSON($data, $code=200, $flag=null)
	{
		$flag = intval($flag);
		$flag = ($flag | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		return parent::withJSON($data, $code, $flag);
	}

}
