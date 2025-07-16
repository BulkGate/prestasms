<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Test;

require_once __DIR__ . '/../../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Eshop\Language, Plugin\Event\Variables, PrestaShop\Event\Loader\Shop};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class ShopTest extends TestCase
{
	public function testLoad(): void
	{
		$prestashop_shop = Mockery::mock('overload:Shop');
		$prestashop_shop->shouldReceive('__construct')->set('id', 451)->set('name', 'PrestaShop Development');
		$prestashop_shop->shouldReceive('getBaseURL')->withNoArgs()->once()->andReturn('https://bulkgate.com/');

		$configuration = Mockery::mock('overload:Configuration');
		$configuration->shouldReceive('get')->with('PS_SHOP_EMAIL', null, null, 451)->once()->andReturn('xxx@bulkgate.com');
		$configuration->shouldReceive('get')->with('PS_SHOP_PHONE', null, null, 451)->once()->andReturn('+420777777777');
		$configuration->shouldReceive('get')->with('PS_CURRENCY_DEFAULT', null, null, 451)->once()->andReturn('7');
		$configuration->shouldReceive('get')->with('PS_LANG_DEFAULT', null, null, 451)->once()->andReturn('8');

		$currency = Mockery::mock('overload:Currency');
		$currency->shouldReceive('getIsoCodeById')->with(7)->once()->andReturn('CZK');

		$variables = new Variables(['shop_id' => 451]);

		$shop = new Shop($language = Mockery::mock(Language::class));
		$language->shouldReceive('get')->with(8)->once()->andReturn('cs');

		$shop->load($variables);

		Assert::same([
			'shop_id' => 451,
			'shop_email' => 'xxx@bulkgate.com',
			'shop_phone' => '+420777777777',
			'shop_currency' => 'CZK',
			'shop_name' => 'PrestaShop Development',
			'shop_domain' => 'https://bulkgate.com/',
			'lang_id' => '8',
			'language' => 'cs',
		], $variables->toArray());
	}


	public function testNotShopId(): void
	{
		$shop = new Shop(Mockery::mock(Language::class));
		$shop->load($variables = new Variables());

		Assert::same([], $variables->toArray());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ShopTest())->run();
