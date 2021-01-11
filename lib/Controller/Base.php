<?php
/**
 * Base Controller
 */

namespace OpenTHC\Controller;

class Base
{
	protected $_container;

	/**
		Save the Container
	*/
	function __construct($c)
	{
		$this->_container = $c;
	}

	/**
		Extenders should implement this
	*/
	function __invoke($REQ, $RES, $ARG)
	{
		die("Not Implemented");
	}

	/**
		Parse some JSON Input
	*/
	function parseJSON()
	{
		if ('POST' != $_SERVER['REQUEST_METHOD']) {
			_exit_json(array(
				'meta' => [ 'detail' => 'Invalid Verb [ALU#036]' ]
			), 405);
		}
		$x = strtok($_SERVER['CONTENT_TYPE'], ';');
		if ('application/json' != $x) {

			_exit_json(array(
				'meta' => [
					'detail' => 'Invalid Content Type [ALU#043]',
					'type' => $x,
				]
			), 405);

		}

		$data = file_get_contents('php://input');
		if (empty($data)) {
			_exit_json(array(
				'meta' => [ 'detail' => 'Invalid Input [ALU#017]' ]
			), 400);
		}

		if (!empty($data)) {
			$data = json_decode($data, true);
		}
		if (empty($data)) {
			_exit_json(array(
				'meta' => [ 'detail' => 'Invalid Input [ALU#027]' ]
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


	function render($RES, $file, $data)
	{
		// Private Anon-Class/Object for View
		$view = $this->_render_class($file, $data)

	}


	function _render_class($file, $data)
	{
		$view =  new class($data) {

			private $head;
			private $body; // Main Content Body
			private $foot;

			private $data;
			private $layout_file;

			function __construct($file, $data)
			{
				$this->layout_file = sprintf('%s/view/_layout/html.php', APP_ROOT);

				$this->output_file = $file;
				// $this->layout_file = sprintf('%s/view/html-left.php', APP_ROOT);
				// $this->layout_file = sprintf('%s/view/html.php', APP_ROOT);
				$this->data = $data;

			}

			/**
			 * Render a Block File
			 */
			function block($f, $d)
			{
				$f = ltrim($f, '/');
				$f = basename($f, '.php');
				$f = sprintf('%s/view/_block/%s.php', APP_ROOT, $f);

				if (is_file($f)) {
					ob_start();
					require_once($f);
					return ob_get_clean();
				}

			}

			function _block($f, $d)
			{

			}

			function render($file)
			{
				$file = ltrim($file, '/');
				$file = sprintf('%s/view/%s', APP_ROOT, $file);

				$data = $this->data;

				ob_start();
				require_once($file);
				$body = ob_get_clean();

				// Strip out JavaScript and add it to the TAIL
				if (preg_match_all('/(<script.+?<\/script>)/ms', $body, $m)) {
					foreach ($m[1] as $s) {
						$foot_script[] = $s;
						$body = str_replace($s, "\n", $body);
					}
				}

				$this->body = $body;
				$this->foot_script = implode("\n", $foot_script);
				$this->body = ob_get_clean();

			}

		};

		return $view;
	}

}
