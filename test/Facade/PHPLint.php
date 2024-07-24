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

	function execute() {

		foreach ($this->path_list as $path) {

			$path = sprintf('%s/%s', APP_ROOT, $path);

			if (is_dir($path)) {

				$rdi = new \RecursiveDirectoryIterator($path, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
				$rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);
				foreach ($rii as $k => $v) {
					if (is_dir($k)) {
					} elseif (is_file($k)) {
						$this->_lint($k);
					}
				}
			}

			if (is_file($path)) {
				$this->_lint($path);
			}

		}

	}

	/**
	 *
	 */
	protected function _lint($file) {

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

		$out = null;
		$res = null;

		$buf = exec($cmd, $out, $res);
		if ('No syntax errors detected' == substr($buf, 0, 25)) {
			return 0;
		}

		var_dump($buf);
		var_dump($out);
		var_dump($res);

		return 1;

	}

}
