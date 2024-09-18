<?php
/**
 * https://gist.github.com/aczietlow/7c4834f79a7afd920d8f
 * https://github.com/seleniumhq/selenium-google-code-issue-archive/issues/2766
 * https://www.browserstack.com/docs/automate/selenium/getting-started/php/phpunit
 * https://php-webdriver.github.io/php-webdriver/1.4.0/Facebook/WebDriver/Remote/RemoteWebDriver.html
 */

namespace OpenTHC\Test\Unit;

class Docopt_Test extends \OpenTHC\Test\Base {

	/**
	 * @test
	 */
	function test_options()
	{
		$doc = <<<DOC
		TEST SCRIPT

		Usage:
			test <positional-argument> [<optional-command-options>]

		Options:
			--company=<COMPANY>   The Company ID
			--license=<LICENSE>   The License ID
			--source=<FILE>       Source File List
			--object=<TYPE>       Object Type
		DOC;

		$res = \Docopt::handle($doc, [
			'exit' => false,
			'help' => true,
			'argv' => [
				'--company',
				'ULID',
			],
		]);

		$this->assertNotEmpty($res);
		$this->assertObjectHasProperty('args', $res);

	}

	function test_command_then_options()
	{
		$doc = <<<DOC
		TEST SCRIPT

		Usage:
			test <command> <command-options>
		DOC;

		$arg = \OpenTHC\Docopt::parse($doc, [
			'command',
			'--quiet',
		]);
		var_dump($arg);

		$this->assertIsArray($arg);
		$this->assertNotEmpty($arg);
		$this->assertArrayHasKey('<command>', $arg);
		$this->assertArrayHasKey('<command-options>', $arg);

	}

	function test_option_short_and_long()
	{
		$doc = <<<DOC
		TEST SCRIPT

		Usage:
			test [options] <command>

		Options:
			-h --help     Help
			-V --version  Version Information
			-s --short    Short Options
		DOC;

		$arg = \OpenTHC\Docopt::parse($doc);
		// $this->assertNotEmpty($arg);

		$arg = \OpenTHC\Docopt::parse($doc, [
			'-h',
		]);

		$arg = \OpenTHC\Docopt::parse($doc, [
			'--help',
		]);

		$arg = \OpenTHC\Docopt::parse($doc, [
			'-s',
		]);

		$arg = \OpenTHC\Docopt::parse($doc, [
			'--short',
		]);

		$arg = \OpenTHC\Docopt::parse($doc, [
			'-V',
		]);

		$arg = \OpenTHC\Docopt::parse($doc, [
			'--version',
		]);


		$this->assertNotEmpty($arg);
	}

	/**
	 *
	 */
	function test_facade()
	{
		$doc = <<<DOC
		TEST SCRIPT
		Usage:
			test [optional] <POSTITION> <command-options>

		Options:
			--company=<COMPANY>   The Company ID
			--license=<LICENSE>   The License ID
			--source=<FILE>       Source File List
			--object=<TYPE>       Object Type
		DOC;

		$res = \OpenTHC\Docopt::parse($doc);

		$this->assertNotEmpty($res);
	}
}
