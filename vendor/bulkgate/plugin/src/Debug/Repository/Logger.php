<?php declare(strict_types=1);

namespace BulkGate\Plugin\Debug\Repository;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface Logger
{
	public function log(string $message, int $created, string $level = 'error'): void;


	/**
	 * @return list<array{message: string, created: int}>
	 */
	public function getList(string $level = 'error'): array;
}
