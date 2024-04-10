<?php
/**
 * StatsD Metrics Functions
 */

/**
 * Send Stat, Static Handle, Immediate Send
 * @param $d Datagram
 */
function _stat_send($d)
{
	static $s;

	if (empty($s)) {

		$url = \OpenTHC\Config::get('statsd/url');
		if (empty($url)) {
			$url = [
				'host' => '127.0.0.1',
				'port' => 8125,
			];
		} else {
			$url = parse_url($url);
		}

		$s = fsockopen(sprintf('udp://%s', $url['host']), $url['port'], $eno, $esz, 1);

	}

	fwrite($s, $d);

}


/**
 * @param $s Stat Path
 * @param $v Counter Value
 */
function _stat_count($s, $v=1)
{
	_stat_send(sprintf('%s:%d|c', $s, $v));
}
function _stat_counter($s, $v)  // @alias
{
	_stat_send(sprintf('%s:%d|c', $s, $v));
}

/**
 * @param $v Gauge Value
 */
function _stat_gauge($s, $v)
{
	_stat_send(sprintf('%s:%d|g', $s, $v));
}

/**
 * @param $v Timer Value
 */
function _stat_timer($s, $v)
{
	_stat_send(sprintf('%s:%d|ms', $s, $v));
}
