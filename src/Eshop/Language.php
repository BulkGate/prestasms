<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

use BulkGate\Plugin\Eshop;
use BulkGate\Plugin\Strict;
use Language as PrestaShopLanguage;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class Language implements Eshop\Language
{
    use Strict;

    public function load(): array
    {
        $output = [];

        foreach (PrestaShopLanguage::getLanguages() as ['iso_code' => $iso, 'name' => $name]) {
            $output[$iso] = $name;
        }

        return $output;
    }

    public function get(?int $id = null): string
    {
        if ($id === null) {
            return 'en';
        }

        /** @var mixed $iso */
        $iso = PrestaShopLanguage::getIsoById($id);

        if (\is_string($iso)) {
            return $iso;
        }

        return 'en';
    }

    public function hasMultiLanguageSupport(): bool
    {
        return true;
    }
}
