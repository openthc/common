<?php
/**
 * GeoIP Helper
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class GeoIP
{
	private $api;

	function __construct($cfg=null)
	{
		if (empty($cfg)) {
			$cfg = \OpenTHC\Config::get('maxmind');
		}

		if ( ! empty($cfg['account'])) {
			$this->api = new \GeoIp2\WebService\Client($cfg['account'], $cfg['license-key']);
		}
	}

	/**
	 * @return array data of GeoIP Lookup
	 */
	function get(string $ip)
	{
		if ( ! empty($this->api)) {
			$res = $this->api->city($ip);
			$ret = $res->raw;
			return $ret;
		}

		// Fallback
		// Return an array that looks kinda like the Maxmind GeoIP one.
		if (function_exists('geoip_record_by_name')) {
			$res = geoip_record_by_name($ip);
			$ret = [
				'city' => [
					'name' => $res['city'],
				],
				'continent' => [
					'code' => $res['continent_code'],
				],
				'country' => [
					'iso_code' => $res['country_code'],
				],
				'location' => [
					'latitude' => $res['latitude'],
					'longitude' => $res['longitude'],
				],
				'postal' => [
					'code' => $res['postal_code'],
				]
			];
			return $ret;
		}

		return [];

	}

}
