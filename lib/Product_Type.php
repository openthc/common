<?php
/**
 * Inventory SKU Items
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

/*
METRC Attributes

[Name] => Whole Harvested Plant
[ProductCategoryType] => Plants
[QuantityType] => WeightBased
[DefaultLabTestingState] => NotSubmitted
[RequiresApproval] =>
[RequiresAdministrationMethod] =>
[RequiresStrain] => 1
[RequiresUnitCbdPercent] =>
[RequiresUnitCbdContent] =>
[RequiresUnitThcPercent] =>
[RequiresUnitThcContent] =>
[RequiresUnitVolume] =>
[RequiresUnitWeight] =>
[RequiresServingSize] =>
[RequiresSupplyDurationDays] =>
[UnitQuantityMultiplier] =>
[UnitQuantityUnitOfMeasureName] =>
[RequiresIngredients] =>
[RequiresProductPhoto] =>
[CanContainSeeds] =>
[CanBeRemediated] => 1

*/

class Product_Type extends \OpenTHC\SQL\Record
{

	const FLAG_B2B     = 0x00000001;
	const FLAG_B2C     = 0x00000002;

	const FLAG_WEIGHT  = 0x00000004;
	const FLAG_VOLUME  = 0x00000008;

	const FLAG_BULK    = 0x00000010;
	const FLAG_EACH    = 0x00000020;

	const FLAG_WANT_QA = 0x00000100;
	const FLAG_NEED_QA = 0x00000200;

	const FLAG_PLANT_SOURCE = 0x00001000;
	const FLAG_PLANT_OUTPUT = 0x00002000;

	const FLAG_MEDICAL = 0x00080000;

	const FLAG_MUTE    = 0x04000000;

	protected $_table = 'product_type';

	public static function map($t)
	{
		$product_type_list = [];
		$product_type_list['5'] = '018NY6XC00PTNPA4TPCYSKD5XN';
		$product_type_list['6'] = '018NY6XC00PTZZWCH7XVREHK6T';
		$product_type_list['7'] = '018NY6XC00PT3EZZ4GN6105M64';
		$product_type_list['9'] = '018NY6XC00PTGBW49J6YD3WM84';
		$product_type_list['10'] = '018NY6XC00PTY9THKSEQ8NFS1J';
		$product_type_list['11'] = '018NY6XC00PT2BKFPCEFB9G1Z2';
		$product_type_list['12'] = '018NY6XC00PTRPPDT8NJY2MWQW';
		$product_type_list['13'] = '018NY6XC00PTAF3TFBB51C8HX6';
		$product_type_list['14'] = '018NY6XC00PT8ZPGMPR8H2TAXH';
		$product_type_list['17'] = '018NY6XC00PTCS5AZV189X1YRK';
		$product_type_list['18'] = '018NY6XC00PTR9M5Z9S4T31C4R';
		$product_type_list['19'] = '018NY6XC00PTHP9NMJ1RE6TA62';
		$product_type_list['21'] = '018NY6XC00PTY5XPA4KJT6W3K4';
		$product_type_list['22'] = '018NY6XC00PTBNDY5VJ8JQ6NKP';
		$product_type_list['23'] = '018NY6XC00PT7N83PFNCX8ZFEF';
		$product_type_list['24'] = '018NY6XC00PTSF5NTC899SR0JF';
		$product_type_list['25'] = '018NY6XC00PT0WQP2XV5KNP395';
		$product_type_list['26'] = '018NY6XC00PTHE7GWB4QTG4JKZ';
		$product_type_list['27'] = '018NY6XC00PT8AXVZGNZN3A0QT';
		$product_type_list['28'] = '018NY6XC00PTGMB39NHCZ8EDEZ';
		$product_type_list['30'] = '018NY6XC00PT63ECNBAZH32YC3';
		$product_type_list['31'] = '018NY6XC00PTKYYGMRSKV4XNH7';
		$product_type_list['32'] = '018NY6XC00PTGRX4Q9SZBHDA5Z';
		$product_type_list['33'] = '018NY6XC00PTFY48D1136W0S0J';
		$product_type_list['34'] = '018NY6XC00PT25F95HPG583AJB';
		$product_type_list['35'] = '018NY6XC00PTD9Q4QPFBH0G9H2';
		$product_type_list['36'] = '018NY6XC00PTHPB8YG56S0MCAC';
		$product_type_list['37'] = '018NY6XC00PTBJ3G5FDAJN60EX';
		$product_type_list['39'] = '018NY6XC00PR0DUCTTYPE5BV22'; // Usable Trim == Grade-B / Packaged
		$product_type_list['40'] = '018NY6XC00PR0DUCTTYPE7FH3Z';
		$product_type_list['41'] = '018NY6XC00PR0DUCTTYPEF14Q4';

		return new self($product_type_list[ $t ]);

	}

	/**
	 * Need to embed in data-model some how
	 */
	function getMaxWeight()
	{
		$ret = -1; // no max

		// Nasty Hack
		// @todo Detect if Medical too, makes a 3lb limit in some places
		switch ($this->_data['id']) {
		// case '018NY6XC00PTZZWCH7XVREHK6T': // Flower/Net
		case '018NY6XC00PTAF3TFBB51C8HX6': // 5lb Flower Lot
		case 30:  // BT:Marijuana Mix
		case '018NY6XC00PTAF3TFBB51C8HX6': // LD:Harvest Materials/Flower Lots
		case '018NY6XC00PT63ECNBAZH32YC3': // LD:Intermediate Product/Marijuana Mix
			if ('usa/ok' == $_SESSION['cre']['id']) {
				return 4535.9; // 10LB
			}
			return 22679.0; // 50LB
		// case '018NY6XC00PTGBW49J6YD3WM84': // Other Plant Material
		case '018NY6XC00PT8ZPGMPR8H2TAXH': // Other Material Lot
		// case '018NY6XC00PTGBW49J6YD3WM84': // LD:Harvest Materials/Other Material
			return 6803.8; // 15 LB
		}
	}

}
