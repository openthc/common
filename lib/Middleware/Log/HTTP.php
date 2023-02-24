<?php
/**
 * Log Request and Response
 * @todo can we use GuzzleHttp\Psr7\str($req) ? 
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Middleware\Log;

class HTTP extends \OpenTHC\Middleware\Base
{
	/**
	 * @todo low-risk interface
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	*/
	public function __invoke($REQ, $RES, $NMW)
	{
		// Log the Full Body
		$log_file = sprintf('%s/var/log/%s.http', APP_ROOT, $_SERVER['UNIQUE_ID']);

		$log_path = dirname($log_file);
		if (!is_dir($log_path)) {
			mkdir($log_path, 0755, true);
		}

		$log_pipe = fopen($log_file, 'a');

		fwrite($log_pipe, json_encode(array(
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_SESSION' => $_SESSION,
			'_SERVER' => $_SERVER,
		)));
		fwrite($log_pipe, "\n");
		// file_put_contents($file, $data);

		$RES = $NMW($REQ, $RES);

		//$file = sprintf('%s/var/%s-res.dump', APP_ROOT, $_SERVER['UNIQUE_ID']);
		// $file = '/tmp/res.dump';
		$data = json_encode(array(
			'_HEAD' => $RES->getHeaders(),
			'_BODY' => $RES->getBody()->__toString(),
		));
		fwrite($log_pipe, $data);
		fclose($log_pipe);
		// file_put_contents($file, $data);

		return $RES;

	}
}
