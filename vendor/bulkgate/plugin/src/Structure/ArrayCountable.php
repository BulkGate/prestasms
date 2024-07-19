<?php declare(strict_types=1);

namespace BulkGate\Plugin\Structure;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

trait ArrayCountable
{
	public function count(): int
	{
		return count($this->list);
	}
}
