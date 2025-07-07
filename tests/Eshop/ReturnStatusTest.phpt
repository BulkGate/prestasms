<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Eshop\Test;

require_once __DIR__ . '/../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\PrestaShop\Eshop\ReturnStatus;
use PrestaShop\PrestaShop\Adapter\{OrderReturnState\OrderReturnStateDataProvider, Employee\ContextEmployeeProvider};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class ReturnStatusTest extends TestCase
{
	public function testLoad(): void
	{
		$returnStatus = new ReturnStatus($return_state = Mockery::mock(OrderReturnStateDataProvider::class), $employee = Mockery::mock(ContextEmployeeProvider::class));
		$employee->shouldReceive('getLanguageId')->once()->andReturn(8);
		$return_state->shouldReceive('getOrderReturnStates')->with(8)->once()->andReturn([
			['id_order_return_state' => 1, 'name' => 'Čeká na schválení'],
			['id_order_return_state' => 2, 'name' => 'Vyřízeno'],
		]);

		Assert::same([
			1 => 'Čeká na schválení',
			2 => 'Vyřízeno',
		], $returnStatus->load());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ReturnStatusTest())->run();

