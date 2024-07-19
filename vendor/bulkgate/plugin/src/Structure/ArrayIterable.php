<?php declare(strict_types=1);

namespace BulkGate\Plugin\Structure;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use ArrayIterator;

/**
 * @template TKey of array-key
 * @template TValue of mixed
 * @property array<TKey, TValue> $list
 */
trait ArrayIterable
{
	/**
	 * @return ArrayIterator<TKey, TValue>
	 */
	public function getIterator(): ArrayIterator
	{
		/**
		 * @var ArrayIterator<TKey, TValue> $iterator
		 */
		$iterator = new ArrayIterator($this->list);

		return $iterator;
	}
}
