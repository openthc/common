<?php
/**
 *
 */

namespace OpenTHC\Test\Core;

class Config_Test extends \OpenTHC\Test\Base
{
	function test_config_file()
	{
		\OpenTHC\Config::init(APP_ROOT);

		$cfg = \OpenTHC\Config::get('database');
		$this->assertIsArray($cfg);

		$cfg = \OpenTHC\Config::get('database/auth');
		$this->assertIsArray($cfg);

		$cfg = \OpenTHC\Config::get('database/auth/hostname');
		$this->assertNotEmpty($cfg);
		// var_dump($cfg);


	}

	function test_openthc_file()
	{
		\OpenTHC\Config::init(APP_ROOT);

		$cfg = \OpenTHC\Config::get('openthc');
		$this->assertNotEmpty($cfg);
		// var_dump($cfg);


		$cfg = \OpenTHC\Config::get('openthc/app');
		$this->assertNotEmpty($cfg);


		$cfg = \OpenTHC\Config::get('openthc/app/hostname');
		$this->assertNotEmpty($cfg);

		// How to Inspect Config?
		$cfg = \OpenTHC\Config::dump();

	}

}
