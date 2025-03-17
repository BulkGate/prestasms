<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Localization;

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Localization\{Formatter, FormatterBasic, FormatterIntl};

class FormatterFactory
{
	public static function create(string $language): Formatter
	{
		if (extension_loaded('intl'))
		{
			return new FormatterIntl($language);
		}
		else
		{
			return new FormatterBasic();
		}
	}
}
