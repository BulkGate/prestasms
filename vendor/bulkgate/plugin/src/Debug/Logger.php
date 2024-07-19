<?php declare(strict_types=1);

namespace BulkGate\Plugin\Debug;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;

class Logger
{
	use Strict;

	private Repository\Logger $repository;

	public function __construct(Repository\Logger $repository)
	{
		$this->repository = $repository;
	}


	public function log(string $message, string $level = 'error'): void
	{
		$this->repository->log($message, time(), $level);
	}


	/**
	 * @return list<array{message: string, created: int}>
	 */
	public function getList(string $level = 'error'): array
	{
		return $this->repository->getList($level);
	}
}
