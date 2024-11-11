<?php
/**
 * https://gist.github.com/aczietlow/7c4834f79a7afd920d8f
 * https://github.com/seleniumhq/selenium-google-code-issue-archive/issues/2766
 * https://php-webdriver.github.io/php-webdriver/1.4.0/Facebook/WebDriver/Remote/RemoteWebDriver.html
 */

namespace OpenTHC\Test;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class BaseBrowser extends Base {

	public static $cfg = [];

	protected static $stat = 'PASSED';
	protected static $stat_int = 0;
	protected static $stat_msg = '';

	protected static $wd;

	/**
	 *
	 */
	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();

		$ob_combo_list = [
			'Linux   | latest   | Chrome  | latest',
			'Linux   | latest   | Firefox | latest',
			// 'Windows | 11       | Chrome  | latest',
			// 'Windows | 11       | Edge    | latest',
			// 'Windows | 11       | Firefox | latest',
			// 'Windows | 10       | Chrome  | latest',
			// 'Windows | 10       | Edge    | latest',
			// 'Windows | 10       | Firefox | latest',
			// 'OS X    | Sonoma   | Chrome  | latest',
			// 'OS X    | Sonoma   | Firefox | latest',
			// 'OS X    | Sonoma   | Safari  | 17',
			// 'OS X    | Ventura  | Chrome  | latest',  // SendKeys issue on Password ?
			// 'OS X    | Ventura  | Firefox | latest',
			// 'OS X    | Ventura  | Safari  | 16.5',
			// 'OS X    | Monterey | Chrome  | latest',
			// 'OS X    | Monterey | Firefox | latest',
			// 'OS X    | Monterey | Safari  | 15.6',
		];
		$ob_combo_pick = $ob_combo_list[ array_rand($ob_combo_list) ];
		$ob_combo_pick = preg_split('/\s+\|\s+/', $ob_combo_pick);

		// The names here are confusing
		// The documentation is conflicting it seems?
		// Or maybe the tooling automatically knows if W3C vs JSONWP protocol?
		$cfg = array(
			// OS on BrowserStack
			'os' => $ob_combo_pick[0], // 'Windows',
			'os_version' => $ob_combo_pick[1],
			// LambdaTest calls it "platform"
			'platform' => $ob_combo_pick[0],
			// Both call it this
			'browser' => $ob_combo_pick[2],
			// 'browserName' => $ob_combo_pick[2],
			'browserVersion' => $ob_combo_pick[3],
			// 'project' => '', // Valid, Preferred if both present
			// 'projectName' => '', // Valid
			// 'build' => '',
			// 'buildName' => '',
			// 'sessionName' => sprintf('B2B %d', getmypid()),
			'idleTimeout' => 30,
			// 'browserstack.console' => 'verbose',
			// 'browserstack.debug' => true,
			// 'LT:Options' => [],
		);
		$cfg = array_merge($cfg, self::$cfg);

		// Pick URL
		$url = '';
		if ( ! empty($_ENV['OPENTHC_TEST_WEBDRIVER_URL'])) {
			$url = $_ENV['OPENTHC_TEST_WEBDRIVER_URL']; // v1
		} elseif (defined('OPENTHC_TEST_WEBDRIVER_URL')) {
			$url = OPENTHC_TEST_WEBDRIVER_URL; // v0
		}
		self::$wd = RemoteWebDriver::create( $url, $cfg);

