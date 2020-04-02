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

		$h = $_ENV['statsd']['host'];
		if (empty($h)) {
			$h = 'udp://127.0.0.1';
		}
		$p = $_ENV['statsd']['port'];
		if (empty($p)) {
			$p = '8125';
		}

		$s = fsockopen($h, $p, $eno, $esz, 1);

	}

	fwrite($s, $d);

}

/**
 * @param $s Stat Path
 * @param $v Counter Value
 */
function _stat_counter($s, $v)
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
