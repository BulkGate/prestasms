<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Eshop\Test;

require_once __DIR__ . '/../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\PrestaShop\Eshop\MultiStore;
use PrestaShop\PrestaShop\Core\Multistore\MultistoreContextCheckerInterface;

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class MultiStoreTest extends TestCase
{
	public function testLoad(): void
	{
		$multiStore = new MultiStore($checker = Mockery::mock(MultistoreContextCheckerInterface::class));
		$checker->shouldReceive('getShops')->once()->andReturn([
			['id_shop' => 1, 'name' => 'Shop 1'],
			['id_shop' => 2, 'name' => 'Shop 2'],
		]);

		Assert::same([
			1 => 'Shop 1',
			2 => 'Shop 2',
		], $multiStore->load());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new MultiStoreTest())->run();

