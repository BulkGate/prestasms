<?php declare(strict_types=1);

namespace BulkGate\Plugin\Event\Repository;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Structure\Collection;

interface Asynchronous
{
	/**
	 * @param positive-int $limit
	 * @return Collection<array-key, Entity\Task>
	 */
	public function load(int $limit): Collection;


	public function finish(string $key): void;
}
