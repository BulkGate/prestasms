<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Event\Test;

require_once __DIR__ . '/../bootstrap.php';

use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Event\Variables, PrestaShop\Event\Helpers};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class HelpersTest extends TestCase
{
	public function testPriorityValues(): void
	{
		$variables = new Variables(['v1' => 1, 'v2' => 2, 'v3' => 3]);

		Assert::same('test', Helpers::priorityValues([], $variables, 'test'));
		Assert::same(3, Helpers::priorityValues(['v3'], $variables, 'test'));
		Assert::same(1, Helpers::priorityValues(['v1', 'v3'], $variables, 'test'));
		Assert::same(2, Helpers::priorityValues(['v2', 'v1', 'v3'], $variables, 'test'));
		Assert::same(2, Helpers::priorityValues(['cc', 'v2', 'v1', 'v3'], $variables, 'test'));
	}
}

(new HelpersTest())->run();
