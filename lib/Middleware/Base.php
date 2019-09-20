<?php
/**
	Base Middleware
*/

namespace OpenTHC\Middleware;

class Base
{
	protected $_container;

	/**
		Save the container
	*/
	function __construct($c)
	{
		$this->_container = $c;
	}

	/**
		Everyone should implement this
	*/
	function __invoke($REQ, $RES, $NMW)
	{
		return $NMW($REQ, $RES);
	}
}
