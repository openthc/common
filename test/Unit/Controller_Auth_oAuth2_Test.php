<?php
/**
 * Unit test for \OpenTHC\Controller\Auth\oAuth2
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test;

class Mock_oAuth2 extends \OpenTHC\Controller\Auth\oAuth2
{

	function __invoke($REQ, $RES, $ARG) {
		return $RES->withStatus(200);
	}

	// Reflect protected methods
	function expose_getProvider($r=null) { return $this->getProvider($r); }
	function expode_getProfileFromToken($p) { return $this->getProfileFromToken($p); }
}

class Controller_Auth_oAuth2_Test extends \OpenTHC\Test\Base
{
	protected static function inject_config($config=[])
	{
		file_put_contents(APP_ROOT . '/etc/config.php', '<?php return ' . var_export(array_replace_recursive([
			'openthc' => [
				'sso' => [
					'origin' => 'https://sso.openthc.example.com',
					'client-id' => 'public_key',
					'client-sk' => 'secret key',
				],
			],
		], $config), true) . ';');
	}

	public static function setUpBeforeClass(): void
	{
		mkdir(APP_ROOT . '/etc');
		self::inject_config();
		\OpenTHC\Config::init(APP_ROOT);
	}

	public function test_oAuth2()
	{
		// Mock slim environment
		$container = new \Slim\Container;

		// oAuth2 must be extended in order to implement the invoke method
		$mock_controller = new Mock_oAuth2($container);

		$req = \Slim\Http\Request::createFromEnvironment(\Slim\Http\Environment::mock());
		$res = new \Slim\Http\Response();
		$x = $mock_controller($req, $res, []);

		// Our mock implementation works
		$this->assertEquals(200, $x->getStatusCode());
	}

	// Test checkState() happy path
	public function test_checkState()
	{
		$container = new \Slim\Container;
		$mock_controller = new Mock_oAuth2($container);
		$_SESSION = [ 'oauth2-state' => 'ABZ-XYZ' ];
		$_GET     = [ 'state'        => 'ABZ-XYZ' ];
		$x = $mock_controller->checkState();
		$this->assertEmpty($x);
	}

	// $a fails
	public function test_checkState_a_fails()
	{
		$container = new \Slim\Container;
		$mock_controller = new Mock_oAuth2($container);
		$_SESSION = [ 'oauth2-state' => null ];
		$_GET     = [ 'state'        => 'ABZ-XYZ' ];
		$this->expectException(\Exception::class);
		$x = $mock_controller->checkState();

	}

	// $b fails
	public function test_checkState_b_fails()
	{
		$container = new \Slim\Container;
		$mock_controller = new Mock_oAuth2($container);
		$_SESSION = [ 'oauth2-state' => 'ABZ-XYZ' ];
		$_GET     = [ 'state'        => null ];
		$this->expectException(\Exception::class);
		$x = $mock_controller->checkState();

	}

	// match fails
	public function test_checkState_match_fails()
	{
		$container = new \Slim\Container;
		$mock_controller = new Mock_oAuth2($container);
		$_SESSION = [ 'oauth2-state' => '123-789' ];
		$_GET     = [ 'state'        => 'ABZ-XYZ' ];
		$this->expectException(\Exception::class);
		$x = $mock_controller->checkState();
	}

	public function test_getProfileFromToken()
	{
		// Mock slim environment
		$container = new \Slim\Container;

		// oAuth2 must be extended in order to implement the invoke method
		$mock_controller = new Mock_oAuth2($container);
		$_SESSION = [ 'oauth2-state' => 'ABZ-XYZ' ];
		$_GET     = [ 'state'        => 'ABZ-XYZ', 'code' => 'MY_CODE', ];

		$tokMock = $this->createMock(\League\OAuth2\Client\Token\AccessToken::class);
		$tokMock->method('jsonSerialize')->willReturn([ 
			'access_token' => 'your_access_token',
			'token_type' => 'bearer',
		]);

		$pMock = $this->createMock(\League\OAuth2\Client\Provider\GenericProvider::class);
		$pMock->expects($this->once())
			->method('getAccessToken')
			->willReturn($tokMock)
		;
		$pMock->expects($this->once())
			->method('getResourceOwner')
			->willReturn(new class {
				function toArray() {
					return [
						'Contact' => [
							'id' => 'contact_id',
						],
						'Company' => [
							'id' => 'company_id',
						],
						'scope' => 'space separated values',
					];
				}
			})
		;

		$profile = $mock_controller->expode_getProfileFromToken($pMock);
		$this->assertNotEmpty($profile);
		$this->assertNotEmpty($profile['Contact']['id']);
		$this->assertNotEmpty($profile['Company']['id']);
	}

	// Test getProvider()
	public function test_getProvider()
	{
		$container = new \Slim\Container;
		// $container['config'] = [];
		// $container['config']['openthc/sso'] = [
		// 	'origin' => 'https://sso.openthc.example.com',
		// 	'id' => 'public_key',
		// 	'secret' => 'secret key',
		// ];
		$mock_controller = new Mock_oAuth2($container);
		$p = $mock_controller->expose_getProvider();
		$this->assertNotEmpty($p);

		$arg = array(
			'scope' => 'space separated arbitrary values',
		);
		$url = $p->getAuthorizationUrl($arg);
		$this->assertNotEmpty($url);
		$this->assertStringStartsWith('https://sso.openthc.example.com', $url);

		// Get the state generated for you and store it to the session.
		$this->assertNotEmpty($p->getState());
	}

/*
	We do not get to test these until we can do Dependency Injection
		- Including the etc/config.php is not working for some reason.
		- Our `inject_config` does not work the way we want. By the time any instance of it runs here, Config::init will have already been run once, and the existing cache will remain in the static variables. We decided to change fewer things while we don't have tests for them, and I dont have a test for any direction in the config class.
	/mbw 2024-316


	// Test getProvider()
	public function test_getProvider_fail_clientId()
	{
		self::inject_config([
			'openthc' => [
				'sso' => [
					'client-id' => null,
				],
			],
		]);
		$container = new \Slim\Container;
		// $container['config']['openthc/sso'] = [
		// 	'origin' => 'https://sso.openthc.example.com',
		// 	'id' => null,
		// 	'secret' => 'secret key',
		// ];
		$mock_controller = new Mock_oAuth2($container);
		$this->expectException(\Exception::class);
		$p = $mock_controller->expose_getProvider();
	}

	// Test getProvider()
	public function test_getProvider_fail_clientSecret()
	{
		$container = new \Slim\Container;
		$container['config']['openthc/sso'] = [
			'origin' => 'https://sso.openthc.example.com',
			'id' => 'public key',
			'secret' => null,
		];
		$mock_controller = new Mock_oAuth2($container);
		$this->expectException(\Exception::class);
		$p = $mock_controller->expose_getProvider();
	}
*/
}