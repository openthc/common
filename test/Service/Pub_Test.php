<?php
/**
 *
 */

namespace OpenTHC\Test\Service;

class Pub_Test extends \OpenTHC\Test\Base_Case
{
	function test_publish() {

		\OpenTHC\Config::init(APP_ROOT);
		$this->assertNotEmpty( \OpenTHC\Config::get('openthc/lab/public') );
		$this->assertNotEmpty( \OpenTHC\Config::get('openthc/lab/secret') );
		$this->assertNotEmpty( \OpenTHC\Config::get('openthc/pub/origin') );
		$this->assertNotEmpty( \OpenTHC\Config::get('openthc/pub/public') );

		$cfg = [
			'service' => _ulid(),
			'contact' => _ulid(),
			'company' => _ulid(),
			'license' => _ulid(),
			'client-pk' => \OpenTHC\Config::get('openthc/lab/public'),
			'client-sk' => \OpenTHC\Config::get('openthc/lab/secret'),
		];

		$pub = new \OpenTHC\Service\Pub($cfg);

		$ref_path = '/b2b/01HTEFE2MQCTW1QRF78DQACNBZ';
		$pub->setPath($ref_path);
		$pub->setName('test.txt');

		$url = $pub->getUrl();
		$this->assertNotEmpty($url);

		$msg_body = 'TEST MESSAGE';
		$msg_type = 'text/plain';

		$res = $pub->put($msg_body, $msg_type);

		$this->assertIsArray($res);
		$this->assertArrayHasKey('data', $res);
		$this->assertEquals($url, $res['data']);

	}

	function test_get() {

		\OpenTHC\Config::init(APP_ROOT);
		$this->assertNotEmpty( \OpenTHC\Config::get('openthc/lab/public') );
		$this->assertNotEmpty( \OpenTHC\Config::get('openthc/lab/secret') );
		$this->assertNotEmpty( \OpenTHC\Config::get('openthc/pub/origin') );
		$this->assertNotEmpty( \OpenTHC\Config::get('openthc/pub/public') );

		$cfg = [
			'service' => _ulid(),
			'contact' => _ulid(),
			'company' => _ulid(),
			'license' => _ulid(),
			'client-pk' => \OpenTHC\Config::get('openthc/lab/public'),
			'client-sk' => \OpenTHC\Config::get('openthc/lab/secret'),
		];

		$pub = new \OpenTHC\Service\Pub($cfg);

		$ref_path = '/b2b/01HTEFE2MQCTW1QRF78DQACNBZ';

		$pub->setPath($ref_path);
		$pub->setName('test.txt');

		$url = $pub->getUrl();
		$this->assertNotEmpty($url);

		$msg_body = 'TEST MESSAGE ' . _ulid();
		$msg_type = 'text/plain';

		$res = $pub->put($msg_body, $msg_type);

		$this->assertIsArray($res);
		$this->assertArrayHasKey('data', $res);
		$this->assertEquals($url, $res['data']);

		// var_dump($url);
		$res = $pub->get($url);
		var_dump($res);
		$this->assertIsArray($res);
		$this->assertArrayHasKey('data', $res);
		$this->assertEquals($msg_body, $res['data']);

	}

}
