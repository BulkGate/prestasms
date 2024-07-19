<?php declare(strict_types=1);

namespace BulkGate\Plugin\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface ReturnStatus
{
	/**
	 * @return array<array-key, string>
	 */
	public function load(): array;
}
