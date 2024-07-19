<?php declare(strict_types=1);

namespace BulkGate\Plugin\Localization;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use function date, strtotime, sprintf, number_format, is_integer;

class FormatterBasic implements Formatter
{
	/**
	 * @param string $type 'date', 'time', 'datetime', 'price', 'number', 'country'
	 * @param scalar|null|mixed $value
	 * @param mixed ...$parameters
	 */
	public function format(string $type, $value, ...$parameters): ?string
	{
		switch ($type)
		{
			case 'date':
				return $this->formatDate('d. n. Y', $value);
			case 'time':
				return $this->formatDate('H:i', $value);
			case 'datetime':
				return $this->formatDate('d. n. Y H:i', $value);
			case 'price':
				return sprintf("%0.2f %s", $value, $parameters[0] ?? 'EUR');
			case 'number':
				return number_format((float)$value, $parameters[0] ?? 2);
			case 'country':
				return $value;
			default:
				return null;
		}
	}


	/**
	 * @param string|int $value
	 */
	private function formatDate(string $format, $value): ?string
	{
		$value = is_integer($value) ? $value : (strtotime($value) ?: null);

		if ($value === null)
		{
			return null;
		}

		return date($format, $value);
	}
}