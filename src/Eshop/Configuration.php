<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\Eshop;

use BulkGate\Plugin\Eshop;
use BulkGate\Plugin\Strict;
use PrestaShop\PrestaShop\Core\Shop\Url\UrlProviderInterface;
use PrestaShop\PrestaShop\Adapter\Shop;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class Configuration implements Eshop\Configuration
{
    use Strict;

    private string $version_number;

    private string $site_url;

    private string $site_name;

    public function __construct(string $version_number, UrlProviderInterface $url_provider, Shop\Context $shop)
    {
        $this->version_number = $version_number;
        $this->site_url = $url_provider->getUrl();
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
