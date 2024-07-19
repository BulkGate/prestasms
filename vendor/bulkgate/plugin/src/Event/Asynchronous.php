<?php declare(strict_types=1);

namespace BulkGate\Plugin\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;
use function is_array, is_string;

class Asynchronous
{
	use Strict;

	private Repository\Asynchronous $repository;

	private Hook $hook;

	public function __construct(Repository\Asynchronous $repository, Hook $hook)
	{
		$this->repository = $repository;
		$this->hook = $hook;
	}


	/**
	 * @param positive-int $limit
	 */
	public function run(int $limit): int
	{
		$list = $this->repository->load($limit);

		$counter = 0;

		foreach ($list as $item)
		{
			$data = $item->value;

			if (isset($data['category']) && isset($data['endpoint']) && isset($data['variables']))
			{
				['category' => $category, 'endpoint' => $endpoint, 'variables' => $variables] = $data;

				if (is_string($category) && is_string($endpoint) && is_array($variables))
				{
					$this->hook->dispatch($category, $endpoint, $variables);

					$counter ++;
				}
			}

			$this->repository->finish($item->key);
		}
		return $counter;
	}
}
