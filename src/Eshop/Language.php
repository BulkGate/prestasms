<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Eshop;

class Language implements Eshop\Language
{

    public function load(): array
    {
        $output = [];

        foreach (\Language::getLanguages() as ['iso_code' => $iso, 'name' => $name]) {
            $output[$iso] = $name;
        }

        return $output;
    }

    public function get(?int $id = null): string
    {
		return \Language::getIsoById($id);
    }

    public function hasMultiLanguageSupport(): bool
    {
        return true;
    }
}
