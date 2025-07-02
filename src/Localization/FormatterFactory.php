<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Localization;

use BulkGate\Plugin\Localization\Formatter;
use BulkGate\Plugin\Localization\FormatterBasic;
use BulkGate\Plugin\Localization\FormatterIntl;

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class FormatterFactory
{
    public static function create(string $language): Formatter
    {
        if (extension_loaded('intl')) {
            return new FormatterIntl($language);
        } else {
            return new FormatterBasic();
        }
    }
}
