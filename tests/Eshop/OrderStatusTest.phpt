<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Eshop\Test;

require_once __DIR__ . '/../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\PrestaShop\Eshop\OrderStatus;
use PrestaShop\PrestaShop\Adapter\{OrderState\OrderStateDataProvider, Employee\ContextEmployeeProvider};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class OrderStatusTest extends TestCase
{
	public function testLoad(): void
	{
		$orderStatus = new OrderStatus($order_state = Mockery::mock(OrderStateDataProvider::class), $employee = Mockery::mock(ContextEmployeeProvider::class));
		$employee->shouldReceive('getLanguageId')->once()->andReturn(8);
		$order_state->shouldReceive('getOrderStates')->with(8)->once()->andReturn([
			['id_order_state' => 1, 'name' => 'Nová objednávka'],
			['id_order_state' => 2, 'name' => 'Odesláno'],
		]);

		Assert::same([
			1 => 'Nová objednávka',
			2 => 'Odesláno',
		], $orderStatus->load());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new OrderStatusTest())->run();

