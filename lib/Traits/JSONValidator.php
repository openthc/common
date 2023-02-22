<?php
/**
 * JSON Validator
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Common\Traits;

// use Opis\JsonSchema\Validator;
use Swaggest\JsonSchema\Schema;

trait JSONValidator
{
	/**
	 *
	 */
	function validateJSON($source_json, $json_schema)
	{
		$jsv = Schema::import($json_schema);
		try {
			$res_json = $jsv->in($source_json);
		} catch (\Exception $e) {
			__exit_text($e->getMessage(), 500);
		}
	}
}


		// $schema = Schema::import($schema_spec);
		// try {
		// 	$res_json = $schema->in($source_data);
		// } catch (\Exception $e) {
		// 	__exit_text($e->getMessage(), 500);
		// }

		// $validator = new Validator();
		// $res_json = $validator->validate($source_data, $schema_spec);
		// if ( ! $res_json->isValid()) {
		// 	__exit_text($res_json->error()->__toString(), 500);
		// }
