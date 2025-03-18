<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Event;

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use ArrayAccess;
use BulkGate\Plugin\Strict;

class Helpers
{
	use Strict;

	/**
	 * @param array<array-key, mixed> $priority
	 * @param ArrayAccess<array-key, mixed> $values
	 * @param mixed $default
	 * @return mixed
	 */
	public static function priorityValues(array $priority, ArrayAccess $values, $default = null)
	{
		foreach ($priority as $key)
		{
			if (isset($values[$key]))
			{
				return $values[$key];
			}
		}

		return $default;
	}
}
