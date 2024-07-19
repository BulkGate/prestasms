<?php declare(strict_types=1);

namespace BulkGate\Plugin\Structure;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Countable, IteratorAggregate;
use function array_key_exists;

/**
 * @template TKey of array-key
 * @template TValue of Entity
 * @implements \ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
class Collection implements \ArrayAccess, Countable, IteratorAggregate
{
	use ArrayCountable;

	/**
	 * @use ArrayCast<TKey, TValue>
	 */
	use ArrayCast;

	/**
	 * @use ArrayIterable<TKey, TValue>
	 */
	use ArrayIterable;

	/**
	 * @use ArrayAccess<TKey, TValue>
	 */
	use ArrayAccess {
		offsetSet as private offsetSetPublic;
	}

	/**
	 * @var class-string<TValue>
	 */
	private string $type;

	/**
	 * @var array<array-key, TValue>
	 */
	protected array $list;

	/**
	 * @param class-string<TValue> $type
	 * @param array<array-key, TValue> $list
	 */
	public function __construct(string $type, array $list = [])
	{
		$this->type = $type;
		$this->list = $list;
	}


	/**
	 * @param TKey|null $offset
	 * @param TValue $value
	 */
	public function offsetSet($offset, $value): void
	{
		if ($value instanceof $this->type)
		{
			$this->offsetSetPublic($offset, $value);
		}
	}
}
