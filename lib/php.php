<?php
/**
 * Top Level PHP Functions, utility wrappers
 *
 * SPDX-License-Identifier: MIT
 */

function h($x)
{
	return htmlspecialchars($x, ENT_COMPAT|ENT_HTML5, 'utf-8', true);
	// return htmlentities($x, ENT_QUOTES, 'UTF-8', true);
}

// @deprecated use lib-sodium or some helper
function base64_encode_url($x) {
	return str_replace(['+','/','='], ['-','_',''], base64_encode($x));
}

// @deprecated use lib-sodium or some helper
function base64_decode_url($x) {
	return base64_decode(str_replace(['-','_'], ['+','/'], $x));
}


function _curl_init($uri)
{
	$ch = curl_init($uri);

	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

	// Booleans
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIESESSION, false);
	curl_setopt($ch, CURLOPT_CRLF, false);
	curl_setopt($ch, CURLOPT_FAILONERROR, false);
	curl_setopt($ch, CURLOPT_FILETIME, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_NETRC, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT,true);

	// curl_setopt($ch, CURLOPT_BUFFERSIZE, 16384);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 240);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	// curl_setopt($ch, CURLOPT_SSLVERSION, 3); // 2, 3 or GnuTLS
	curl_setopt($ch, CURLOPT_TIMEOUT, 600);

	curl_setopt($ch, CURLOPT_USERAGENT, 'OpenTHC/420.20.196');

	return $ch;
}


/**
 * POST JSON to URL
 */
function _curl_post_json(string $url, $body, array $head1=[])
{
	if ( ! is_string($body)) {
		$body = json_encode($body);
	}

	$req = _curl_init($url);
	// curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 4); // Allowed Connect Time (s)
	// curl_setopt($req, CURLOPT_CONNECTTIMEOUT_MS, 4000); // Allowed Connect Time (ms)
	curl_setopt($req, CURLOPT_TIMEOUT, 4); // Allowed Total Time (s)
	// curl_setopt($req, CURLOPT_TIMEOUT_MS, 8000); // Allowed Total Time (ms)

	// POST JSON
	curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($req, CURLOPT_POSTFIELDS, $body);

	// Headers
	$head0 = [
		'accept' => 'application/json',
		'content-type' => 'application/json'
	];
	$head1 = array_change_key_case($head1, CASE_LOWER);
	$head2 = array_merge($head0, $head1);
	$head3 = [];
	foreach ($head2 as $k=>$v) {
		$head3[] = sprintf('%s: %s', $k, $v);
	}

	curl_setopt($req, CURLOPT_HTTPHEADER, $head3);

	$res = curl_exec($req);
	$res = json_decode($res);

	return $res;

}

/**
	Date Format
	@param $f Date Format
	@param $d Date/Time
	@param $tz Time Zone
*/
function _date($f, $d=null, $tz=null)
{
	$r = $d;

	if (empty($d) && empty($tz)) {
		return '-';
	}

	if (empty($tz)) {
		$tz = $_SESSION['tz'];
	}


	if (!empty($r)) {
		// Match UNIX Timestamp (may be negative)
		if (preg_match('/^\-?\d+$/', $r)) {
			$r = '@' . $r;
		}
	}

	if (!empty($tz)) {
		if (is_string($tz)) {
			$tz = new DateTimeZone($tz);
		}
	}

	if (empty($tz)) {
		$tz = new DateTimeZone('UTC');
	}

	try {

		$dt = new DateTime($r); //, $tz);

		if (!empty($tz)) {
			$dt->setTimezone($tz);
		}

	} catch (\Exception $e) {
		return $r;
	}

	// A strftime Type Format
	if (strpos($f, '%') === false) {
		$r = $dt->format($f);
	} else {
		$tz0 = date_default_timezone_get();
		if ($tz) {
			date_default_timezone_set($tz->getName());
		}
		$r = strftime($f, $dt->getTimestamp());
		date_default_timezone_set($tz0);
	}

	return $r;

}


