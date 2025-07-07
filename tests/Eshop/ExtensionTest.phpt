<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Eshop\Test;

require_once __DIR__ . '/../bootstrap.php';

use Hook;
use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Event\Variables, PrestaShop\Event\Loader\Extension, Plugin\Database\Connection};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class ExtensionTest extends TestCase
{
	public function testLoad(): void
	{
		$hook = Mockery::mock('overload:' . Hook::class);

		$hook->shouldReceive('exec')->with('actionPrestaSmsExtendsVariables', [
			'variables' => $variables = new Variables(['shop_id' => 451]),
			'database' => $connection = Mockery::mock(Connection::class),
		], null, false, true, false, 451)->once()->andReturnNull();

		$loader = new Extension($connection);

		$loader->load($variables);

		Assert::same(['shop_id' => 451], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ExtensionTest())->run();

