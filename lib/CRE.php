<?php
/**
 * An Interface to the OpenTHC Pipe
 */

namespace OpenTHC;

class CRE
{
	const ENGINE = 'openthc';

	protected $_api_base = 'https://pipe.openthc.com';

	protected $_c; // Client Connection

	/**
		@param $sid Session ID to the PIPE Service
	*/
	function __construct($sid=null)
	{
		$jar = new \GuzzleHttp\Cookie\CookieJar();

		if (!empty($sid)) {
			$c = new \GuzzleHttp\Cookie\SetCookie(array(
				'Domain' => 'pipe.openthc.com',
				'Name' => 'poc',
				'Value' => $sid,
			));
			$jar->setCookie($c);
		}

		$this->_c = new \GuzzleHttp\Client(array(
			'base_uri' => $this->_api_base,
			'cookies' => $jar,
			'headers' => array(
				'user-agent' => 'OpenTHC/420.18.304',
			),
			'allow_redirects' => false,
			'http_errors' => false
		));

	}

	/**
	 * Format an Error
	 */
	function formatError($x)
	{
		$ret = array();
		$ret[] = 'OpenTHC CRE Error:';
		$ret[] = json_encode($x, JSON_PRETTY_PRINT);
		return implode(' ', $ret);
	}

	function get($u)
	{
		$r = $this->_c->get($u);
		return json_decode($r->getBody(), true);
	}

	function post($u, $a=null)
	{
		$r = $this->_c->post($u, $a);

		switch ($r->getStatusCode()) {
		case 200:
			// OK
			return json_decode($r->getBody(), true);
			break;
		default:
			$c = $r->getStatusCode();
			$r = $r->getBody()->__toString();
			echo "ERROR #$c:\n$r\n###";
		// 	exit;
			break;
		}

		return json_decode($r->getBody(), true);
	}

	function delete($u)
	{
		$r = $this->_c->delete($u);
		return json_decode($r->getBody(), true);
	}

	/**
		Authnentication Interfaces
	*/
	function auth($p)
	{
		$r = $this->_c->post('/auth/open', array('form_params' => $p));
		return json_decode($r->getBody(), true);

		$this->_auth = $auth;

		$post = array(
			'cre' => $this->_auth['system'],
			'license' => $this->_auth['license'],
			'license-key' => $this->_auth['secret'],
		);
		$res = $this->post('/auth/open', $post);
		switch ($res['status']) {
		case 'failure':
			return $res;
			break;
		case 'success':
			$this->_auth['session-id'] = $res['result'];
			return $res;
			break;
		}
	}

	function ping()
	{
		$r = $this->_c->get('/auth/ping');
		return json_decode($r->getBody(), true);
	}

	/**
	*/
	function company()
	{
		$r = $this->_c->get('/config/company');
		return json_decode($r->getBody(), true);
	}

	/**
	*/
	function contact()
	{
		$r = $this->_c->get('/config/contact');
		return json_decode($r->getBody(), true);
	}

	/**
	*/
	function license()
	{
		$r = $this->_c->get('/config/license?source=true');
		return json_decode($r->getBody(), true);
	}

	/**
	*/
	function lot()
	{
		$r = $this->_c->get('/lot');
		return json_decode($r->getBody(), true);
	}

	/**
	*/
	function plant()
	{
		$r = $this->_c->get('/plant');
		return json_decode($r->getBody(), true);
	}

	/**
	*/
	function product()
	{
		$r = $this->_c->get('/config/product');
		switch ($r->getStatusCode()) {
		case 200:
			// Expected
			echo $r->getBody()->__toString();
			return json_decode($r->getBody(), true);
			break;
		case 304:
			// Old Shit
			return array(
				'status' => 'success',
				'result' => array(),
			);
			break;
		}

	}

	/**
		Wholesale & Retail
	*/
	function sales()
	{
		return array();
	}

	/**
	*/
	function strain()
	{
		$r = $this->_c->get('/config/strain');
		//echo $r->getBody()->__toString();
		return json_decode($r->getBody(), true);
	}

	/**
	*/
	function transfer()
	{
		$r = $this->_c->get('/transfer/outgoing?source=true');
		return json_decode($r->getBody(), true);
	}

	/**
	*/
	function zone()
	{
		$r = $this->_c->get('/config/zone');
		return json_decode($r->getBody(), true);
	}

}
