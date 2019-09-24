<?php
/**
 * License Type Model
 */

namespace OpenTHC;

class License_Type
{
	const FLAG_PRODUCER   = 0x00000010;
	const FLAG_PROCESSOR  = 0x00000020;
	const FLAG_LABORATORY = 0x00000040;
	const FLAG_RETAIL     = 0x00000080;

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
			$x = 'G';
			break;
		case 'CULTIVATOR_PRODUCTION':
			$x = 'G,P';
			break;
		case 'DISPENSARY': // Retailer in MJF/LD
			$x = 'R';
			break;
		case 'LABORATORY':
		case 'MARIJUANA TESTING FACILITY': // Oregon?
			$x = 'QA';
			break;
		case 'M':
			$x = 'P';
			break;
		case 'MARIJUANA PROCESSOR':
			$x = 'P';
			break;
		case 'MARIJUANA PRODUCER TIER 1':
			$x = 'G1';
			break;
		case 'MARIJUANA PRODUCER TIER 2':
			$x = 'G2';
			break;
		case 'MARIJUANA PRODUCER TIER 3':
			$x = 'G3';
			break;
		case 'MARIJUANA RETAILER':
			$x = 'R';
			break;
		case 'MARIJUANA TRANSPORTATION':
			$x = 'C';
			break;
		case 'MEDICAL MARIJUANA':
			$x = '+MMJ';
			break;
		case 'PRODUCTION': // This is a Processor Only Type in LeafData
			$x = 'P';
			break;
		case 'TRANSPORTER':
			$x = 'C';
			break;
		}

		return $x;
	}
}
