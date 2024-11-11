<?php
/**
 * Helper Class for Test Cases
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test\Helper;

class XML2HTML {

	/**
	 *
	 */
	function __construct(string $source)
	{
		// $xml = file_get_contents($source_file);
		// $this->xml = new \SimpleXMLElement($xml, LIBXML_NONET);
		$this->xml = simplexml_load_file($source, \SimpleXMLElement::class, LIBXML_NONET);

		$ft0 = filemtime($source);
		$this->dt0 = new \DateTime(sprintf('@%d', $ft0));

	}

	/**
	 *
	 */
	function render(string $output_file) : void
	{
		ob_start();
		$this->render_node($this->xml->testsuite);
		$report_data = ob_get_clean();
		$output_data = $this->render_page($report_data);
		file_put_contents($output_file, $output_data);
	}

	function render_node($node0)
	{
		$node0_name = $node0->getName();
		$node0_attr = $node0->attributes();

		$table_open = false;

		switch ($node0_name) {
			case 'testsuite':

				$date = $this->dt0->format('D Y-m-d H:i:s e');
				// $this->render_node_testsuite($node0_attr);
				// Top Level Suite
				if (empty($node0_attr['name'])) {
					echo '<section class="mb-4">';
					echo "<h1>Test Suite $date</h1>\n";
					$this->render_node_testsuite_summary($node0_attr);
					echo '</section>';
				} elseif (empty($node0_attr['file'])) {

					echo '<section class="mb-4">';
					printf('<h2>Suite: %s</h2>', __h($node0_attr['name']));
					$this->render_node_testsuite_summary($node0_attr);

					$table_open = true;
					?>
					<table class="table table-sm">
					<thead class="table-dark">
					<tr>
						<th>Name</th>
						<th class="text-end">Tests</th>
						<th class="text-end">Assertions</th>
						<th class="text-end">Skipped</th>
						<th class="text-end">Warnings</th>
						<th class="text-end">Errors</th>
						<th class="text-end">Failures</th>
						<th class="text-end">Time</th>
					</tr>
					</thead>
					<tbody>
					<?php
					// echo '</section>';
				} else {
					?>
					<tr class="fs-3">
						<td><?= __h($node0_attr['name']) ?></td>
						<?php
						printf('<td class="text-end"><strong>%s</strong></td>', $node0_attr['tests']);
						printf('<td class="text-end"><strong>%s</strong></td>', $node0_attr['assertions']);
						foreach ([ 'skipped', 'warnings' ] as $k) {
							$v = $this->__draw_zero_success_or_else($node0_attr[$k], 'text-warning');
							printf('<td class="text-end">%s</td>', $v);
						}
						foreach ([ 'errors', 'failures' ] as $k) {
							$v = $this->__draw_zero_success_or_else($node0_attr[$k], 'text-danger');
							printf('<td class="text-end">%s</td>', $v);
						}
						printf('<td class="text-end"><strong>%s</strong></td>', $node0_attr['time']);
						?>
					</tr>
					<?php
				}
				break;
			case 'testcase':
				$this->render_node_testcase($node0, $node0_attr);
				break;
			case 'error':
			case 'failure':
			case 'skipped':
			case 'system-out':
			case 'warning':
				$this->render_node_message($node0);
				break;
			default:
				var_dump($node0_name);
				var_dump($node0);
				throw new \Exception("Invalid Node '$node0_name'");
		}

		$node1_list = $node0->children();
		foreach ($node1_list as $node1) {
			$this->render_node($node1);
		}

		if ($table_open) {
			echo '</tbody>';
			echo '</table>';
			echo '</section>';
		}
	}

	function render_node_message($node0)
	{
		$node0_name = $node0->getName();
		$node0_attr = $node0->attributes();

		$out = [];
		if ( ! empty($node0_attr['message'])) {
			$out[] = sprintf('Message: %s', $node0_attr['message']);
			$out[] = "\n";
		}
		$x = $node0->__toString();
		if ( ! empty($x)) {
			$out[] = $x;
			// $out[] = $this->__html_pre($x);
		}

		$css_list = [ 'fs-4 p-2' ];
		switch ($node0_name) {
		case 'error':
		case 'failure':
			$css_list[] = 'text-bg-danger'; // bg-danger-subtle
			break;
		case 'warning':
			$css_list[] = 'text-bg-warning'; // bg-warning-subtle
			break;
		case 'skipped':
			return;
			break;
		case 'system-out':
			// var_dump()
			break;
		default:
			throw new \Exception("Invalid Node Name: '$node0_name'");
		}

		$css_list = implode(' ', $css_list);
		echo '<tr><td colspan="8">';
		if ( ! empty($node0['type'])) {
			printf('<div class="%s">%s</div>', $css_list, __h($node0['type']));
		}

		$out = implode('', $out);
		echo $this->__html_pre($out);

		echo '</td>';
		echo '</tr>';

	}

	function render_node_testcase($node0, $node0_attr)
	{
		$peek = $node0->children();
		echo '<tr>';
		printf('<td colspan="2" class="ps-4">Test: %s', __h($node0_attr['name']) . '</td>');
		if (isset($peek->skipped)) {
			echo '<td colspan="5" class="text-center text-warning">';
			echo '<strong>Skipped</strong>';
			echo '</td>';
		} else {
			echo '<td class="text-end">' . $node0_attr['assertions'] . '</td>';
			echo '<td colspan="4"></td>';
		}
		printf('<td class="text-end">%s</td>', $node0_attr['time']);
		echo '</tr>';
	}

	function render_node_testsuite_summary($node0_attr)
	{
		echo '<div class="row mb-2">';
		$this->__draw_card(sprintf('<div>Tests</div><strong>%d</strong>', $node0_attr['tests']));
		$this->__draw_card(sprintf('<div>Time</div><strong>%s</strong>', $node0_attr['time']));
		$this->__draw_card(sprintf('<div>Assertions</div><strong>%s</strong>', $node0_attr['assertions']));
		$this->__draw_card(sprintf('<div>Warnings</div>%s', $this->__draw_zero_success_or_else($node0_attr['warnings'], 'text-warning')));
		$this->__draw_card(sprintf('<div>Skipped</div>%s', $this->__draw_zero_success_or_else($node0_attr['skipped'], 'text-warning')));
		$this->__draw_card(sprintf('<div>Errors</div>%s', $this->__draw_zero_success_or_else($node0_attr['errors'], 'text-danger')));
		$this->__draw_card(sprintf('<div>Failures</div>%s', $this->__draw_zero_success_or_else($node0_attr['failures'], 'text-danger')));
		echo '</div>';
	}

	function render_page($body) : string
	{
		return <<<HTML
		<html>
		<head>
		<meta charset="utf-8" />
		<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
		<meta name="viewport" content="initial-scale=1, user-scalable=yes">
		<link rel="stylesheet" href="/vendor/bootstrap/bootstrap.min.css" crossorigin="anonymous">
		<style type="text/css">
		table tr.top {
			background-color: #dddddd;
			border-bottom: 2px solid #000000;
		}

		table tr.single-test th {
			padding-left: 3em;
		}

		table td.c {
			text-align: center;
		}
		table td.r {
			text-align: right;
		}

		.errored {
			background-color: #FFB8BA;
		}

		.no-tests {
			text-decoration: line-through;
			text-decoration-style: double;
		}
		.error-detail-type, .fail-detail-type {
			padding-left: 2em;
			font-weight: bold;
		}
		.error-detail-detail, .fail-detail-detail {
			padding-left: 4em;
			white-space: pre-wrap;
			padding-bottom: 1em;
		}
		</style>
		<title>OpenTHC Test Results</title>
		</head>
		<body>
		<div class="container">
			$body
		</div>
		</body>
		</html>
		HTML;
	}

	/**
	 * Utility Functions
	 */
	function __draw_card($body)
	{
		echo '<div class="col">';
		echo '<div class="card"><div class="card-body text-center">';
		echo $body;
		echo '</div></div>';
		echo '</div>';

	}

	function __draw_zero_success_or_else($v, $c) : string {
		$v = floatval($v);
		return sprintf('<strong class="%s">%s</strong>', ($v == 0 ? 'text-success' : $c), $v);
	}

	function __html_pre($x) : string {
		$x = trim($x);
		if (empty($x)) {
			return '';
		}

		$ret[] = '';
		$ret[] = '<pre class="m-0 p-2 bg-secondary-subtle" style="white-space: pre-wrap;">';
		$ret[] = __h($x);
		$ret[] = '</pre>';

		return implode('', $ret);
	}
}
