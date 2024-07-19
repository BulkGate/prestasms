<?php declare(strict_types=1);

namespace BulkGate\Plugin\Database;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Structure;
use ArrayAccess, Countable, IteratorAggregate;
use function is_array, class_alias;

/**
 * @implements ArrayAccess<array-key, Result>
 * @implements IteratorAggregate<array-key, Result>
 */
class ResultCollection implements ArrayAccess, Countable, IteratorAggregate
{
	use Structure\ArrayCountable;

	/**
	 * @use Structure\ArrayIterable<array-key, Result>
	 */
	use Structure\ArrayIterable;

	/**
	 * @use Structure\ArrayCast<array-key, Result>
	 */
	use Structure\ArrayCast;

	/**
	 * @use Structure\ArrayAccess<array-key, Result>
	 */
	use Structure\ArrayAccess {
		offsetSet as private offsetSetPublic;
	}

	/**
	 * @var array<array-key, Result>
	 */
	protected array $list = [];


	/**
	 * @param array<array-key, array<array-key, mixed>> $list
	 */
	public function __construct(array $list = [])
	{
		foreach ($list as $key => $value) if (is_array($value))
		{
			$this[$key] = $value;
		}
	}


	/**
	 * @param array-key|null $offset
	 * @param array<array-key, mixed>|Result $value
	 */
	public function offsetSet($offset, $value): void
	{
		if (is_array($value))
		{
			$value = new Result($value);
		}

		$this->offsetSetPublic($offset, $value);
	}


	// BC

	/**
	 * @deprecated use count()
	 */
	public function getNumRows(): int
	{
		return count($this);
	}


	/**
	 * @deprecated
	 */
	public function getRow(): ?object
	{
		$key = array_key_first($this->list);

		return $key !== null ? $this->list[$key] : null;
	}


	/**
	 * @deprecated
	 * @return array<array-key, object>
	 */
	public function getRows(): array
	{
		return $this->list;
	}
}

class_alias(ResultCollection::class, 'BulkGate\Extensions\Database\Result');