/**
*/
function _exit_html($html, $code=200)
{
	while (ob_get_level()) ob_end_clean();

	_http_code($code);

	header('cache-control: no-store, max-age=0');
	header('content-type: text/html; charset=utf-8');

	echo $html;

	exit(0);

}

function _exit_html_fail($body, $code=500, $opt0=null)
{
	if (empty($opt0)) {
		$opt0 = [];
	}

	$opt1 = [
		'title' => 'System Failure',
		'border-color' => 'red',
	];

	$opt2 = array_merge($opt0, $opt1);

	_exit_html(_exit_html_wrap($body, $code, $opt2), $code);

}

function _exit_html_warn($body, $code=400, $opt0=null)
{
	if (empty($opt0)) {
		$opt0 = [];
	}

	$opt1 = [
		'title' => 'System Warning',
		'border-color' => 'orange',
	];

	$opt2 = array_merge($opt0, $opt1);

	_exit_html(_exit_html_wrap($body, $code, $opt2), $code);

}

function _exit_html_wrap($body, $code=400, $opts=null)
{
	if (empty($opts)) {
		$opts = [];
	}

	if (empty($opts['title'])) {
		$opts['title'] = 'OpenTHC';
	}

	if (empty($opts['border-color'])) {
		$opts['border-color'] = 'red';
	}

	$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="theme-color" content="#069420">
<style>
:root {
	--rd: red;
	--gn: #069420;
	--og: #ff9900;
	--wt: #fdfdfd;
}
* {
	box-sizing: border-box;
}
body {
	background: #202020;
	border-left: 4vw solid {$opts['border-color']};
	border-right: 4vw solid {$opts['border-color']};
	color: var(--wt);
	font-family: sans-serif;
	font-size: 1.25em;
	margin: 0;
	min-height: 100vh;
	padding: 2vh 2vw;
	width: 100%;
}
a {
	/* border: 1px solid ; */
	border-radius: 0.25rem;
	color: var(--gn);
	cursor: pointer;
	line-height: 1.5;
	padding: 0 0.25rem;
	text-decoration: none;
	vertical-align: middle;
	/*
	text-align: center;
	user-select: none;
	min-width: 10rem; */
}
a:hover {
	background: var(--gn);
	color: var(--wt);
}
footer {
	border-top: 0.125vh solid {$opts['border-color']};
	font-family: monospace;
	margin: 10vh 2vw 0 2vw;
	padding: 0.25em;
}
h1, h2, p, pre {
	margin: 0 0 1rem 0;
	padding: 0;
}
main {
	min-height: 70vh;
}
pre {
	background: #f0f0f0;
	color: #101010;
	padding: 0.50rem;
	white-space: break-spaces;
}
svg {
	margin: 0;
	overflow: hidden;
	padding: 0;
	vertical-align: middle;
}
.svg-icon {
	max-width: 1rem;
}
div.link-list {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	justify-content: space-around;
	margin-top: 4rem;
}
</style>
<title>{$opts['title']}</title>
</head>
<body>
<main>
$body
</main>
<footer>Request Log: {$_SERVER['UNIQUE_ID']}</footer>
</body>
</html>
HTML;
	return $html;
}

