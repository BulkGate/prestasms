<?php declare(strict_types=1);

namespace BulkGate\Plugin\Structure;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

/**
 * @template TKey of array-key
 * @template TValue of mixed
 * @property array<TKey, TValue> $list
 */
trait ArrayCast
{
	/**
	 * @return array<TKey, TValue>
	 */
	public function toArray(): array
	{
		return $this->list;
	}
}
