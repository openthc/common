<?php
/**
 *
 */

namespace OpenTHC\Test\Service;

class Pub_Test extends \OpenTHC\Test\Base
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
			'client-pk' => OPENTHC_TEST_SERVICE_CLIENT_PK, // \OpenTHC\Config::get('openthc/lab/public'),
			'client-sk' => OPENTHC_TEST_SERVICE_CLIENT_SK, // \OpenTHC\Config::get('openthc/lab/secret'),
		];

		$pub = new \OpenTHC\Service\Pub($cfg);
		// $put->put($body, $type); // v0
		// $res = $pub->setPath('/b2b/ID/file.txt'); // v1
		// $url = $pub->getURL(); // v1
		// $res = $pub->put($body, $type); // v1
		// $url == $res['data']; // v1

		// '/b2b/01HTEFE2MQCTW1QRF78DQACNBZ/test.txt';
		$ref_path = '/b2b/01HTEFE2MQCTW1QRF78DQACNBZ/file.txt';
		// $ref_path = '/b2b/01HTEFE2MQCTW1QRF78DQACNBZ/file.pdf';
		$url0 = $pub->setPath($ref_path);
		$this->assertNotEmpty($url0);
		// $pub->setName('test.txt');

		$url1 = $pub->getUrl();
		$this->assertNotEmpty($url1);
		$this->assertEquals($url0, $url1);

		$msg_body = 'TEST MESSAGE';
		$msg_type = 'text/plain';

		$res = $pub->put($msg_body, $msg_type);

		$this->assertIsArray($res);
		$this->assertArrayHasKey('data', $res);
		$this->assertEquals($url0, $res['data']);

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

		$ref_path = '/b2b/01HTEFE2MQCTW1QRF78DQACNBZ/test.txt';

		$pub->setPath($ref_path);

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
		// var_dump($res);
		$this->assertIsArray($res);
		$this->assertArrayHasKey('data', $res);
		$this->assertEquals($msg_body, $res['data']);

	}

	function test_path_recalc() {

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
			'client-pk' => OPENTHC_TEST_SERVICE_CLIENT_PK, // \OpenTHC\Config::get('openthc/lab/public'),
			'client-sk' => OPENTHC_TEST_SERVICE_CLIENT_SK, // \OpenTHC\Config::get('openthc/lab/secret'),
		];

		$pub = new \OpenTHC\Service\Pub($cfg);

		$req_path = '/test/01HTJNXWTMCK6HTR1XVDTR4M51/file0.txt';
		$url0 = $pub->setPath($req_path);
		$url0 = str_replace('file0.txt', '', $url0);
		// var_dump($url0);
		// https://pub.openthc.dev/DKw9EDihrZGEQ64g4kF78VwAAeySpNXIcFN8ddjVvhU/file0.txt

		$req_path = '/test/01HTJNXWTMCK6HTR1XVDTR4M51/file1.txt';
		$url1 = $pub->setPath($req_path);
		$url1 = str_replace('file1.txt', '', $url1);
		// var_dump($url1);
		// https://pub.openthc.dev/DKw9EDihrZGEQ64g4kF78VwAAeySpNXIcFN8ddjVvhU/file0.txt

		$this->assertEquals($url0, $url1);

	}

}
