<?php declare(strict_types=1);

namespace BulkGate\Plugin\Settings\Repository;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Structure\Collection, Database\Connection, Settings\Helpers, Strict};

class SettingsDatabase implements Settings
{
	use Strict;

	private Connection $db;

	public function __construct(Connection $db)
	{
		$this->db = $db;
	}


	public function load(string $scope): Collection
	{
		$collection = new Collection(Entity\Setting::class);

		$result = $this->db->execute($this->db->prepare("SELECT * FROM `{$this->db->table('bulkgate_module')}` WHERE `scope` = %s AND `synchronize_flag` != %s ORDER BY `order`", $scope, 'delete'));

		foreach ($result ?? [] as $row)
		{
			$entity = new Entity\Setting($row->toArray());

			$collection[$entity->key] = $entity;
		}

		return $collection;
	}


	public function save(Entity\Setting $setting): void
	{
		$this->db->execute($this->db->prepare(
			"INSERT INTO `{$this->db->table('bulkgate_module')}` (`scope`, `key`, `type`, `value`, `datetime`, `order`, `synchronize_flag`) " .
			"VALUES (%s, %s, %s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE " .
			"`type` = VALUES(`type`), `value` = VALUES(`value`), `datetime` = VALUES(`datetime`), `order` = VALUES(`order`), `synchronize_flag` = VALUES(`synchronize_flag`)",
			$setting->scope,
			$setting->key,
			$setting->type,
			Helpers::serializeValue($setting->value, $setting->type),
			$setting->datetime,
			$setting->order,
			$setting->synchronize_flag
		));
	}


	public function remove(string $scope, string $key): void
	{
		$this->db->execute($this->db->prepare(
			"DELETE FROM `{$this->db->table('bulkgate_module')}` WHERE `scope` = %s AND `key` = %s",
			$scope, $key
		));
	}


	public function cleanup(): void
	{
		$this->db->execute("DELETE FROM `{$this->db->table('bulkgate_module')}` WHERE `synchronize_flag` = 'delete'");
	}


	public function createTable(): void
	{
		$this->db->execute(
			"CREATE TABLE IF NOT EXISTS `{$this->db->table('bulkgate_module')}` (" .
			"`scope` varchar(50) NOT NULL DEFAULT 'main'," .
			"`key` varchar(50) NOT NULL," .
			"`type` varchar(50) NOT NULL DEFAULT 'string'," .
			"`value` longtext DEFAULT NULL," .
			"`datetime` int(11) NOT NULL," .
			"`order` int(11) NOT NULL DEFAULT 0," .
			"`synchronize_flag` varchar(50) NOT NULL DEFAULT 'none' COMMENT 'none/add/change/delete'," .
			"PRIMARY KEY (`scope`,`key`)," .
			"KEY `synchronize_flag` (`synchronize_flag`)," .
			"KEY `scope_synchronize_flag` (`scope`,`synchronize_flag`)" .
			") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
		);

		$this->db->execute("ALTER TABLE `{$this->db->table('bulkgate_module')}` ".
			"CHANGE `type` `type` varchar(50) NULL DEFAULT 'string' AFTER `key`, " .
			"CHANGE `value` `value` longtext DEFAULT NULL AFTER `type`," .
			"CHANGE `synchronize_flag` `synchronize_flag` varchar(50) NOT NULL DEFAULT 'none' AFTER `order`, " .
			"ENGINE='InnoDB';");

		$this->db->execute("UPDATE `{$this->db->table('bulkgate_module')}` SET `synchronize_flag` = 'delete', `datetime` = UNIX_TIMESTAMP() WHERE `scope` IN ('translates', 'menu');");
	}


	public function dropTable(): void
	{
		$this->db->execute("DROP TABLE IF EXISTS `{$this->db->table('bulkgate_module')}`");
	}
}
