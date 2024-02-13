<?php
/**
 * Integrates with https://ntfy.sh
 *
 * @see https://docs.ntfy.sh/publish
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Service;

class Ntfy
{
	protected $ch;

	/**
	 *
	 */
	function __construct(?string $ch)
	{
		if (empty($ch)) {
			$ch = \OpenTHC\Config::get('ntfy/channel');
		}

		$this->ch = ltrim($ch, '/');
	}

	function send(string $msg, ?int $pri)
	{
		$req = _curl_init(sprintf('https://ntfy.sh/%s', $this->ch));
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($req, CURLOPT_POSTFIELDS, $msg);
		curl_setopt($req, CURLOPT_HTTPHEADER, [
		        'content-type: text/plain',
			// 'title' => '',
			// 'priority' => '',
			// 'tags' => '',
		]);

		// curl_setopt($req, 

		$res = curl_exec($req);
		return json_decode($res);
	}

}
