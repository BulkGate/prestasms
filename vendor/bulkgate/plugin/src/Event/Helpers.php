<?php declare(strict_types=1);

namespace BulkGate\Plugin\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;

class Helpers
{
	use Strict;

	/**
	 * @param array<string, string|null> $primary
	 * @param array<string, string|null> $secondary
	 */
	public static function address(string $key, array $primary, array $secondary): ?string
	{
		if (!empty($primary[$key]))
		{
			return $primary[$key];
		}
		else if (!empty($secondary[$key]))
		{
			return $secondary[$key];
		}
		return null;
	}


	/**
	 * @param array<string, string|null> $primary
	 * @param array<string, string|null> $secondary
	 */
	public static function joinStreet(string $street1, string $street2, array $primary, array $secondary): ?string
	{
		if (!empty($primary[$street1]))
		{
			return $primary[$street1] . (!empty($primary[$street2]) ? ', ' . $primary[$street2] : '');
		}
		else if (!empty($secondary[$street1]))
		{
			return $secondary[$street1] . (!empty($secondary[$street2]) ? ', ' . $secondary[$street2] : '');
		}
		return null;
	}
}
