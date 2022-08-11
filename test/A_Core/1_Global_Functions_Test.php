<?php
/**
 *
 */

namespace OpenTHC\Test\Core;

class Global_Functions_Test extends \PHPUnit\Framework\TestCase
{
	function test_functions()
	{
		$function_list = [
			'h',
			'base64_decode_url',
			'base64_encode_url',
			'_array_diff_keyval_r',
			'_curl_init',
			'_date',
			'_decrypt',
			'_encrypt',
			'_exit_html',
			'_exit_html_fail',
			'_exit_html_warn',
			'_exit_json',
			'_exit_text',
			'_http_code', // Should be _exit_code
			'_ksort_r',
			'_markdown',
			'_parse_str',
			'_phone_e164',
			'_phone_nice',
			'_random_hash',
			'_tmp_file',
			'_text_stub',
			'_twig',
			'_ulid',
			'_url_assemble',
		];

		foreach ($function_list as $f) {
			$chk = function_exists($f);
			$this->assertTrue($chk, sprintf('Function "%s" is not defined', $f));
		}

	}

}
