<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop\Configuration as EshopConfiguration, Eshop\Language, Event\Variables, Strict, Event\DataLoader};
use PrestaShop\PrestaShop\Adapter\Configuration;

class Shop implements DataLoader
{
	use Strict;

	private EshopConfiguration $eshop_configuration;

	private Configuration $configuration;

	private Language $language;


	public function __construct(EshopConfiguration $eshop_configuration, Configuration $configuration, Language $language)
	{
		$this->eshop_configuration = $eshop_configuration;
		$this->configuration = $configuration;
		$this->language = $language;
	}


	public function load(Variables $variables, array $parameters = []): void
	{
		$variables['shop_id'] = 0;
		$variables['shop_email'] =  $this->configuration->get("PS_SHOP_EMAIL", "@");
		$variables['shop_name'] = $this->eshop_configuration->name();
		$variables['shop_domain'] = $this->eshop_configuration->url();

		/*if (!isset($variables['lang_id']))
		{
			$variables['lang_id'] = $this->language->get();
		}*/
	}
}
