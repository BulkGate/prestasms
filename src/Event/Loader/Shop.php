<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Eshop\Language;
use BulkGate\Plugin\Event\DataLoader;
use BulkGate\Plugin\Event\Variables;
use BulkGate\Plugin\Strict;

class Shop implements DataLoader
{
    use Strict;

    private Language $language;

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    public function load(Variables $variables, array $parameters = []): void
    {
		if (isset($variables['shop_id']))
		{
			$shop = new \Shop($variables['shop_id']);

			$variables['shop_email'] = \Configuration::get('PS_SHOP_EMAIL', null, null, $shop->id) ?: null;
			$variables['shop_phone'] = \Configuration::get('PS_SHOP_PHONE', null, null, $shop->id) ?: null;
			$variables['shop_name'] = $shop->name;
			$variables['shop_domain'] = $shop->getBaseURL();
		}

        if (isset($variables['lang_id']))
        {
            $variables['lang_iso'] = $this->language->get($variables['lang_id']);
        }
    }
}
