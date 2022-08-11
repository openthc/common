<?php
/**
 * Product/CPC/SKU Items
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Product extends \OpenTHC\SQL\Record
{
	const TABLE = 'product';

	const FLAG_MUTE       = 0x04000000;

	const FLAG_DEAD       = 0x08000000;
	const FLAG_DELETED    = 0x08000000;

	protected $_table = 'product';

	// static function countActive()
	// {
	// 	$sql = 'SELECT count(id)';
	// 	$sql.= ' FROM product';
	// 	$sql.= ' WHERE product.license_id = :l AND product.flag = 0';
	// 	$arg = array(':l' => $_SESSION['License']['id']);
	// 	$res = SQL::fetch_one($sql, $arg);
	// 	return $res;
	// }

	// static function findByGUID($x)
	// {
	// 	// New Faster Lookup
	// 	$sql = 'SELECT product.*';
	// 	$sql.= ' FROM product';
	// 	$sql.= ' WHERE product.guid = ?';
	// 	$arg = array($x);
	// 	$res = SQL::fetch_row($sql, $arg);
	// 	if (!empty($res)) {
	// 		$r = new self($res);
	// 		return $r;
	// 	}
	// }

	/**
	*/
	// static function findByName($x, $l)
	// {
	// 	// New Faster Lookup
	// 	$sql = 'SELECT product.*';
	// 	$sql.= ' FROM product';
	// 	$sql.= ' WHERE product.license_id = ? AND product.name = ?';
	// 	$arg = array($l, $x);
	// 	$res = SQL::fetch_row($sql, $arg);
	// 	if (!empty($res)) {
	// 		$r = new self($res);
	// 		return $r;
	// 	}
	// }

	/**
	 * @param string $fmt Format of 'nice*' | 'norm'
	 */
	function getPackageWeight($fmt=null)
	{
		$q = 0;
		$w = 0;
		$u = 'ea';

		if (!empty($this->_data['package_dose_qty'])) {

			// Calc Based on This
			$q = $this->_data['package_dose_qty'];
			$w = $this->_data['package_dose_qom'];
			$u = 'g';

			switch ($this->_data['package_dose_uom']) {
			case 'mg':
				$w = UOM::mg_to_g($w);
				$u = 'g';
				break;
			default:
				throw new Exception('Invalid Dosage UOM [ALP#068]');
			}

		} else {

			$mode = sprintf('%s/%s', $this->_data['package_pack_uom'], $this->_data['package_unit_uom']);
			switch ($mode) {
			case '/':
			case '/ea':
			case 'ea/':
			case 'ea/ea':
				$q = $this->_data['package_pack_qom'];
				$w = $this->_data['package_unit_qom'];
				$u = 'ea';
				break;
			case 'ea/kg':
				$q = $this->_data['package_pack_qom'];
				$w = $this->_data['package_unit_qom'] * 1000;
				$u = 'g';
				break;
			case '/Grams':
			case '/g':
			case 'ea/g':
			case 'ea/Grams':
			case 'ea/l':
			case 'ea/ml':
			case 'Each/Grams':
				$q = $this->_data['package_pack_qom'];
				$w = $this->_data['package_unit_qom'];
				$u = 'g';
				break;
			case 'ea/lb':
				$q = $this->_data['package_pack_qom'];
				$w = UOM::lb_to_g($this->_data['package_unit_qom']);
				$u = 'g';
				break;
			case '/Ounces':
			case 'ea/oz':
				$q = $this->_data['package_pack_qom'];
				$w = UOM::oz_to_g($this->_data['package_unit_qom']);
				$u = 'g';
				break;
			case 'ea/mg':
				$q = $this->_data['package_pack_qom'];
				$w = UOM::mg_to_g($this->_data['package_unit_qom']);
				$u = 'g';
				break;
			default:
				var_dump($mode);
				throw new \Exception('Invalid Package [ALP#083]');
			}

			// var_dump($this->_data);
			// exit;

		}

		// $this['package_full_qom'] = $w * $q;
		// $this['package_full_uom'] = $u;

		return sprintf('%0.2f %s', $w * $q, $u);

		// switch ($this->_data['package_unit_uom']) {
		// case 'g':
		// 	return $this->_data['package_unit_qom'] * $this->_data['package_pack_qom'];
		// case 'mg':
		// 	return $this->_data['package_unit_qom'] * $this->_data['package_pack_qom'];
		// default:
		// 	var_dump($this->_data);
		// 	exit;
		// }
	}

	/**
	 *
	 */
	function addMedia($x) : bool
	{
		$media_path = Company::getPath('/product');

	}


	/**
	 *
	 */
	function getMediaList() : array
	{
		// $img_list

		return [];
	}

	/**
	 * Evaluate this Object for Consistency with the CRE Needs
	 * @return [type] [description]
	 */
	function review()
	{
		$m = $this->getMeta();

		$ret = array(
			'action' => 'ignore',
			'detail' => array(),
		);

		// Ignore These
		switch ($m['type']) {
		case 'waste':
			return $ret;
		}

		$bad_words = array(
			// LD noise words
			'propagation material',
			'mature plant',
			// METRC noise words
			'Seeds (weight)',
			'Shake/Trim',
		);
		foreach ($bad_words as $w) {
			if (strpos($this->_data['name'], $w) !== false) {
				$ret['action'] = 'update';
				$ret['detail'][] = "Update Name to remove '$w'";
			}
		}


		$x = sprintf('%s/%s', $m['type'], $m['intermediate_type']);
		switch ($x) {
		// Bulk, Each, Each
		case 'immature_plant/clones':
		case 'immature_plant/plants':
		case 'immature_plant/seeds':
		case 'mature_plant/mature_plant':

			if ('bulk' != $this->_data['package_type']) {
				$ret['detail'][] = 'Update WT Package Type to "bulk"';
				$this->_data['package_type'] = 'bulk';
			}

			if (1 != $this->_data['package_pack_qom']) {
				$ret['detail'][] = 'Update WT Package Bulk/Pack QOM=1';
				$this->_data['package_pack_qom'] = 1;
			}
			if ('ea' != $this->_data['package_pack_uom']) {
				$ret['detail'][] = 'Update WT Package Bulk/Pack UOM=ea';
				$this->_data['package_pack_uom'] = 'ea';
			}

			if (1 != $this->_data['package_unit_qom']) {
				$ret['detail'][] = 'Update WT Package Bulk/Unit QOM=1';
				$this->_data['package_unit_qom'] = 1;
			}

			if ('ea' != $this->_data['package_unit_uom']) {
				$ret['detail'][] = 'Update WT Package Bulk/Unit QOM=ea';
				$this->_data['package_unit_uom'] = 'ea';
			}

			// LeafData
			if ('ea' != $m['uom']) {
				$ret['detail'][] = 'Update LD UOM to "ea"';
			}

			break;

		// Bulk, Gram, Gram
		case 'harvest_materials/flower':
		case 'harvest_materials/flower_lots':
		case 'harvest_materials/other_material':
		case 'harvest_materials/other_material_lots':
		case 'intermediate_product/ethanol_concentrate':
		case 'intermediate_product/food_grade_solvent_concentrate':
		case 'intermediate_product/hydrocarbon_concentrate':
		// case 'intermediate_product/liquid_edible':
		case 'intermediate_product/non-solvent_based_concentrate':

			if ('bulk' != $this->_data['package_type']) {
				$ret['detail'][] = 'Update WT Package Type to "bulk"';
				$this->_data['package_type'] = 'bulk';
			}

			if (1 != $this->_data['package_pack_qom']) {
				$ret['detail'][] = 'Update WT Package Bulk/Pack QOM=1';
				$this->_data['package_pack_qom'] = 1;
			}
			if ('ea' != $this->_data['package_pack_uom']) {
				$ret['detail'][] = 'Update WT Package Bulk/Pack UOM=ea';
				$this->_data['package_pack_uom'] = 'ea';
			}

			if ('g' != $this->_data['package_unit_uom']) {
				$this->_data['package_unit_uom'] = 'g';
				// $ret['detail'][] = 'Update WT Package Bulk/Unit UOM=g';
			}

			if ('gm' != $m['uom']) {
				$ret['detail'][] = 'Update LD UOM to "gm"';
			}

			break;

		// Each, Gram, Each
		case 'end_product/concentrate_for_inhalation':
		case 'end_product/infused_mix':
		case 'end_product/packaged_marijuana_mix':
		case 'end_product/usable_marijuana':

			if ('g' != $this->_data['package_unit_uom']) {
				$ret['detail'][] = 'Update WT Package Each/Unit UOM to "g"';
				$this->_data['package_unit_uom'] = 'g';
			}

			if ('ea' != $m['uom']) {
				$ret['detail'][] = 'Update LD UOM to "ea"';
			}

			$w0 = floatval($m['net_weight']);
			$w1 = floatval($m['total_marijuana_in_grams']);
			$w2 = floatval($m['weight_per_unit_in_grams']);

			if (empty($w0) && empty($w1) && empty($w2)) {
				$ret['detail'][] = 'LeafData Package Weight Error, Re-Save';
			}

			break;

		// Pack, Milligram, Each
		case 'end_product/capsules':
		case 'end_product/liquid_edible':
		case 'end_product/solid_edible':
		case 'end_product/suppository':
		case 'end_product/tinctures':
		case 'end_product/topical':
		case 'end_product/transdermal_patches':

		// "liquid edible", "solid edible", "topical", "capsules", "tinctures", "transdermal patches", and "suppository" sub-categories, the "serving size" and "servings per unit" fields must be completed

			if (('each' != $this->_data['package_type']) && ('pack' != $this->_data['package_type'])) {
				$ret['detail'][] = 'Update WT Package Type to "each" or "pack"';
			}

			//if ('mg' != $this->_data['package_unit_uom']) {
			//	$ret['detail'][] = 'Update WT Package Unit UOM to "mg"';
			//}

			if ('ea' != $m['uom']) {
				$ret['detail'][] = 'Update LD UOM to "ea"';
			}

			$x0 = floatval($m['serving_num']);
			$x1 = floatval($m['serving_size']);

			if (empty($x0) && empty($x1)) {
				$ret['detail'][] = 'LeafData Serving Information';
			} elseif (empty($x0) && !empty($x1)) {
				$ret['detail'][] = 'LeafData Serving Count';
			} elseif (!empty($x0) && empty($x1)) {
				$ret['detail'][] = 'LeafData Serving Size';
			} else {
				// Good!
			}

			break;

		default:

			// die(sprintf('Invalid Product Type "%s" [ALP#143]', $x));
			// throw new Exception(sprintf('Invalid Product Type "%s" [ALP#143]', $x));

		}

		switch ($this->_data['package_type']) {
			case 'each':
				if (empty($this->_data['package_unit_qom'])) {
					$ret['detail'][] = 'Product is missing the Weight';
				}
		}

		// $x = $this->_review_name();
		// if (!empty($x)) {
		// 	$ret['detail'][] = $x;
		// }

		if (count($ret['detail']) > 1) {
			$ret['action'] = 'update';
		}

		return $ret;

	}

	function _review_name()
	{

		switch ($this->_data['package_type']) {
		case 'each':

			$qom = null;
			$uom = null;

			if (preg_match('/^(.+) \- ([\d\.]+)\s?(ea|g|gm|mg|ml|oz)$/', $this->_data['name'], $m)) {

				// Perfect
				// $rec['_name'] = $m[1];
				$qom = $m[2];
				$uom = $m[3];

			} elseif (preg_match('/^(.+) ([\d\.]+)\s?(ea|g|gm|mg|ml|oz)$/', $this->_data['name'], $m)) {
				// Perfect
				// $rec['_name'] = $m[1];
				$qom = $m[2];
				$uom = $m[3];

			} elseif (preg_match('/([\d\.]+)\s?(ea|g|gm|mg|ml|oz)/', $this->_data['name'], $m)) {

				// $rec['name'] = $m[1];
				$qom = $m[2];
				$uom = $m[3];

				//echo "Format: $link '{$rec['name']}' to include weight\n";

			} else {
				//echo "Update: $link {$rec['mode']} '{$rec['name']}' to include weight in name\n";
				//$ret['detail'][] = 'Add Weight to Name for GreenBits';
			}

			// if ($rec['package_type'] != 'each') {
			// 	$rec['fail-mode'] = 'modify-package';
			// 	$rec['_note'][] = "Set {$rec['package_type']} to 'each'";
			// 	// _audit_table_row($rec, 'XX');
			// }

			// if (empty($rec['meta']['net_weight'])) {
			// 	$rec['fail-mode'] = 'modify-weight';
			// 	$rec['_note'][] = "LeafData Definition Missing Weight, Set to $qom / $uom";
			// 	//_audit_table_row($rec, 'XX');
			// }

		}
	}

}
