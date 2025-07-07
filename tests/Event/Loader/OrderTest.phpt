<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Test;

require_once __DIR__ . '/../../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Event\Variables, PrestaShop\Event\Loader\Order, Plugin\Localization\Formatter};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class OrderTest extends TestCase
{
	public function testLoad(): void
	{
		$order = Mockery::mock('overload:Order');
		$order->shouldReceive('__construct')->with(123)->set('id', 123)->set('id_currency', 7)->set('id_address_delivery', 1)->set('id_address_invoice', 2)->set('id_cart', 10)->set('id_carrier', 20)->set('payment', 'Bankwire')->set('total_paid', 999.99)->set('reference', 'ORD123')->set('date_add', '2025-07-04 12:34:56')->set('id_shop', 451);

		$currency = Mockery::mock('overload:Currency');
		$currency->shouldReceive('getCurrency')->with(7)->andReturn(['iso_code' => 'CZK']);

		$formatter = Mockery::mock(Formatter::class);
		$formatter->shouldReceive('format')->with('number', 999.99)->andReturn('999,99');
		$formatter->shouldReceive('format')->with('price', 999.99, 'CZK')->andReturn('999,99 CZK');
		$formatter->shouldReceive('format')->with('datetime', '2025-07-04 12:34:56')->andReturn('4.7.2025 12:34');
		$formatter->shouldReceive('format')->with('date', '2025-07-04 12:34:56')->andReturn('4.7.2025');
		$formatter->shouldReceive('format')->with('time', '2025-07-04 12:34:56')->andReturn('12:34');

		$carrier = Mockery::mock('overload:Carrier');
		$carrier->shouldReceive('__construct')->with(20, 8)->set('name', 'PPL')->set('url', 'https://track/@')->set('delay', 'Next day');
		$order_carrier = Mockery::mock('overload:OrderCarrier');
		$order_carrier->shouldReceive('__construct')->with(123, 8)->set('tracking_number', 'TRACK123')->set('date_add', '2025-07-04 13:00:00')->set('shipping_cost_tax_incl', 100.0)->set('weight', 2.5);
		$formatter->shouldReceive('format')->with('datetime', '2025-07-04 13:00:00')->andReturn('4.7.2025 13:00');
		$formatter->shouldReceive('format')->with('number', 100.0)->andReturn('100,00');
		$formatter->shouldReceive('format')->with('number', 2.5)->andReturn('2,50');
		$formatter->shouldReceive('format')->with('price', 100.0, 'CZK')->andReturn('100,00 CZK');

		$order_detail = Mockery::mock('overload:OrderDetail');
		$order_detail->shouldReceive('getList')->with(123)->andReturn([
			[
				'id_order_detail' => 1,
				'product_quantity' => 2,
				'product_name' => 'T-shirt',
				'product_reference' => 'TSHIRT',
				'product_id' => 101,
				'product_price' => 500.0,
			],
		]);
		$formatter->shouldReceive('format')->with('price', 500.0, 'CZK')->andReturn('500,00 CZK');

		$message = Mockery::mock('overload:Message');
		$message->shouldReceive('getMessagesByOrderId')->with(123)->andReturn(['message' => 'Děkujeme za objednávku']);

		$variables = new Variables([
			'order_id' => 123,
			'lang_id' => 8,
		]);

		$loader = new Order($formatter);
		$loader->load($variables);

		Assert::same([
			'order_id' => 123,
			'lang_id' => 8,
			'id_address_delivery' => 1,
			'id_address_invoice' => 2,
			'long_order_id' => '000123',
			'cart_id' => 10,
			'carrier_id' => 20,
			'order_payment' => 'Bankwire',
			'order_currency' => 'CZK',
			'order_total_paid' => '999,99',
			'order_total_locale' => '999,99 CZK',
			'order_reference' => 'ORD123',
			'order_datetime' => '4.7.2025 12:34',
			'order_date' => '4.7.2025',
			'order_date1' => '04.07.2025',
			'order_date2' => '04/07/2025',
			'order_date3' => '04-07-2025',
			'order_date4' => '2025-07-04',
			'order_date5' => '07.04.2025',
			'order_date6' => '07/04/2025',
			'order_date7' => '07-04-2025',
			'order_time' => '12:34',
			'order_time1' => '12:34',
			'order_carrier_name' => 'PPL',
			'order_carrier_url' => 'https://track/TRACK123',
			'order_carrier_delay' => 'Next day',
			'order_carrier_tracking_number' => 'TRACK123',
			'order_carrier_tracking_date' => '4.7.2025 13:00',
			'order_carrier_price' => '100,00',
			'order_carrier_weight' => '2,50',
			'order_carrier_price_locale' => '100,00 CZK',
			'order_message' => 'Děkujeme za objednávku',
			'order_products1' => '2x T-shirt TSHIRT',
			'order_products2' => '2x T-shirt',
			'order_products3' => '2x (101)T-shirt TSHIRT',
			'order_products4' => '2x TSHIRT',
			'order_products5' => "2x T-shirt TSHIRT",
			'order_products6' => "2x T-shirt",
			'order_products7' => "2x (101)T-shirt TSHIRT",
			'order_products8' => "2x TSHIRT",
			'order_smsprinter1' => '2,T-shirt,500,00 CZK',
			'order_smsprinter2' => '2;T-shirt;500,00 CZK',
			'order_smsprinter3' => '2,TSHIRT,500,00 CZK',
			'order_smsprinter4' => '2;TSHIRT;500,00 CZK',
		], $variables->toArray());
	}

	public function testNotOrderId(): void
	{
		$formatter = Mockery::mock(Formatter::class);
		$loader = new Order($formatter);
		$loader->load($variables = new Variables());
		Assert::same([], $variables->toArray());
	}

	public function testLoadWithReturn(): void
	{
		$order = Mockery::mock('overload:Order');
		$order->shouldReceive('__construct')->with(123)->set('id', 123)->set('id_currency', 7)->set('id_address_delivery', 1)->set('id_address_invoice', 2)->set('id_cart', 10)->set('id_carrier', 20)->set('payment', 'Bankwire')->set('total_paid', 999.99)->set('reference', 'ORD123')->set('date_add', '2025-07-04 12:34:56')->set('id_shop', 451);

		$currency = Mockery::mock('overload:Currency');
		$currency->shouldReceive('getCurrency')->with(7)->andReturn(['iso_code' => 'CZK']);

		$formatter = Mockery::mock(Formatter::class);
		$formatter->shouldReceive('format')->with('number', 999.99)->andReturn('999,99');
		$formatter->shouldReceive('format')->with('price', 999.99, 'CZK')->andReturn('999,99 CZK');
		$formatter->shouldReceive('format')->with('datetime', '2025-07-04 12:34:56')->andReturn('4.7.2025 12:34');
		$formatter->shouldReceive('format')->with('date', '2025-07-04 12:34:56')->andReturn('4.7.2025');
		$formatter->shouldReceive('format')->with('time', '2025-07-04 12:34:56')->andReturn('12:34');

		$carrier = Mockery::mock('overload:Carrier');
		$carrier->shouldReceive('__construct')->with(20, 8)->set('name', 'PPL')->set('url', 'https://track/@')->set('delay', 'Next day');
		$order_carrier = Mockery::mock('overload:OrderCarrier');
		$order_carrier->shouldReceive('__construct')->with(123, 8)->set('tracking_number', 'TRACK123')->set('date_add', '2025-07-04 13:00:00')->set('shipping_cost_tax_incl', 100.0)->set('weight', 2.5);
		$formatter->shouldReceive('format')->with('datetime', '2025-07-04 13:00:00')->andReturn('4.7.2025 13:00');
		$formatter->shouldReceive('format')->with('number', 100.0)->andReturn('100,00');
		$formatter->shouldReceive('format')->with('number', 2.5)->andReturn('2,50');
		$formatter->shouldReceive('format')->with('price', 100.0, 'CZK')->andReturn('100,00 CZK');

		$order_detail = Mockery::mock('overload:OrderDetail');
		$order_detail->shouldReceive('getList')->with(123)->andReturn([
			[
				'id_order_detail' => 1,
				'product_quantity' => 2,
				'product_name' => 'T-shirt',
				'product_reference' => 'TSHIRT',
				'product_id' => 101,
				'product_price' => 500.0,
			],
		]);
		$formatter->shouldReceive('format')->with('price', 500.0, 'CZK')->andReturn('500,00 CZK');

		$message = Mockery::mock('overload:Message');
		$message->shouldReceive('getMessagesByOrderId')->with(123)->andReturn(['message' => 'Děkujeme za objednávku']);

		$order_return = Mockery::mock('overload:OrderReturn');
		$order_return->shouldReceive('__construct')->with(55, 8, 451)->set('id', 55)->set('question', 'Chci vrátit zboží');
		$order_return->shouldReceive('getOrdersReturnProducts')->with(55, Mockery::type('object'))->andReturn([
			[
				'product_quantity' => 1,
				'product_name' => 'T-shirt',
				'product_reference' => 'TSHIRT',
				'product_id' => 101,
			],
		]);

		$variables = new Variables([
			'order_id' => 123,
			'lang_id' => 8,
			'return_id' => 55,
		]);

		$loader = new Order($formatter);
		$loader->load($variables);

		Assert::same([
			'order_id' => 123,
			'lang_id' => 8,
			'return_id' => 55,
			'id_address_delivery' => 1,
			'id_address_invoice' => 2,
			'long_order_id' => '000123',
			'cart_id' => 10,
			'carrier_id' => 20,
			'order_payment' => 'Bankwire',
			'order_currency' => 'CZK',
			'order_total_paid' => '999,99',
			'order_total_locale' => '999,99 CZK',
			'order_reference' => 'ORD123',
			'order_datetime' => '4.7.2025 12:34',
			'order_date' => '4.7.2025',
			'order_date1' => '04.07.2025',
			'order_date2' => '04/07/2025',
			'order_date3' => '04-07-2025',
			'order_date4' => '2025-07-04',
			'order_date5' => '07.04.2025',
			'order_date6' => '07/04/2025',
			'order_date7' => '07-04-2025',
			'order_time' => '12:34',
			'order_time1' => '12:34',
			'order_carrier_name' => 'PPL',
			'order_carrier_url' => 'https://track/TRACK123',
			'order_carrier_delay' => 'Next day',
			'order_carrier_tracking_number' => 'TRACK123',
			'order_carrier_tracking_date' => '4.7.2025 13:00',
			'order_carrier_price' => '100,00',
			'order_carrier_weight' => '2,50',
			'order_carrier_price_locale' => '100,00 CZK',
			'order_message' => 'Děkujeme za objednávku',
			'order_products1' => '2x T-shirt TSHIRT',
			'order_products2' => '2x T-shirt',
			'order_products3' => '2x (101)T-shirt TSHIRT',
			'order_products4' => '2x TSHIRT',
			'order_products5' => "2x T-shirt TSHIRT",
			'order_products6' => "2x T-shirt",
			'order_products7' => "2x (101)T-shirt TSHIRT",
			'order_products8' => "2x TSHIRT",
			'order_smsprinter1' => '2,T-shirt,500,00 CZK',
			'order_smsprinter2' => '2;T-shirt;500,00 CZK',
			'order_smsprinter3' => '2,TSHIRT,500,00 CZK',
			'order_smsprinter4' => '2;TSHIRT;500,00 CZK',
			'return_question' => 'Chci vrátit zboží',
			'return_products1' => '1x T-shirt TSHIRT',
			'return_products2' => '1x T-shirt',
			'return_products3' => '1x (101)T-shirt TSHIRT',
			'return_products4' => '1x TSHIRT',
			'return_products5' => "1x T-shirt TSHIRT",
			'return_products6' => "1x T-shirt",
			'return_products7' => "1x (101)T-shirt TSHIRT",
			'return_products8' => "1x TSHIRT",
		], $variables->toArray());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new OrderTest())->run();
