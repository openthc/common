<?php
/**
 * Unit of Measure Handling
 * @see https://www.unitconverters.net/weight-and-mass/grams-to-ounces.htm
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace OpenTHC;

class UOM
{
	const G_IN_LB = 453.59237; // == 1 / 0.002204623
	const G_IN_OZ =  28.349523125; // == 1 / 0.03527396
	const L_IN_OZ =  33.814022702;

	private $_ini;
	private $_uom;

	function __construct($u)
	{
		$this->_ini = parse_ini_file(APP_ROOT . '/etc/uom.ini', true, INI_SCANNER_RAW);

		// Find by Slug / Code
		if (!empty($this->_ini[$u])) {
			$this->_uom = $this->_ini[$u];
			$this->_uom['stub'] = $u;
		}

		if (empty($this->_uom)) {
		// Find by First/Best Match
		foreach ($this->_ini as $k0 => $v0) {
			foreach ($v0 as $k1 => $v1) {
				if ($u == $v1) {
					$this->_uom = $this->_ini[$k0];
					$this->_uom['stub'] = $k0;
					break 2;
				}
			}
		}
		}

		if (empty($this->_uom)) {
			throw new Exception("Cannot Load Unit of Measure: ($u)");
		}

	}

	/**
	 * Get the UOM's name
	 */
	function getName($s=null)
	{
		return $this->_uom['name'];
	}

	/**
	 * Get the UOM's code
	 */
	function getCode($s=null)
	{
		return $this->_uom['stub'];
	}
	function getStub($s=null)
	{
		return $this->getCode($s);
	}

	/**
		Mass
	 */

	/**
	 * Try to Convert arbitrary Number of some Unit to Grams
	 */
	static function to_g($w, $to)
	{
		if (empty($to)) {
			return $w;
		}

		$r = false;
		switch ($to) {
			case 'ea':
			case 'g':
			case 'l':
				$r = $w;
				break;
			case 'kg':
				$r = $w * 1000;
				break;
			case 'mg':
				$r = $w / 1000;
				break;
			case 'lb':
				$r = self::lb_to_g($w);
				break;
			case 'oz':
				$r = self::oz_to_g($w);
				break;
		}

		return $r;

	}

	/**
	 * Try to Convert Number of Grams to a different unit
	 */
	static function g_to($g, $to)
	{
		$r = false;
		switch ($to) {
			case 'ea':
			case 'g':
			case 'l':
				$r = $g;
			break;
			case 'kg':
				$r = $g / 1000;
			break;
			case 'lb':
				$r = self::g_to_lb($g);
			break;
			case 'oz':
				$r = self::g_to_oz($g);
			break;
		}

		return $r;

	}

	/**
	 * @param $g Grams
	 * @return US-LB, Float
	*/
	static function g_to_lb($g)
	{
		return floatval($g) / self::G_IN_LB;
	}

	/**
	 * @param $g Grams
	 * @return US-OZ, Float
	 */
	static function g_to_oz($g)
	{
		return floatval($g) / self::G_IN_OZ;;
	}

	/**
	 * @param $g Grams
	 * @return array(LB, OZ);
	 */
	static function g_to_lb_oz($x)
	{
		$l = floor(self::g_to_lb($x));
		$o = self::g_to_oz($x - $l);
		return array($l, $o);
	}

	/**
	 * @param $x Pounds
	 * @return Float, $x in Grams
	 */
	static function lb_to_g($x)
	{
		return floatval($x) * self::G_IN_LB;
	}

	/**
	 * @param $x Miligrams
	 * @return Float, $x in Grams
	 */
	static function mg_to_g($x)
	{
		return floatval($x) / 1000;
	}

	/**
		Volume
	*/

	/**
	 * @param $x Mililiters
	 * @return Float, $x in US-OZ
	 */
	static function ml_to_oz($x)
	{
		return floatval($x) * 0.03381413;
		// return (floatval($x) * 1000) / self::L_IN_OZ;
	}

	/**
	 * @param $x US-OZ
	 * @return Float, $x in Grams
	 */
	static function oz_to_g($x)
	{
		return floatval($x) * self::G_IN_OZ;
	}

	/**
	 * @param $x US-OZ
	 * @return Float, $x in Mililiters
	 */
	static function oz_to_ml($x)
	{
		return floatval($x) * 29.5735296875;
		// return floatval($x) * self::L_IN_OZ
	}

}
