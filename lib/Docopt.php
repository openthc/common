<?php
/**
 * OpenTHC Docopt Wrapper
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Docopt {

	/**
	 * Parse Docopt Arguments and Provide Result
	 */
	function parse(string $doc, array $arg=[])
	{
		$cfg = [
			'argv' => $arg,
			'exit' => false,
			'help' => true,
			'optionsFirst' => true,
		];
		if (defined('APP_VERSION')) {
			$cfg['version'] = APP_VERSION;
		} else {
			// $git_head = APP_ROOT . '/.git/refs/heads/main';
			$cfg['version'] = 'dev-main';
		}

		$res = \Docopt::handle($doc, $cfg);
		var_dump($res);

		return $res->args;

	}
}
