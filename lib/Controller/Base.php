<?php
/**
	Base Controller
*/

namespace OpenTHC\Controller;

class Base
{
	protected $_container;

	/**
		Save the Container
	*/
	function __construct($c)
	{
		$this->_container = $c;
	}

	/**
		Extenders should implement this
	*/
	function __invoke($REQ, $RES, $ARG)
	{
		die("Not Implemented");
	}

	/**
		Parse some JSON Input
	*/
	function parseJSON()
	{
		if ('POST' != $_SERVER['REQUEST_METHOD']) {
			_exit_json(array(
				'meta' => [ 'detail' => 'Invalid Verb [ALU#036]' ]
			), 405);
		}
		$x = strtok($_SERVER['CONTENT_TYPE'], ';');
		if ('application/json' != $x) {

			_exit_json(array(
				'meta' => [
					'detail' => 'Invalid Content Type [ALU#043]',
					'type' => $x,
				]
			), 405);

		}

		$data = file_get_contents('php://input');
		if (empty($data)) {
			_exit_json(array(
				'meta' => [ 'detail' => 'Invalid Input [ALU#017]' ]
			), 400);
		}

		if (!empty($data)) {
			$data = json_decode($data, true);
		}
		if (empty($data)) {
			_exit_json(array(
				'meta' => [ 'detail' => 'Invalid Input [ALU#027]' ]
			), 400);
		}

		return $data;

	}


	/**
	 * Adds a Session Flash Message
	 * @param $data Twig Data Array
	 * @return Twig Data Array w/Flash Messages
	 */
	protected function makeTwigData(array $data = null) : array
	{
		$x = \Edoceo\Radix\Session::flash();
		if (empty($x)) {
			return($data);
		}

		// Rewrite Radix Style to Bootstrap Style
		$x = str_replace('<div class="good">', '<div class="alert alert-success alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="info">', '<div class="alert alert-info alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="warn">', '<div class="alert alert-warning alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="fail">', '<div class="alert alert-danger alert-dismissible" role="alert">', $x);

		// Add Close Button
		$x = str_replace('</div>', '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden=true">&times;</span></button></div>', $x);

		return array_merge([ 'alert' => $x ], $data ?: []);
	}
}
