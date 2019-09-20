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
}
