<?php
/**
 * A Big Wrapper for Slim to make Apps "our" way
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class App extends \Slim\App
{
	private $__cfg;

	/**
	 * @todo low-risk interface
	 * @param array (optional) $opt
	*/
	function __construct($opt=null)
	{

		$this->__cfg = array(
			'debug' => (!empty($opt['debug'])),
			'settings' => array(
				'addContentLengthHeader' => false,
				'determineRouteBeforeAppMiddleware' => true,
				'displayErrorDetails' => (!empty($opt['debug'])),
			),
		);

		// Update App Container
		$con = new \Slim\Container($this->__cfg);

		// Not Found Helper
		$con['notFoundHandler'] = function($c) {
			return new class extends \Slim\Handlers\NotFound {

				private $e;

				public function __invoke($request, $response, $e=null)
				{
					$this->e = $e;
					return parent::__invoke($request, $response);
				}

				function renderHtmlNotFoundOutput($request)
				{
					return <<<HTML
					<!DOCTYPE html>
					<html lang="en">
					<head>
					<meta charset="utf-8">
					<meta name="viewport" content="initial-scale=1, user-scalable=yes, width=device-width">
					<meta name="application-name" content="OpenTHC">
					<title>Not Found</title>
					<style>
					body {
						margin:0;
						padding: 4vh 4vw;
						font:1.5rem/1.5 Helvetica,Arial,Verdana,sans-serif;
					}
					h1 {
						margin:0;
						font-size: 3rem;
						font-weight: normal;
						line-height: 3rem;
					}
					</style>
					</head>
					<body>
						<h1>Not Found</h1>
						<p>{$this->e->note}</p>
					</body>
					</html>
					HTML;

					// $html = parent::renderHtmlNotFoundOutput($request);
					// $this->request->getAttribute('note')
					// return $html;<!DOCTYPE html>
				}

				function renderJsonNotFoundOutput()
				{
					return json_encode([
						'data' => 'null',
						'meta' => [ 'note' => $this->e->note ],
					]);
				}

			};

		};

		return parent::__construct($con);

	}

}
