<?php declare(strict_types=1);

namespace BulkGate\Plugin\Localization;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface Formatter
{
	/**
	 * @param mixed $value
	 * @param mixed ...$parameters
	 */
	public function format(string $type, $value, ...$parameters): ?string;
}
