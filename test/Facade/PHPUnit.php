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
		$output_text = ob_get_clean();
		$output_file = sprintf('%s/phpunit.txt', $this->output_path);
		file_put_contents($output_file, $output_text);

		// PHPUnit Transform
		$source_file = sprintf('%s/phpunit.xml', $this->output_path);
		$output_file = sprintf('%s/phpunit.html', $this->output_path);
		\OpenTHC\Test\Helper::xsl_transform($source_file, $output_file);

		$ret = [];
		switch ($res) {
		case 0:
			$ret = [
				'code' => 200,
				'data' => $output_text,
				'meta' => [ 'note' => 'SUCCESS' ]
			];
			break;
		case 1:
			$ret = [
				'code' => 400,
				'data' => $output_text,
				'meta' => [ 'note' => 'FAILURE' ]
			];
			break;
		case 2:
			$ret = [
				'code' => 500,
				'data' => $output_text,
				'meta' => [ 'note' => 'FAILURE (ERRORS)' ]
			];
		default:
			$ret = [
				'code' => 500,
				'data' => $output_text,
				'meta' => [ 'note' => "UNKNOWN ($res)" ]
			];
			break;
		}

		return $ret;
	}

}
