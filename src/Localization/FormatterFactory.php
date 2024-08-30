<?php

namespace BulkGate\PrestaSms\Localization;

use BulkGate\Plugin\Localization\Formatter;
use BulkGate\Plugin\Localization\FormatterBasic;
use BulkGate\Plugin\Localization\FormatterIntl;

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
