<?php
/**
 * Response Factory
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC\HTTP\Response;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Factory implements ResponseFactoryInterface
{
	public function createResponse(int $code = 200, string $reasonPhrase = '') : ResponseInterface
	{
		return new \OpenTHC\HTTP\Response($code, [], null, '1.1', $reasonPhrase);
	}
}
