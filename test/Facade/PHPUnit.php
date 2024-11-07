<?php
/**
 * PHPUnit Wrapper
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test\Facade;

class PHPUnit {

	protected $output_path = '';

	protected $config = [];

	/**
	 * Parameters
	 */
	function __construct(array $cfg = [])
	{
		$this->output_path = $cfg['output'];
		unset($cfg['output']);

		$this->config = [];
		$this->config['--configuration'] = sprintf('%s/test/phpunit.xml', APP_ROOT);
		$this->config['--coverage-xml']  = sprintf('%s/coverage', $this->output_path);
		$this->config['--log-junit']     = sprintf('%s/phpunit.xml', $this->output_path);
		$this->config['--testdox-html']  = sprintf('%s/testdox.html', $this->output_path);
		$this->config['--testdox-text']  = sprintf('%s/testdox.txt', $this->output_path);
		$this->config['--testdox-xml']   = sprintf('%s/testdox.xml', $this->output_path);
		foreach ($cfg as $k => $v) {
			if (preg_match('/^\-\-\w/', $k)) {
				$this->config[$k] = $v;
				unset($cfg[$k]);
			}
		}
	}

	/**
	 * Execute the PHPUnit
	 */
	function execute()
	{

		$arg = [];
		$arg[] = 'phpunit';
		foreach ($this->config as $k => $v) {
			$arg[] = $k;
			$arg[] = $v;
		}
		printf("cmd: %s\n", implode(' ', $arg));

		ob_start();
		// ob_start(function($s) {
		//      // Do something w/String $s
		// });

		$cmd = new \PHPUnit\TextUI\Command();
		$res = $cmd->run($arg, false);
		switch ($res) {
		case 0:
			echo "\nTEST SUCCESS\n";
			break;
		case 1:
			echo "\nTEST FAILURE\n";
			break;
		case 2:
			echo "\nTEST FAILURE (ERRORS)\n";
			break;
		default:
			echo "\nTEST UNKNOWN ($res)\n";
			break;
		}
		$output_text = ob_get_clean();

		$output_file = sprintf('%s/phpunit.txt', $this->output_path);

		file_put_contents($output_file, $output_text);

		// PHPUnit Transform
		$source = sprintf('%s/phpunit.xml', $this->output_path);
		$output = sprintf('%s/phpunit.html', $this->output_path);
		\OpenTHC\Test\Helper::xsl_transform($source, $output);

		return $res;
	}

}
