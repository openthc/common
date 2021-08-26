<?php
/**
 * Error Handling and Wrappers
 */

/**
 * Exit with our Error Page
 * @param $err is the error data array
 */
function _error_handler_exit($err=null)
{
	static $done = false;

	if ($done) {
		exit(0);
	}

	$done = true;

	$hint = null;
	if (!empty($_ENV['_error_handler']['hint'])) {
		$hint = $_ENV['_error_handler']['hint'];
	}
	if (!empty($err['hint'])) {
		$hint = $err['hint'];
		unset($err['hint']);
	}

	$body = <<<HTML
<h1>System Error</h1>
<p>The system encountered a very unexpected error.</p>
<p>Details from the request have been logged and some humans have been notified.</p>
$hint
<pre>{$err['text']}</pre>
HTML;

	_exit_500($body, 500);

	exit(0);
}

/**
 * @param $e An Initial Error Value, like returned from error_get_last at the start for trapping interpreter errors
 */
function _error_handler_init($cfg=null)
{
	$_ENV['_error_handler'] = $cfg;

	// Check Input Errors
	// if (!empty($e)) {
	// 	_error_handler_trap($e['type'], $e['message'], $e['file'], $e['line']);
	// 	// App::fail($e['type'], $e['message'], $e['file'], $e['line']);
	// }

	// Install Error Handler
	set_error_handler('_error_handler_trap');
	set_exception_handler('_error_handler_trap');

	// Maybe this is dumb?
	// register_shutdown_function(function() {
	// 	$e1 = error_get_last();
	// 	if (!empty($e1)) {
	// 		_error_handler_trap($e1['type'], $e1['message'], $e1['file'], $e1['line']);
	// 	}
	// });

}

/**
 * Actually Trap the Error and to something
 */
function _error_handler_trap($ex, $etext=null, $efile=null, $eline=null, $edata=null)
{
	// Ignore errors based on set level
	if (is_numeric($ex)) {
		$el = error_reporting();
		if (0 == ($el & $ex)) {
			return(false);
		}
	}

	// Count Stat
	_stat_counter('app.err', 1);

	// Dump & Format
	$err = _error_handler_trap_dump($ex, $etext, $efile, $eline, $edata);

	// Log Local
	error_log($err['name']);

	// Log HTTP
	$err_json = json_encode($err);
	$req = _curl_init('https://cic.openthc.com/api/v2018/bug');
	curl_setopt($req, CURLOPT_POST, true);
	curl_setopt($req, CURLOPT_POSTFIELDS, $err_json);
	curl_setopt($req, CURLOPT_HTTPHEADER, [
		'authorization: bearer ',
		'content-type: application/json',
	]);
	$xxx = curl_exec($req);

	_error_handler_exit($err);

}

/**
 * From the Handler, Dump Error and Return a formatted ERR data-array
 * @param ... the ones that you pass to regular handler
 * @return data-array
 */
function _error_handler_trap_dump($ex, $etext=null, $efile=null, $eline=null, $edata=null)
{
	$ret = [
		'name' => '-unknown-',
		'type' => 'Error',
		'code' => 0,
		'text' => '',
		'file' => '-unknown-',
		'line' => '0',
		'dump' => '',
	];

	// An Error
	if (is_numeric($ex)) {
		$ret['code'] = $ex;
		$ret['file'] = $efile;
		$ret['line'] = $eline;
		$ret['text'] = $etext;
		$ret['dump'] = debug_backtrace(0, 32);
	} elseif (is_object($ex)) { // Exception
		$ret['type'] = 'Exception';
		$ret['code'] = $ex->getCode();
		$ret['file'] = $ex->getFile();
		$ret['line'] = $ex->getLine();
		$ret['text'] = $ex->getMessage();
		$ret['dump'] = $ex->getTraceAsString();
	} else {
		$ret['text'] = 'Unknown Error Type';
		$ret['dump'] = serialize($ex);
	}

	$ret['_GET'] = $_GET;
	$ret['_POST'] = $_POST;
	$ret['_SESSION'] = $_SESSION;
	$ret['_SERVER'] = $_SERVER;
	$ret['_ENV'] = $_ENV;

	// Trap Existing Output Buffer
	$obuf = '';
	while (ob_get_level() > 0) {
		$obuf.= ob_get_clean();
	}

	if (strlen($obuf)) {
		$ret['_OUTPUT'] = $obuf;
	}

	$ret['name'] = sprintf('%s: %s:"%s" @ %s#%d', $ret['type'], $ret['code'], $ret['text'], $ret['file'], $ret['line']);

	$file = sprintf('/tmp/err-%s.dump', $_SERVER['UNIQUE_ID']);
	$dump = json_encode($ret, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE, 8);
	file_put_contents($file, $dump);

	return $ret;

}
