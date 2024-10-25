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

	// /**
	//  * Save the container
	//  * @param \Slim\Container $c
	// */
	// function __construct(\Psr\Http\Message\ServerRequestInterface $REQ, \Psr\Http\Message\ResponseInterface $RES)
	// {
	// 	// $this->_container = $c;
	// }

	/**
	 * Everyone should implement this
	 * @todo low-risk interface
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	*/
	function __invoke(\Psr\Http\Message\ServerRequestInterface $REQ, \Psr\Http\Message\ResponseInterface $RES, ?array $NMW = null)
	{
		return $NMW($REQ, $RES);
	}
}
