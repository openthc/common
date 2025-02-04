<?php
/**
 * Helper Class for Test Cases
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test;

class Helper {

	/**
	 * @todo Move to Common
	 */
	static function index_create($note) : void {

		$dt0 = new \DateTime();
		$date = $dt0->format('D Y-m-d H:i:s e');


		$html = <<<HTML
		<html>
		<head>
		<meta charset="utf-8">
		<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
		<meta name="viewport" content="initial-scale=1, user-scalable=yes">
		<meta name="theme-color" content="#069420">
		<style>
		html {
				font-family: sans-serif;
				font-size: 1.5rem;
		}
		</style>
		<title>Test Result ${date}</title>
		</head>
		<body>

		<h1>Test Result ${date}</h1>

		<p>Linting: <a href="phplint.txt">phplint.txt</a></p>
		<p><del>PHPCPD: <a href="phpcpd.txt">phpcpd.txt</a></del></p>
		<p>PHPStan: <a href="phpstan.xml">phpstan.xml</a> and <a href="phpstan.html">phpstan.html</a></p>
		<p>PHPUnit: <a href="phpunit.txt">phpunit.txt</a>, <a href="phpunit.xml">phpunit.xml</a> and <a href="phpunit.html">phpunit.html</a></p>
		<p>Textdox: <a href="testdox.txt">testdox.txt</a>, <a href="testdox.xml">testdox.xml</a> and <a href="testdox.html">testdox.html</a></p>

		$note

		</body>
		</html>
		HTML;

		$file = sprintf('%s/index.html', OPENTHC_TEST_OUTPUT_BASE);
		file_put_contents($file, $html);

	}

	static function output_path_init() : string {

		$p = sprintf('%s/webroot/output/test-report', APP_ROOT);
		if ( ! is_dir($p)) {
			mkdir($p, 0755, true);
		}

		// Empty Directory?
		// $rdi = new \RecursiveDirectoryIterator(OPENTHC_TEST_OUTPUT_BASE, \FilesystemIterator::KEY_AS_PATHNAME);
		// $rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);


		$file_list = glob(sprintf('%s/*', $p));
		foreach ($file_list as $f) {
			unlink($f);
		}

		return $p;

	}

	static function xsl_transform(string $source, string $output) : void
	{
		$x = new \OpenTHC\Test\Helper\XML2HTML($source);
		$x->render($output);
	}

}