/**
*/
function _exit_json($data, $code=200)
{
	while (ob_get_level()) ob_end_clean();

	_http_code($code);

	if ( ! is_string($data)) {
		$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	header('cache-control: no-store, max-age=0');
	header('content-type: application/json');

	echo $data;

	exit(0);
}


/**
*/
function _exit_text($text, $code=200)
{
	while (ob_get_level()) ob_end_clean();

	_http_code($code);

	header('cache-control: no-store, max-age=0');
	header('content-type: text/plain; charset=utf-8');

	if (!is_string($text)) {
		$text = json_encode($text, JSON_PRETTY_PRINT);
	}

	echo $text;

	exit(0);
}

/**
	Sets the HTTP Header Code
*/
function _http_code($code)
{
	$map_code = array(
		200 => 'OK',
		201 => 'Created',
		204 => 'No Content',
		304 => 'Not Modified',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		409 => 'Conflict',
		410 => 'Gone',
		500 => 'Server Error',
		503 => 'Unavailable',
		504 => 'Gateway Timeout',
	);
	$text = $map_code[$code];
	$head = trim(sprintf('HTTP/1.1 %d %s', $code, $text));

	header($head, true, $code);
	// header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);
	// header($_SERVER['SERVER_PROTOCOL'] . ' 403 Not Authorized', true, 403);
}

/**
 * @return true if in CLI mode
 */
function is_cli()
{
	return defined('STDIN')
		|| php_sapi_name() === 'cli'
		|| (stristr(PHP_SAPI, 'cgi') && getenv('TERM'))
		|| (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0);
}

/**
	Array Diff by Key and Value - Recursive
	@param $a0 Old Data Array
	@param $a1 New Data Array
	@return Array of Keys with Old and New values
*/
function _array_diff_keyval_r($a0, $a1)
{
	$ret = array();

	if (empty($a0)) {
		$a0 = array();
	}

	if (empty($a1)) {
		$a1 = array();
	}

	$key_a = array_keys($a0);
	$key_b = array_keys($a1);
	$key_list = array_merge($key_a, $key_b);

	foreach ($key_list as $key) {

		$v0 = $a0[$key];
		$v1 = $a1[$key];

		if ($v0 != $v1) {

			if (is_array($v0) && is_array($v1)) {
				$x = _array_diff_keyval_r($v0, $v1);
				$ret[$key] = $x;
			} else {
				$ret[$key] = array(
					'old' => $v0,
					'new' => $v1,
				);
			}
		}
	}

	return $ret;
}

/**
 * Read a File, Parse YAML Header
 */
function _content_read($f)
{
	$data = [
		'head' => [],
		'body' => null,
	];

	if ( ! is_file($f)) {
		return $data;
	}

	$text = file_get_contents($f);
	if (preg_match('/^---\n(.+)\n---\n(.+)/ms', $text, $m)) {
		$data['head'] = yaml_parse($m[1]);
		$data['body'] = trim($m[2]);
	} else {
		$data['body'] = trim($text);
	}

	if (empty($data['head']['updated_at'])) {
		if ( ! empty($data['head']['date'])) {
			$data['head']['updated_at'] = $data['head']['date'];
		}
	}

	if (empty($data['head']['updated_at'])) {
		$t0 = filemtime($f);
		$data['head']['updated_at'] = date(\DateTimeInterface::RFC3339, $t0);
	}

	return $data;

}


/**
	Sort a Keyed Array, Recursively
	@return bool
*/
function _ksort_r(&$array)
{
	foreach ($array as &$value) {
		if (is_array($value)) {
			_ksort_r($value);
		}
	}

	return ksort($array);
}


/**
 * @deprecated use _markdown_ex
 */
function _markdown($x)
{
	static $PD;

	if (empty($PD)) {
		$PD = new Parsedown();
	}

	return $PD->text($x);

}

/**
 * Turns Markdown Text into HTML
 */
function _markdown_ex($t)
{
	static $p;
	if (empty($p)) {
		$p = new ParsedownExtra();
	}

	return $p->text($t);

}


/**
 * Generates Stub type Text
 */
function _text_stub($x)
{
	$x = strtolower($x);
	$x = preg_replace('/[^\w\-]+/', '-', $x);
	$x = preg_replace('/\-+/', '-', $x);
	$x = trim($x, '-');
	return $x;
}


/**
	Extended _parse_str()
*/
function _parse_str($x)
{
	$r = null;
	parse_str($x, $r);
	return $r;
}


// Format Phone
function _phone_e164($p, $l='US')
{
	$p = trim($p);
	if (empty($p)) {
		return null;
	}

	try {
		$pnu = \libphonenumber\PhoneNumberUtil::getInstance();
		$r = $pnu->parse($p, $l);
		$r = $pnu->format($r, \libphonenumber\PhoneNumberFormat::E164);
		return $r;
	} catch (Exception $e) {
		return $p;
	}
}

// Format Phone
function _phone_nice($p, $l='US')
{
	$p = trim($p);
	if (empty($p)) {
		return $p;
	}

	try {
		$pnu = \libphonenumber\PhoneNumberUtil::getInstance();
		$r = $pnu->parse($p, $l);
		$r = $pnu->format($r, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
		return $r;
	} catch (Exception $e) {
		return $p;
	}
}



/**
 * Generate a good Random Hash
 * @return string base64-url encoded sha256 of 256 random bytes
 */
function _random_hash()
{
	return base64_encode_url(hash('sha256', random_bytes(256), true));
}


/*
 * Returns a File that will magically be cleaned up
 */
function _tmp_file($ext='tmp')
{
	// File Name
	$fn = sprintf('%s/%s.%s', sys_get_temp_dir(), \Edoceo\Radix\ULID::generate(), $ext);

	// Supress Errors
	$er = error_reporting(0);

	// Exclusve!
	$fh = fopen($fn, 'x');
	if (empty($fh)) {
		return null;
	}
	fclose($fh);

	error_reporting($er);

	// Auto Cleanup!
	register_shutdown_function(function($x) {
		if (is_file($x)) {
			unlink($x);
		}
	}, $fn);

	return $fn;
}


/**
	Assemble a split URL
*/
function _url_assemble($uri)
{
	$sc = isset($uri['scheme'])   ? $uri['scheme'] . '://' : null;
	$ho = isset($uri['host'])     ? $uri['host']           : null;
	$po = isset($uri['port'])     ? ':' . $uri['port']     : null;
	$un = isset($uri['user'])     ? $uri['user']           : null;
	$pw = isset($uri['pass'])     ? ':' . $uri['pass']     : null;
	$pw = ($un || $pw)            ? "$pw@"                 : null;
	$pa = isset($uri['path'])     ? $uri['path']           : null;
	$qs = isset($uri['query'])    ? '?' . $uri['query']    : null;
	$fr = isset($uri['fragment']) ? '#' . $uri['fragment'] : null;
	return $sc . $un . $pw . $ho . $po . $pa . $qs . $fr;
}


/**
	Cheap Hacks for Encrypt/Decrypt
	@param $x Is a String to Encrypt or Decrypt
*/
function _encrypt($d, $k=null)
{
	if (null == $k) {
		$k = APP_SALT;
	}
	$d = openssl_encrypt($d, 'AES-256-ECB', $k, true);
	return base64_encode_url($d);
}

function _decrypt($d, $k=null)
{
	if (null == $k) {
		$k = APP_SALT;
	}
	$d = base64_decode_url($d);
	return trim(openssl_decrypt($d, 'AES-256-ECB', $k, true));
}


/*
	Twig Wrapper
*/
function _twig($file, $data=null)
{
	$path = dirname($file);
	$base = basename($file);

	//if ('.' == $path) {
	// $path = sprintf('%s/twig', APP_ROOT);
	//}

	$tlf = new \Twig\Loader\FilesystemLoader(array(
		$path,
		sprintf('%s/twig', APP_ROOT)
	));
	$cfg = array(
		'strict_variables' => false,
		// 'debug' => true,
		'cache' => '/tmp/twig',
	);
	$twig = new \Twig\Environment($tlf, $cfg);
	//$twig->addFilter(new \Twig\TwigFilter('base64', function($x) {
	//      return chunk_split(base64_encode($x), 72);
	//}));

	if (empty($data)) {
		$data = [];
	}

	$html = $twig->render($base, $data);

	return $html;
}

function _ulid($tms=null)
{
	$code_set = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
	$code_len = 32;

	if (empty($tms)) {
		$tms = microtime(true);
		$tms = floor($tms * 1000);
	}

	$ret = array();

	// Time Segment
	for ($idx = 10; $idx > 0; $idx--) {

		$mod = $tms % $code_len;
		$chr = substr($code_set, $mod, 1);

		array_unshift($ret, $chr);
		$tms = ($tms - $mod) / $code_len;
	}

	// Random Segment
	for ($idx=0; $idx < 16; $idx++) {

		$rnd0 = mt_rand() / mt_getrandmax();
		$rnd1 = floor($code_len * $rnd0);
		$chr = substr($code_set, $rnd1, 1);

		$ret[] = $chr;

	}

	return implode('', $ret);

}
