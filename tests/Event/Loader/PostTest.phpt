<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Test;

require_once __DIR__ . '/../../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Event\Variables, PrestaShop\Event\Loader\Post};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class PostTest extends TestCase
{
	public function testLoad(): void
	{
		$tools = Mockery::mock('overload:Tools');
		$tools->shouldReceive('getValue')->with('phone_number')->once()->andReturn('123456789');
		$tools->shouldReceive('getValue')->with('phone_mobile')->once()->andReturn('987654321');

		$_POST = [
			'phone_number' => '123456789',
			'phone_mobile' => '987654321',
		];

		$variables = new Variables();
		$loader = new Post();
		$loader->load($variables);

		Assert::same([
			'customer_phone' => '123456789',
			'customer_mobile' => '987654321',
		], $variables->toArray());
	}

	public function testEmptyPost(): void
	{
		$_POST = [];
		$variables = new Variables();
		$loader = new Post();
		$loader->load($variables);
		Assert::same([], $variables->toArray());
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new PostTest())->run();
