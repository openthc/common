<?php
/**
 * Base Middleware
 *
 * SPDX-License-Identifier: MIT
*/

namespace OpenTHC\Middleware;

class Base
{
	protected $_container;

	/**
	 * Save the container
	 * @param \Slim\Container $c
	*/
	function __construct(\Slim\Container $c)
	{
		$this->_container = $c;
	}

	/**
	 * Everyone should implement this
	 * @todo low-risk interface
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	*/
	function __invoke($REQ, $RES, $NMW)
	{
		return $NMW($REQ, $RES);
	}
}
