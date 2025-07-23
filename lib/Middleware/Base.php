<?php
/**
 * Base Middleware
 *
 * SPDX-License-Identifier: MIT
*/

namespace OpenTHC\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Base
{
	protected $_container;

	/**
	 * Everyone should implement this
	 * @todo low-risk interface
	 * @param Psr\Http\Message\ServerRequestInterface $REQ
	 * @param Psr\Http\Message\ResponseInterface $RES
	 * @param array $ARG
	*/
	function __invoke(Request $REQ, Response $RES, ?array $NMW = null)
	{
		return $NMW($REQ, $RES);
	}
}
