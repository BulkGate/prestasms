<?php declare(strict_types=1);

namespace BulkGate\PrestaShop\Database\Test;

require_once __DIR__ . '/../bootstrap.php';

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\PrestaShop\Database\Connection;
use Doctrine\DBAL\{Connection as DBALConnection, ForwardCompatibility\Result};

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @testCase
 */
class ConnectionTest extends TestCase
{
	public function testExecuteAndPrepare(): void
	{
		$connection = new Connection($doctrine = Mockery::mock(DBALConnection::class));
		$doctrine->shouldReceive('executeQuery')->with('SELECT * FROM test WHERE id = ?', [451])->once()->andReturn($result = Mockery::mock(Result::class));
		$result->shouldReceive('fetchAll')->with(2)->once()->andReturn([['id' => 1, 'name' => 'test1'], ['id' => 2, 'name' => 'test2']]);

		$collection = $connection->execute($connection->prepare('SELECT * FROM test WHERE id = %s', 451));

		Assert::same(['id' => 1, 'name' => 'test1'], $collection[0]->toArray());
		Assert::same(['id' => 2, 'name' => 'test2'], $collection[1]->toArray());

		Assert::same(['SELECT * FROM test WHERE id = ?'], $connection->getSqlList());
	}


	public function testTableAndPrefix(): void
	{
		define('_DB_PREFIX_', 'ps_');
		$connection = new Connection(Mockery::mock(DBALConnection::class));

		Assert::same('ps_xxx', $connection->table('xxx'));

		Assert::same('ps_', $connection->prefix());
	}


	public function testEscape(): void
	{
		$connection = new Connection($doctrine = Mockery::mock(DBALConnection::class));
		$doctrine->shouldReceive('quote')->with('test')->once()->andReturn('ok');

		Assert::same('ok', $connection->escape('test'));
	}


	public function testLastId(): void
	{
		$connection = new Connection($doctrine = Mockery::mock(DBALConnection::class));
		$doctrine->shouldReceive('lastInsertId')->withNoArgs()->once()->andReturn(5);
		$doctrine->shouldReceive('lastInsertId')->withNoArgs()->once()->andReturn('ok');
		$doctrine->shouldReceive('lastInsertId')->withNoArgs()->once()->andReturn([]);

		Assert::same(5, $connection->lastId());
		Assert::same('ok', $connection->lastId());
		Assert::same(0, $connection->lastId());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ConnectionTest())->run();

