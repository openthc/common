<?php
/**
 * Top Level PHP Functions, utility wrappers
 */

function h($x)
{
	return htmlspecialchars($x, ENT_COMPAT|ENT_HTML5, 'utf-8', true);
}

function base64_encode_url($x) {
	return str_replace(['+','/','='], ['-','_',''], base64_encode($x));
}

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

	curl_setopt($ch, CURLOPT_USERAGENT, 'OpenTHC/420.18.201');

	return $ch;
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
			date_default_timezone_set($tz);
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

	header('Cache-Control: no-cache');
	header('Content-Type: text/html; charset=utf-8');

	echo $html;

	exit(0);

}


/**
*/
function _exit_json($data, $code=200)
{
	while (ob_get_level()) ob_end_clean();

	_http_code($code);

	if (!is_string($data)) {
		$data = json_encode($data, JSON_PRETTY_PRINT);
	}

	header('Cache-Control: no-cache');
	header('Content-Type: application/json; charset=utf-8');

	echo $data;

	exit(0);
}


/**
*/
function _exit_text($text, $code=200)
{
	while (ob_get_level()) ob_end_clean();

	_http_code($code);

	header('Cache-Control: no-cache');
	header('Content-Type: text/plain; charset=utf-8');

	if (!is_string($text)) {
		$text = json_encode($text, JSON_PRETTY_PRINT);
	}

	echo $text;

	exit(0);
}

/**
	Exit with a 403
*/
function _exit_403($text='Not Authorized')
{
	_exit_text($text, 403);
}

/**
	Exit with a 404
*/
function _exit_404($text='Not Found')
{
	_exit_text($text, 404);
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


function _markdown($x)
{
	static $PD;

	if (empty($PD)) {
		$PD = new Parsedown();
	}

	return $PD->text($x);

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

	$pnu = \libphonenumber\PhoneNumberUtil::getInstance();
	$r = $pnu->parse($p, $l);
	$r = $pnu->format($r, \libphonenumber\PhoneNumberFormat::E164);
	return $r;
}

// Format Phone
function _phone_nice($p, $l='US')
{
	$p = trim($p);
	if (empty($p)) {
		return null;
	}

	$pnu = \libphonenumber\PhoneNumberUtil::getInstance();
	$r = $pnu->parse($p, $l);
	$r = $pnu->format($r, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
	return $r;
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

	$tlf = new Twig_Loader_Filesystem(array(
		$path,
		sprintf('%s/twig', APP_ROOT)
	));
	$cfg = array(
		'strict_variables' => true,
		'debug' => true,
		'cache' => '/tmp/twig',
	);
	$twig = new Twig_Environment($tlf, $cfg);
	//$twig->addFilter(new Twig_Filter('base64', function($x) {
	//      return chunk_split(base64_encode($x), 72);
	//}));

	if (empty($data)) {
		$data = array();
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
