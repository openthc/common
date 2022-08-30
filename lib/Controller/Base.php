<?php
/**
 * OpenTHC Base Controller
 */

namespace OpenTHC\Controller;

class Base
{
	protected $_container;

	/**
	 * Save the Container from Slim
	 */
	function __construct($c)
	{
		$this->_container = $c;
	}

	/**
	 * Extenders should implement this
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		_exit_text('Not Implemented [OCB-025]', 501);
	}

	/**
	 * Parse some JSON Input
	 */
	function parseJSON()
	{
		// Method Check
		switch ($_SERVER['REQUEST_METHOD']) {
		case 'POST':
		case 'PUT':
			// OK
			break;
		default:
			_exit_json(array(
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Verb [OCB-036]' ]
			), 405);
		}

		// Content Type
		$x = strtok($_SERVER['CONTENT_TYPE'], ';');
		if ('application/json' != $x) {
			_exit_json(array(
				'data' => null,
				'meta' => [
					'detail' => 'Invalid Content Type [OCB-043]',
					'type' => $x,
				]
			), 405);
		}

		$data = file_get_contents('php://input');
		if (empty($data)) {
			_exit_json(array(
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Input [OCB-017]' ]
			), 400);
		}

		if (!empty($data)) {
			$data = json_decode($data, true);
		}
		if (empty($data)) {
			_exit_json(array(
				'data' => null,
				'meta' => [
					'detail' => 'Invalid Input [OCB-027]',
					'error_message' => json_last_error_msg(),
				]
			), 400);
		}

		return $data;

	}


	/**
	 * Adds a Session Flash Message
	 * @param $data Twig Data Array
	 * @return Twig Data Array w/Flash Messages
	 */
	protected function makeTwigData(array $data = null) : array
	{
		$x = \Edoceo\Radix\Session::flash();
		if (empty($x)) {
			return($data);
		}

		// Rewrite Radix Style to Bootstrap Style
		$x = str_replace('<div class="good">', '<div class="alert alert-success alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="info">', '<div class="alert alert-info alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="warn">', '<div class="alert alert-warning alert-dismissible" role="alert">', $x);
		$x = str_replace('<div class="fail">', '<div class="alert alert-danger alert-dismissible" role="alert">', $x);

		// Add Close Button
		$x = str_replace('</div>', '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden=true">&times;</span></button></div>', $x);

		return array_merge([ 'alert' => $x ], $data ?: []);
	}

	/**
	 * Directly execute this script
	 * no buffering
	 * script is responsible for headers
	 */
	function direct($file, $data)
	{
		while (ob_get_level()) { ob_end_clean(); }

		$x = ltrim($file, '/');
		$x = basename($x, '.php');
		$x = sprintf('%s/view/%s.php', APP_ROOT, $x);

		require_once($x);

		exit(0);
	}

	/**
	 * top level render, called by controller
	 */
	function render($file, $data)
	{
		$view = $this->_render_class($file, $data);
		return $view->render();
	}

	/**
	 * Underlying Render Class (@todo seperate file (../View.php))
	 */
	function _render_class($f0, $d0)
	{
		// Private Anon-Class/Object for View
		$view = new class($f0, $d0) {

			private $layout_file;
			private $output_file;

			private $head;
			private $body; // Main Content Body
			private $foot;

			private $data;

			function __construct($f, $d)
			{
				$this->layout_file = sprintf('%s/view/_layout/html.php', APP_ROOT);
				// $this->layout_file = sprintf('%s/view/html-left.php', APP_ROOT);

				$f = ltrim($f, '/');
				$f = preg_replace('/\.php$/', '', $f);
				$f = sprintf('%s/view/%s.php', APP_ROOT, $f);

				$this->output_file = $f;

				$this->data = $d;

			}

			/**
			 * Render a Block File
			 */
			function block($f, $data=null)
			{
				// Upscale Data
				if (empty($data)) {
					$data = $this->data;
				}

				// File
				$f = ltrim($f, '/');
				$f = preg_replace('/\.php$/', '', $f);
				$f = sprintf('%s/view/_block/%s.php', APP_ROOT, $f);

				if (is_file($f)) {
					ob_start();
					include($f);
					return ob_get_clean();
				}

			}

			/**
			 * Try to Further Isolate the Context for Bloxks?
			 */
			function _block($f, $data)
			{
				// $x = function($file, $data) { include($file); };

				// Anon-Subclass for Context Isolation?
				// $view = new class($file, $data) {

				//      protected $data;
				//      function __construct($file, $data)
				//      {
				//              $this->data = $data;
				//              $this->file = $file;
				//      }

				//      function render()
				//      {
				//              $data = $this->data;
				//              return include($this->file);
				//      }

				// };

				// return $view->render();
			}

			/**
			 * Render the Requested View
			 */
			function render()
			{
				$data = $this->data;

				ob_start();
				require_once($this->output_file);
				$body = ob_get_clean();

				// Strip out JavaScript and add it to the TAIL
				$foot_script = [];
				if (preg_match_all('/(<script.+?<\/script>)/ms', $body, $m)) {
					foreach ($m[1] as $s) {
						$foot_script[] = $s;
						$body = str_replace($s, "\n", $body);
					}
				}

				$this->body = $body;
				$this->foot_script = implode("\n", $foot_script);

				ob_start();
				require_once($this->layout_file);
				return ob_get_clean();

			}

		};

		return $view;
	}

}
