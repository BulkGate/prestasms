<?php declare(strict_types=1);

namespace BulkGate\Plugin\Localization;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use DateTime, Exception;
use Locale, IntlDateFormatter, NumberFormatter;
use BulkGate\Plugin\{Strict, Utils\Strings};
use function date_default_timezone_get, is_integer;

class FormatterIntl implements Formatter
{
	use Strict;

	private string $locale;

	public function __construct(string $language, ?string $country = null)
	{
		$this->locale = $language . ($country !== null ? '_' . Strings::upper($country) : '');
	}


	/**
	 * @param string $type 'date', 'time', 'datetime', 'price', 'number', 'country'
	 * @param scalar|null|mixed $value
	 * @param mixed ...$parameters
	 */
	public function format(string $type, $value, ...$parameters): ?string
	{
		try
		{
			switch ($type)
			{
				case 'date':
					return (new IntlDateFormatter(
						$this->locale,
						IntlDateFormatter::MEDIUM,
						IntlDateFormatter::NONE,
						$parameters[0] ?? date_default_timezone_get(),
						IntlDateFormatter::GREGORIAN
					))->format(new DateTime(is_integer($value) ? "@$value" : $value)) ?: null;
				case 'time':
					return (new IntlDateFormatter(
						$this->locale,
						IntlDateFormatter::NONE,
						IntlDateFormatter::SHORT,
						$parameters[0] ?? date_default_timezone_get(),
						IntlDateFormatter::GREGORIAN
					))->format(new DateTime(is_integer($value) ? "@$value" : $value)) ?: null;
				case 'datetime':
					return (new IntlDateFormatter(
						$this->locale,
						IntlDateFormatter::MEDIUM,
						IntlDateFormatter::SHORT,
						$parameters[0] ?? date_default_timezone_get(),
						IntlDateFormatter::GREGORIAN
					))->format(new DateTime(is_integer($value) ? "@$value" : $value)) ?: null;
				case 'price':
					$value = (new NumberFormatter(
						$this->locale,
						NumberFormatter::CURRENCY
					))->formatCurrency((float)$value, $parameters[0] ?? 'EUR');

					return $value !== false ? $value : null;
				case 'number':
					$value = (new NumberFormatter(
						$this->locale,
						NumberFormatter::DECIMAL
					))->format((float)$value);

					return $value !== false ? $value : null;
				case 'country':
					return Locale::getDisplayRegion("-$value", $this->locale) ?: $value;
			}
		}
		catch (Exception $e)
		{
		}

		return null;
	}
}
