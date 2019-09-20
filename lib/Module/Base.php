<?php
/**
	Base Module Handler
	For Slim Groups
*/

namespace OpenTHC\Module;

class Base
{
	protected $_container;

	/**
		@param $c Slim Container
	*/
	function __construct($c)
	{
		$this->_container = $c;
	}

	function __invoke($a)
	{
	}

}
