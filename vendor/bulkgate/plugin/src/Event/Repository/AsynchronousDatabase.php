<?php declare(strict_types=1);

namespace BulkGate\Plugin\Event\Repository;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Database\Connection, Structure\Collection};
use function implode;

class AsynchronousDatabase implements Asynchronous
{
	use Strict;

	private Connection $db;

	public function __construct(Connection $db)
	{
		$this->db = $db;
	}


	public function load(int $limit): Collection
	{
		$collection = new Collection(Entity\Task::class);

		$this->db->execute('START TRANSACTION');

		$records = $this->db->execute("SELECT * FROM `{$this->db->table('bulkgate_module')}` WHERE `scope` = 'asynchronous' AND `order` = 0 LIMIT $limit FOR UPDATE");

		if ($records !== null)
		{
			$keys = [];

			foreach ($records as $record)
			{
				$keys[] = $this->db->escape($record['key']);

				$collection[] = new Entity\Task($record->toArray());
			}

			if ($keys !== [])
			{
				$keys_sql = implode("','", $keys);

				$this->db->execute("UPDATE `{$this->db->table('bulkgate_module')}` SET `order` = -1 WHERE `scope` = 'asynchronous' AND `key` IN ('$keys_sql')");
			}

			$this->db->execute("COMMIT");
		}
		else
		{
			$this->db->execute("ROLLBACK");
		}

		return $collection;
	}


	public function finish(string $key): void
	{
		$this->db->execute(
			$this->db->prepare("DELETE FROM `{$this->db->table('bulkgate_module')}` WHERE `scope` = 'asynchronous' AND `key` = %s", $key)
		);
	}
}
