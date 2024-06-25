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

		<p><del>Linting: <a href="phplint.txt">phplint.txt</a></del></p>
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

	static function xsl_transform(string $source, string $output) : void {

		$cmd = [];
		$cmd[] = sprintf('%s/vendor/openthc/common/test/phpunit-xml2html.php', APP_ROOT);
		$cmd[] = escapeshellarg($source);
		$cmd[] = escapeshellarg($output);
		$cmd[] = '2>&1';
		$cmd = implode(' ', $cmd);
		echo "cmd:$cmd\n";
		$res0 = null;
		$res1 = passthru($cmd, $res0);
		// var_dump($res0);
		// var_dump($res1);
	}

}
