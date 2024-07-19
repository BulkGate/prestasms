<?php declare(strict_types=1);

namespace BulkGate\Plugin\Settings\Repository;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Structure\Collection;

interface Settings
{
	/**
	 * @return Collection<string, Entity\Setting>
	 */
	public function load(string $scope): Collection;


	public function save(Entity\Setting $setting): void;


	public function remove(string $scope, string $key): void;


	public function cleanup(): void;


	public function createTable(): void;


	public function dropTable(): void;
}
