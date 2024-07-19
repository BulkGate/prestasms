<?php declare(strict_types=1);

namespace BulkGate\Plugin\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface DataLoader
{
	/**
	 * @param array<array-key, mixed> $parameters
	 */
	public function load(Variables $variables, array $parameters = []): void;
}
