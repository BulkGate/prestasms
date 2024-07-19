<?php declare(strict_types=1);

namespace BulkGate\Plugin\Structure;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use function array_key_exists;

/**
 * @template TKey of array-key
 * @template TValue of mixed
 * @property array<TKey, TValue> $list
 */
trait ArrayAccess
{
	/**
	 * @param TKey $offset
	 */
	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->list);
	}


	/**
	 * @param TKey $offset
	 * @return TValue|null
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->list[$offset] ?? null;
	}


	/**
	 * @param TKey|null $offset
	 * @param TValue $value
	 */
	public function offsetSet($offset, $value): void
	{
		if ($offset === null)
		{
			$this->list[] = $value;
		}
		else
		{
			$this->list[$offset] = $value;
		}
	}

	/**
	 * @param TKey $offset
	 */
	public function offsetUnset($offset): void
	{
		unset($this->list[$offset]);
	}
}
