#!/usr/bin/php
<?php
/**
 * Convert phpunit style XML into HTML
 */

// Read from PHPUnit?
// <xsl:variable name="fail_bad" select="'0.4'" />
// <xsl:variable name="time_slow_hi" select="'10'" />
// <xsl:variable name="time_slow_lo" select="'3'" />
// <xsl:variable name="time_fast_hi" select="'1'" />
// <xsl:variable name="good_time" select="'0.4'" />

$source_file = $argv[1]; // sprintf('%s/webroot/test-output/phpunit.xml', APP_ROOT);
$output_file = $argv[2]; // sprintf('%s/webroot/test-output/phpunit.html', APP_ROOT);

$source_data = file_get_contents($source_data);

$xml = new \SimpleXMLElement($source_data, LIBXML_NONET);

// testsuites/testsuite/testsuite/testsuite/testcase
ob_start();
_process_test_suite_xml($xml);
$output_data = ob_get_clean();

$output_html = __emit_html_page($output_data);
file_put_contents($output_file, $output_html);

function _process_test_suite_xml($node0)
{
	static $depth = 0;

	$node0_name = $node0->getName();
	$node0_attr = $node0->attributes();

	// printf("node:%d:%s\n", $depth, $node0_name);
	// Pleease Close Table?
	$table_open = false;

	switch ($node0_name) {
		case 'testsuites':
			// Ignored
			break;
		case 'testsuite':
			if (empty($node0_attr['name'])) {
				echo '<section class="mb-4">';
				echo "<h1>PHP Unit Test Cases</h1>\n";
				echo '<div class="row mb-2">';
				__draw_card(sprintf('<div>Tests</div><strong>%d</strong>', $node0_attr['tests']));
				__draw_card(sprintf('<div>Time</div><strong>%s</strong>', $node0_attr['time']));
				__draw_card(sprintf('<div>Assertions</div><strong>%s</strong>', $node0_attr['assertions']));
				__draw_card(sprintf('<div>Warnings</div>%s', __draw_zero_success_or_else($node0_attr['warnings'], 'text-warning')));
				__draw_card(sprintf('<div>Skipped</div>%s', __draw_zero_success_or_else($node0_attr['skipped'], 'text-warning')));
				__draw_card(sprintf('<div>Errors</div>%s', __draw_zero_success_or_else($node0_attr['errors'], 'text-danger')));
				__draw_card(sprintf('<div>Failures</div>%s', __draw_zero_success_or_else($node0_attr['failures'], 'text-danger')));
				echo '</div>';
				echo '</section>';
			} elseif (empty($node0_attr['file'])) {
				echo '<section class="mb-4">';
				printf('<h2>Suite: %s</h2>', __h($node0_attr['name']));

				echo '<div class="row mb-2">';
				__draw_card(sprintf('<div>Tests</div><strong>%d</strong>', $node0_attr['tests']));
				__draw_card(sprintf('<div>Time</div><strong>%s</strong>', $node0_attr['time']));
				__draw_card(sprintf('<div>Assertions</div><strong>%s</strong>', $node0_attr['assertions']));
				__draw_card(sprintf('<div>Warnings</div>%s', __draw_zero_success_or_else($node0_attr['warnings'], 'text-warning')));
				__draw_card(sprintf('<div>Skipped</div>%s', __draw_zero_success_or_else($node0_attr['skipped'], 'text-warning')));
				__draw_card(sprintf('<div>Errors</div>%s', __draw_zero_success_or_else($node0_attr['errors'], 'text-danger')));
				__draw_card(sprintf('<div>Failures</div>%s', __draw_zero_success_or_else($node0_attr['failures'], 'text-danger')));
				echo '</div>';

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
						$v = __draw_zero_success_or_else($node0_attr[$k], 'text-warning');
						printf('<td class="text-end">%s</td>', $v);
					}
					foreach ([ 'errors', 'failures' ] as $k) {
						$v = __draw_zero_success_or_else($node0_attr[$k], 'text-danger');
						printf('<td class="text-end">%s</td>', $v);
					}
					printf('<td class="text-end"><strong>%s</strong></td>', $node0_attr['time']);
					?>
				</tr>
				<?php
			}
			break;
		case 'testcase':
			$peek = $node0->children();
			echo '<tr>';
			printf('<td colspan="2" class="ps-4">Test: %s', __h($node0_attr['name']) . '</td>');
			echo '<td class="text-end">' . $node0_attr['assertions'] . '</td>';
			echo '<td colspan="4">';
			if (isset($peek->skipped)) {
				echo '<strong class="text-warning">Skipped</strong>';
			}
			echo '</td>';
			printf('<td class="text-end">%s</td>', $node0_attr['time']);
			echo '</tr>';
			break;
		case 'error':
		case 'failure':
		case 'warning':
		case 'system-out':
			$css_list = [ 'fs-4 p-2' ];
			switch ($node0_name) {
				case 'error':
				case 'failure':
					$css_list[] = 'text-bg-danger'; // bg-danger-subtle
					break;
				case 'warning':
					$css_list[] = 'text-bg-warning'; // bg-warning-subtle
					break;
			}
			$css_list = implode(' ', $css_list);
			echo '<tr><td colspan="8">';
			printf('<div class="%s">%s</div>', $css_list, __h($node0['type']));
			echo '<pre class="p-2 bg-secondary-subtle" style="white-space: pre-wrap;">';
			echo "\n";
			echo __h($node0->__toString());
			echo '</pre>';
			echo "</tr>";
			break;
		case 'skipped':
			// echo '<tr><td>SKIPPED</td></tr>';
			break;
		default:
			var_dump($node0_name);
			var_dump($node0);
			throw new \Exception('Invalid Node');
	}

	$node1_list = $node0->children();
	foreach ($node1_list as $node1) {
		$depth++;
		_process_test_suite_xml($node1);
		$depth--;
	}

	if ($table_open) {
		echo '</tbody>';
		echo '</table>';
		echo '</section>';
	}

}

function __draw_card($body)
{
	echo '<div class="col">';
	echo '<div class="card"><div class="card-body text-center">';
	echo $body;
	echo '</div></div>';
	echo '</div>';

}

function __draw_zero_success_or_else($v, $c)
{
	$v = floatval($v);
	return sprintf('<strong class="%s">%s</strong>', ($v == 0 ? 'text-success' : $c), $v);
}

function __time_style($t)
{
	// <xsl:choose>
	// <xsl:when test="@time &gt; $time_slow_hi"> text-danger</xsl:when>
	// <xsl:when test="@time &gt; $time_slow_lo"> text-warning</xsl:when>
	// <xsl:when test="@time &gt; $time_fast_hi"> text-info</xsl:when>
	// <xsl:when test="@tests &gt; 0 and @time &lt; $good_time"> text-success</xsl:when>
	// <xsl:when test="@tests = 0"></xsl:when>
	// <xsl:otherwise></xsl:otherwise>
	// </xsl:choose>
}

/**
 *
 */
function __emit_html_page($body)
{
	return <<<HTML
	<html>
	<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="initial-scale=1, user-scalable=yes" />
	<meta name="application-name" content="OpenTHC" />
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
