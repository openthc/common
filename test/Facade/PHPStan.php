<?php
/**
 * PHPStan Wrapper
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Test\Facade;

class PHPStan {

	protected $config_file = '';

	protected $output_path = '';

	/**
	 * Parameters
	 */
	function __construct(array $cfg0 = []) {

		$this->config_file = sprintf('%s/test/phpstan.neon', APP_ROOT);
		$this->output_path = $cfg0['output'];

	}

	function config_create() {

		$cfg = [
			'parameters' => [
				'level' => 6,
				'tipsOfTheDay' => false,
				'bootstrapFiles' => [
					'../boot.php'
				],
				'paths' => [
					'../bin',
					'../etc',
					'../lib',
					'../sbin',
					'../test',
					'../view',
				],
				'ignoreErrors' => [
					'/Undefined variable: \$this/',
					'/Using \$this outside a class/',
					'/Variable \$this might not be defined/',
				],
			]
		];
		// $stan_config = yaml_emit($cfg);

		// It's not YAML it's NEON
		$stan_config = [];
		$stan_config[] = 'parameters:';
		$stan_config[] = '	bootstrapFiles:';
		$stan_config[] = '		- ../boot.php';
		if (is_file(APP_ROOT . '/lib/autoload.php')) {
			$stan_config[] = '		- ../lib/autoload.php';
		}
		if (is_file(APP_ROOT . '/lib/cli.php')) {
			$stan_config[] = '		- ../lib/cli.php';
		}
		if (is_file(APP_ROOT . '/lib/php.php')) {
			$stan_config[] = '		- ../lib/php.php';
		}
		if (is_file(APP_ROOT . '/lib/web.php')) {
			$stan_config[] = '		- ../lib/web.php';
		}
		$stan_config[] = '	level: 9';
		$stan_config[] = '	paths:';
		$path_list = [ 'bin', 'block', 'controller', 'etc', 'lib', 'sbin', 'test', 'theme', 'view' ];
		foreach ($path_list as $p) {
			if (is_dir(sprintf('%s/%s', APP_ROOT, $p))) {
				$stan_config[] = sprintf('		- ../%s', $p);
			}
		}
		$stan_config[] = '	ignoreErrors:';
		$stan_config[] = '		- /Undefined variable: \\\$this/';
		$stan_config[] = '		- /Using \\\$this outside a class/';
		$stan_config[] = '		- /Variable \\\$this might not be defined/';
		$stan_config[] = '	scanDirectories:';
		$stan_config[] = '		- ../vendor';
		$stan_config[] = '	tipsOfTheDay: false';

		implode("\n", $stan_config);

		file_put_contents($this->config_file, $stan_config);

	}

	function config_remove() {

		unlink($this->config_file);

	}

	// Call Static Analyser?
	function execute() {

		$this->config_create();

		$bin = sprintf('%s/vendor/bin/phpstan', APP_ROOT);
		if ( ! is_file($bin)) {
			throw new \Exception('Cannot Find PHPStan');
		}

		$cmd = [];
		$cmd[] = $bin;
		$cmd[] = 'analyze';
		$cmd[] = sprintf('--configuration=%s', $this->config_file);
		$cmd[] = '--error-format=junit';
		$cmd[] = '--no-ansi';
		$cmd[] = '--no-progress';
		$cmd[] = '2>&1';
		$cmd = implode(' ', $cmd);

		// echo "cmd:$cmd\n";

		$out = null;
		$res = null;

		$buf = exec($cmd, $out, $res);
		// var_dump($res);
		// var_dump($buf);
		// var_dump($out);

		$result_data = $buf;
		$result_file = sprintf('%s/phpstan.xml', $this->output_path);
		file_put_contents($result_file, $result_data);

		$output_file = sprintf('%s/phpstan.html', $this->output_path);

		\OpenTHC\Test\Helper::xsl_transform($result_file, $output_file);

		$this->config_remove();

	}

}
