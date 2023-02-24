<?php
/**
 * Base Module Handler
 * For Slim Groups
 *
 * SPDX-License-Identifier: GPL-3.0-only
*/

namespace OpenTHC\Module;

class Base
{
	protected $_container;

	/**
	 * @param \Slim\Container $c Slim Container
	*/
	function __construct(\Slim\Container $c)
	{
		$this->_container = $c;
	}

	/**
	 * @param \OpenTHC\App $a
	 */
	function __invoke(\OpenTHC\App $a)
	{
	}

}
