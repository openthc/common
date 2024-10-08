<?php
/**
 * PHPLint Wrapper
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test\Facade;

class PHPLint {

	protected $path_list;

	/**
	 * Parameters
	 */
	function __construct(array $cfg0 = []) {

		$this->path_list = [
			'boot.php',
			'make.php',
			'test.php',
			'bin',
			'block',
			'content',
			'controller',
			'Custom',
			'lib',
			'sbin',
			'test',
			'theme',
			'view',
			'webroot'
		];

	}

	function execute() : int {

		$ret = 0;

		foreach ($this->path_list as $path) {

			$path = sprintf('%s/%s', APP_ROOT, $path);

			if (is_dir($path)) {

				$rdi = new \RecursiveDirectoryIterator($path, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
				$rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);
				foreach ($rii as $k => $v) {
					if (is_dir($k)) {
					} elseif (is_file($k)) {
						$res = $this->_lint($k);
						$ret = max($ret, $res);
					}
				}
			}

			if (is_file($path)) {
				$res = $this->_lint($path);
				$ret = max($ret, $res);
			}

		}

		return $ret;

	}

	/**
	 *
	 */
	protected function _lint($file) : int {

		if ('.php' != substr($file, -4)) {
			return 0;
		}

		// Recursive Iterator
		$cmd = [];
		$cmd[] = 'php';
		$cmd[] = '-l';
		$cmd[] = escapeshellarg($file);
		$cmd[] = '2>&1';

		$cmd = implode(' ', $cmd);
		// var_dump($cmd);

		$out = '';
		$ret = 0;

		$buf = exec($cmd, $out, $ret);
		if ((0 === $ret) && ('No syntax errors detected' == substr($buf, 0, 25))) {
			return $ret;
		}

		// var_dump($buf);
		// var_dump($out);
		// var_dump($res);

		echo "Lint: $file\n";
		echo implode("\n", $out);
		echo "\n";

		return $ret;

	}

}
