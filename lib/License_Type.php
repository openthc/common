<?php
/**
 * License Type Model
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class License_Type
{
	const FLAG_PRODUCER   = 0x00000010;
	const FLAG_PROCESSOR  = 0x00000020;
	const FLAG_LABORATORY = 0x00000040;
	const FLAG_RETAIL     = 0x00000080;
	const FLAG_CARRIER    = 0x00000100;

	/**
	 * Takes CRE Names and Maps to OpenTHC Types
	 * Should expand to use license-type models from API
	 */
	static function map($x)
	{
		switch (strtoupper($x)) {
		case 'CO-OP':
			$x = 'CO-OP';
			break;
		case 'CULTIVATOR':
			$x = 'Grower';
			break;
		case 'J':
		case 'CULTIVATOR_PRODUCTION':
			$x = 'Grower+Processor';
			break;
		case 'DISPENSARY': // Retailer in MJF/LD
		case 'MARIJUANA RETAILER':
			$x = 'Retail';
			break;
		case 'LABORATORY':
		case 'MARIJUANA TESTING FACILITY': // Oregon?
			$x = 'Laboratory';
			break;
		case 'M':
		case 'MARIJUANA PROCESSOR':
		case 'PRODUCTION': // This is a Processor Only Type in LeafData
			$x = 'Processor';
			break;
		case 'MARIJUANA PRODUCER TIER 1':
		case 'MARIJUANA PRODUCER TIER 2':
		case 'MARIJUANA PRODUCER TIER 3':
			$x = 'Grower';
			break;
		case 'MARIJUANA TRANSPORTATION':
		case 'TRANSPORTER':
			$x = 'Carrier';
			break;
		case 'MEDICAL MARIJUANA':
			$x = '+MMJ';
			break;
		}

		return $x;
	}

}
