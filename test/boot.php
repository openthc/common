<?php
/**
 * Test Bootstrap
 */

define('APP_ROOT', dirname(__DIR__));

require_once(APP_ROOT . '/vendor/autoload.php');

error_reporting(E_ERROR | E_PARSE);

if ( ! \OpenTHC\Config::init(APP_ROOT) ) {
	throw new \Exception('Invalid Application Configuration [ALB-035]', 500);
}
