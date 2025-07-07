<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Test;

require_once __DIR__ . '/../../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Event\Variables, PrestaShop\Event\Loader\OrderStatus};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class OrderStatusTest extends TestCase
{
	public function testLoad(): void
	{
		$order_state = Mockery::mock('overload:OrderState');
		$order_state->shouldReceive('__construct')->with(5, 8)->once()->set('name', 'Shipped');

		$variables = new Variables([
			'order_status_id' => 5,
			'lang_id' => 8,
		]);

		$loader = new OrderStatus();
		$loader->load($variables);

		Assert::same([
			'order_status_id' => 5,
			'lang_id' => 8,
			'order_status' => 'Shipped',
		], $variables->toArray());
	}

	public function testNotOrderStatusId(): void
	{
		$variables = new Variables(['lang_id' => 8]);
		$loader = new OrderStatus();
		$loader->load($variables);

		Assert::same(['lang_id' => 8], $variables->toArray());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new OrderStatusTest())->run();

