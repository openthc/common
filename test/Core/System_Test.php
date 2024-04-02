<?php
/**
 * Test System
 */

namespace OpenTHC\Test\Core;

class System_Test extends \OpenTHC\Test\Base {

	function test_env() {

		\OpenTHC\Config::init(APP_ROOT);
		$this->assertNotEmpty( \OpenTHC\Config::get('redis/url') );
		$this->assertNotEmpty( \OpenTHC\Config::get('statsd/url') );

	}

	function x_test_system() {


	}

}
