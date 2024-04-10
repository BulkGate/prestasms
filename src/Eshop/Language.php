<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Eshop;
use PrestaShop\PrestaShop\Adapter;


class Language implements Eshop\Language
{
	private Adapter\Language\LanguageDataProvider $language;

    public function __construct(Adapter\Language\LanguageDataProvider $language)
    {
        $this->language = $language;
    }

    public function load(): array
	{
        $output = [];

        foreach($this->language->getLanguages() as ['iso_code' => $iso, 'name' => $name])
        {
            $output[$iso] = $name;
        }

		return $output;
	}


	public function get(?int $id = null): string
	{
		throw new \Exception("todo: get iso from order_id");
        //return (string) $this->context->getContext()->language->getIsoById($id);
	}

	public function hasMultiLanguageSupport(): bool
	{
		return true;
	}
}
