<?php
/**
 * PHPUnit Wrapper
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test\Facade;

class PHPUnit {

	protected $output_path = '';

	/**
	 * Parameters
	 */
	function __construct(array $cfg0 = []) {

		$this->output_path = $cfg0['output'];
		unset($cfg0['output']);

		$cfg1['--configuration'] = sprintf('%s/test/phpunit.xml', APP_ROOT);
		$cfg1['--coverage-xml']  = sprintf('%s/coverage', $this->output_path);
		$cfg1['--log-junit']     = sprintf('%s/phpunit.xml', $this->output_path);
		$cfg1['--testdox-html']  = sprintf('%s/testdox.html', $this->output_path);
		$cfg1['--testdox-text']  = sprintf('%s/testdox.txt', $this->output_path);
		$cfg1['--testdox-xml']   = sprintf('%s/testdox.xml', $this->output_path);
		foreach ($cfg0 as $k => $v) {
			if (preg_match('/^\-\-\w/', $k)) {
				$cfg1[$k] = $v;
				unset($cfg0[$k]);
			}
		}

		$arg = [];
		$arg[] = 'phpunit';
		foreach ($arg as $k => $v) {
			$arg[] = $k;
			$arg[] = $v;
		}

		ob_start();
		// ob_start(function($s) {
		//      // Do something w/String
		// });

		$cmd = new \PHPUnit\TextUI\Command();
		$res = $cmd->run($arg, false);

		$output_text = ob_get_clean();
		$output_file = sprintf('%s/phpunit.txt', $this->output_path);

		file_put_contents($output_file, $output_text);

	}

}
