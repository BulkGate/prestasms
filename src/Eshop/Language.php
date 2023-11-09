<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Eshop;
use PrestaShop\PrestaShop\Adapter\LegacyContext;


class Language implements Eshop\Language
{
	private LegacyContext $context;

    public function __construct(LegacyContext $context)
    {
        $this->context = $context;
    }

    public function load(): array
	{
        $output = [];

        foreach($this->context->getLanguages() as ['iso_code' => $iso, 'name' => $name])
        {
            $output[$iso] = $name;
        }

		return $output;
	}


	public function get(?int $id = null): string
	{
		return (string) $this->context->getContext()->language->getIsoById($id);
	}
}
