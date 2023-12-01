<?php
/**
 * Stat Icon Note
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Traits;

trait StatIcon
{
	/**
	 * Get the Status Icon for this Object
	 */
	function getStatIcon(array $icon) : array
	{
		// Dedicated Icons for Status
		switch ($this->_data['stat'])
		{
			case 100:
				$icon[] = '<i class="text-secondary fa-regular fa-square-plus" title="New / Pending"></i>';
				break;
			case 102:
				$icon[] = '<i class="text-secondary fa-regular fa-circle-question" title="Processing"></i>';
				break;
			case 200:
				$icon[] = '<i class="text-success fa-regular fa-square-check" title="Active"></i>';
				break;
			case 308:
				$icon[] = '<i class="text-warning fa-solid fa-arrows-left-right" title="Moved"></i>';
				break;
			case 404:
			case 410:
				$icon[] = '<i class="text-danger fa-solid fa-ban" title="Closed"></i>';
				break;
			case 451:
				$icon[] = '<i class="fa-solid fa-building-lock" title="Blocked / Locked"></i>';
				break;
			default:
				$icon[] = sprintf('%d', $v['stat']);
		}

		return $icon;

	}


}
