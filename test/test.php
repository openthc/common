#!/usr/bin/env php
<?php
/**
 * OpenTHC Common Test Runner
 */

require_once(dirname(__DIR__) . '/boot.php');

// Command Line
$doc = <<<DOC
OpenTHC Test

Usage:
	test <command> [options]
	test phpunit
	test phpstan
	test phplint

Options:
	--filter=<FILTER>   Some Filter for PHPUnit
DOC;

$res = Docopt::handle($doc, [
	'exit' => false,
	'optionsFirst' => true,
]);
$cli_args = $res->args;
var_dump($cli_args);


// Test Config
$cfg = [];
$cfg['base'] = APP_ROOT;
$cfg['site'] = '';

$test_helper = new \OpenTHC\Test\Helper($cfg);
$cfg['output'] = $test_helper->output_path;

// Call Linter?
$tc = new \OpenTHC\Test\Facade\PHPLint($cfg);
$res = $tc->execute();
var_dump($res);


// Call PHPCS?
// \OpenTHC\Test\Facade\PHPCS::execute();


// PHPStan
$tc = new OpenTHC\Test\Facade\PHPStan($cfg);
$res = $tc->execute();
var_dump($res);


// Psalm/Psalter?
// $tc = new OpenTHC\Test\Facade\Psalm($cfg);
// $res = $tc->execute();
// var_dump($res);


// PHPUnit
// Filter?
if ( ! empty($cli_args['--filter'])) {
	$cfg['--filter'] = $cli_args['--filter'];
}
$tc = new OpenTHC\Test\Facade\PHPUnit($cfg);
$res = $tc->execute();
var_dump($res);


// Done
// \OpenTHC\Test\Helper::index_create($res['data']);


// Output Information
// $origin = \OpenTHC\Config::get('openthc/www/origin');
// $output = str_replace(sprintf('%s/webroot/', APP_ROOT), '', $cfg['output']);

// echo "TEST COMPLETE\n  $origin/$output\n";
