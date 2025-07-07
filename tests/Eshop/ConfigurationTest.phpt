<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Eshop\Test;

require_once __DIR__ . '/../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\PrestaShop\Eshop\Configuration;
use PrestaShop\PrestaShop\Adapter\{Shop\Context, Shop\Url\BaseUrlProvider};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class ConfigurationTest extends TestCase
{
	public function testConfiguration(): void
	{
		$url_provider = Mockery::mock(BaseUrlProvider::class);
		$shop = Mockery::mock(Context::class);
		$url_provider->shouldReceive('getUrl')->once()->andReturn('https://eshop.cz/');
		$shop->shouldReceive('getShopName')->once()->andReturn('Můj Eshop');

		$config = new Configuration('8.1.0', $url_provider, $shop);

		Assert::same('https://eshop.cz/', $config->url());
		Assert::same('ps', $config->product());
		Assert::same('8.1.0', $config->version());
		Assert::same('Můj Eshop', $config->name());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ConfigurationTest())->run();

