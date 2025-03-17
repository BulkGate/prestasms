<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop\Language, Event\DataLoader, Event\Variables, Strict};

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
		if (!isset($variables['shop_id']))
		{
			return;
		}

		$shop = new \Shop($variables['shop_id']);

		$variables['shop_email'] = \Configuration::get('PS_SHOP_EMAIL', null, null, $shop->id) ?: null;
		$variables['shop_phone'] = \Configuration::get('PS_SHOP_PHONE', null, null, $shop->id) ?: null;
		$variables['shop_currency'] = \Currency::getIsoCodeById((int)\Configuration::get('PS_CURRENCY_DEFAULT', null, null, $shop->id));
		$variables['shop_name'] = $shop->name;
		$variables['shop_domain'] = $shop->getBaseURL();
		$variables['lang_id'] ??= \Configuration::get("PS_LANG_DEFAULT", null, null, $shop->id); //$shop->getAssociatedLanguage()->getId();
		$variables['lang_iso'] = $this->language->get((int)$variables['lang_id']);
	}
}