		self::$wd->manage()->window()->maximize();

	}

	/**
	 *
	 *
	 */
	function tearDown() : void
	{
		if (self::$stat != 'FAILED') {
			self::$stat = ($this->hasFailed() ? 'FAILED' : 'PASSED');;
			// self::$stat = $this->getStatus();
		}

		self::$stat_int = $this->getStatus();
		self::$stat_msg = $this->getStatusMessage();

	}

	public static function tearDownAfterClass() : void
	{
		$sid = self::$wd->getSessionId();
		$sim = sprintf('int=%d; msg=%s', self::$stat_int, self::$stat_msg);

		echo "\nDONE SESSION ID: {$sid}; stat={$sim}\n";

		// Post to BrowserStack
		// if ( ! empty(self::$stat_int)) {

		// 	$cfg = parse_url(OPENTHC_TEST_WEBDRIVER_URL);
		// 	$url = sprintf('https://api.browserstack.com/automate/sessions/%s.json', $sid);
		// 	$req = _curl_init($url);
		// 	curl_setopt($req, CURLOPT_USERPWD, sprintf('%s:%s', $cfg['user'], $cfg['pass']));
		// 	curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'PUT');
		// 	curl_setopt($req, CURLOPT_POSTFIELDS, json_encode([
		// 		'status' => 'failed',
		// 		'reason' => $sim,
		// 	]));
		// 	curl_setopt($req, CURLOPT_HTTPHEADER, [
		// 		'content-type: application/json'
		// 	]);
		// 	curl_exec($req);
		// 	curl_close($req);

		// }

		// file_put_contents(sprintf('%s/webroot/test-output/last-screenshot.png', APP_ROOT), self::$wd->takeScreenshot());
		// file_put_contents(sprintf('%s/webroot/test-output/last-screenshot.png', APP_ROOT), self::$wd->takeScreenshot());

		sleep(4);

		self::$wd->quit();

	}

	/**
	 * Load a Page
	 */
	public function getPage($u)
	{
		self::$wd->get($u);
		// Check for PHP Errors, get text, or source or HTML and clean-up or something, then Assert? Make a evalPHPErrors common routine?
		//$html = self::$wd->getPageSource();
		//$this->assertDoesNotMatchRegularExpression('/parse error/im', $html );
		//$this->assertDoesNotMatchRegularExpression('/syntax error/im', $html );
		//$this->assertDoesNotMatchRegularExpression('/error:.+in.+on line \d+/im', $html );
		//$this->assertDoesNotMatchRegularExpression('/notice:.+in.+on line \d+/im', $html );
		//$this->assertDoesNotMatchRegularExpression('/warning:.+in.+on line \d+/im', $html );
		//return $html;
	}

	/**
	 * Get an Element by Selector, does magic string promotion
	 */
	public function findElement($find)
	{
		if (is_object($find)) {
			// OK
		} elseif (is_string($find)) {
			if (preg_match('/^([\#\.])(.+)$/', $find, $m)) {
				switch ($m[1]) {
				case '#':
					$find = WebDriverBy::id($m[2]);
					break;
				case '.':
					$find = WebDriverBy::className($m[2]);
					break;
				}
			} elseif (preg_match('/^\/\/.+$/', $find)) {
				$find = WebDriverBy::xpath($find);
			} else {
				$find = WebDriverBy::cssSelector($find);
			}
		}

		$e = self::$wd->findElement($find);
		$pt = $e->getLocation();
		// var_dump($pt);

		// Scroll
		self::$wd->executeScript('arguments[0].scrollIntoView({ behavior: "instant", block: "center", inline: "center" })', [ $e ]);

		return $e;
	}

	/**
		Wrap WebDriver
	*/
	public function findElements($find)
	{
		return self::$wd->findElements($find);
	}

	/**
	 * @after
	 */
	// public function afterTestTakeScreenshot()
	// {
	// 	$this->takeScreenshot($filename);
	// }

	// public function takeScreenshot($filename = null)
	// {
	// 	if (empty($filename)) {
	// 		$class_name = get_class($this);
	// 		$test_name = $this->getName();
	// 		if (empty($class_name)) {
	// 			$class_name = 'UnknownClass';
	// 		}
	// 		if (empty($test_name)) {
	// 			$test_name = 'TestClass_' . uniqid();
	// 		}
	// 		$filename = sprintf("%s::%s", $class_name, $test_name);
	// 	}
	// 	$when = date('Y-z');

	// 	$path = sprintf("%s/%s", APP_ROOT, "test/var/$when/");
	// 	if (!is_dir($path)) {
	// 		mkdir($path, 0755, true);
	// 	}

	// 	$filepath = sprintf("%s/%s", $path, "$filename.png");
	// 	return self::$wd->takeScreenshot($filepath);
	// }

}
