<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;

class Helpers
{
	use Strict;

	public static function priorityValues(array $priority, \ArrayAccess $values, $default = null)
	{
		foreach ($priority as $key)
		{
			if ($values[$key] ?? false)
			{
				return $values[$key];
			}
		}

		return $default;
	}
}
