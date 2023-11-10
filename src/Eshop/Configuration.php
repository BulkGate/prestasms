<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Eshop};
use PrestaShop\PrestaShop\Adapter\Shop;

class Configuration implements Eshop\Configuration
{
	use Strict;

	/**
	 * @var array<string, string>
	 */
	private string $version_number;

	private string $site_url;

	private string $site_name;

	/**
	 * @param array<string, string> $plugin_data
	 */
	public function __construct(string $version_number, Shop\Url\BaseUrlProvider $url, Shop\Context $shop)
	{
        $this->version_number = $version_number;
		$this->site_url = $url->getUrl();
		$this->site_name = $shop->getShopName();
	}


	public function url(): string
	{
		return $this->site_url;
	}


	public function product(): string
	{
		return 'ps';
	}


	public function version(): string
	{
		return $this->version_number;
	}


	public function name(): string
	{
		return $this->site_name;
	}
}