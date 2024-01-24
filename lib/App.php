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

		// Default Twig Directory, or Specific Option?
		$this->__cfg['twig'] = APP_ROOT . '/twig';
		if (!empty($opt['twig'])) {
			$this->__cfg['twig'] = $opt['twig'];
		}
		if (is_dir($this->__cfg['twig'])) {
			$con = $this->addTwig($con);
		}

		//
		$con['notFoundHandler'] = function($c) {
			return function ($REQ, $RES) {
				return $RES->withJSON([
					'data' => null,
					'meta' => [ 'note' => 'Not Found [CLA-037]' ]
				], 404);
			};
		};

		return parent::__construct($con);

	}

	function addTwig($con)
	{
		// Load Slim View
		$con['view'] = function($c0) {

			// Add Common to Twig Path
			$path = array(
				$this->__cfg['twig'],
				sprintf('%s/twig', dirname(dirname(__FILE__))),
			);

			$args = array(
				//'cache' => '/tmp',
				'debug' => $this->__cfg['debug'],
			);

			$view = new \Slim\Views\Twig($path, $args);

			if ($this->__cfg['debug']) {
				$view->addExtension(new \Twig\Extension\DebugExtension());
			}

			// Base64 Filter (for Email)
			$tfb = new \Twig\TwigFilter('base64', function($x) {
				return chunk_split(base64_encode($x), 72);
			});
			$view->getEnvironment()->addFilter($tfb);

			// Markdown Filter
			$tfm = new \Twig\TwigFilter('markdown', function($x) {
				return _markdown($x);
			}, array('is_safe' => array('html')));

			$view->getEnvironment()->addFilter($tfm);

			return $view;

		};

		return $con;

	}

}
