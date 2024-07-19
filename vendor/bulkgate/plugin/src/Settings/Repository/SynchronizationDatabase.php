<?php declare(strict_types=1);

namespace BulkGate\Plugin\Settings\Repository;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Database, IO, Structure};
use function array_values, is_array;

class SynchronizationDatabase implements Synchronization
{
	private Database\Connection $db;

	private IO\Connection $io;

	public function __construct(Database\Connection $db, IO\Connection $io)
	{
		$this->db = $db;
		$this->io = $io;
	}


	public function loadPluginSettings(): Structure\Collection
	{
		$collection = new Structure\Collection(Entity\Setting::class);

		$result = $this->db->execute("SELECT * FROM `{$this->db->table('bulkgate_module')}` WHERE `scope` NOT IN ('static', 'asynchronous')");

		foreach ($result ?? [] as $row)
		{
			$entity = new Entity\Setting($row->toArray());

			$collection["$entity->scope:$entity->key"] = $entity;
		}

		return $collection;
	}


	public function loadServerSettings(string $url, Structure\Collection $plugin_settings, int $timeout = 20): Structure\Collection
	{
		$result = $this->io->run(new IO\Request($url, ['synchronize' => array_values($plugin_settings->toArray())], 'application/json', $timeout));

		$collection = new Structure\Collection(Entity\Setting::class);

		$list = $result->data['data'] ?? null;

		if (is_array($list))
		{
			foreach ($list as $item)
			{
				$entity = new Entity\Setting($item);

				$collection["$entity->scope:$entity->key"] = $entity;
			}
		}

		return $collection;
	}
}
