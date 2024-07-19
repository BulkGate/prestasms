<?php declare(strict_types=1);

namespace BulkGate\Plugin\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;
use ArrayAccess, Countable, IteratorAggregate, ArrayIterator;
use function array_key_exists, is_scalar, is_string, trim, class_alias;

/**
 * @implements ArrayAccess<string, scalar|null>
 * @implements IteratorAggregate<string, scalar|null>
 */
class Variables implements ArrayAccess, Countable, IteratorAggregate
{
	use Strict;

	/**
	 * @var array<string, scalar|null>
	 */
	private array $variables;


	/**
	 * @param array<string, scalar|null> $variables
	 */
	public function __construct(array $variables = [])
	{
		$this->variables = $variables;
	}


	/**
	 * @return array<string, scalar|null>
	 */
	public function toArray(): array
	{
		return $this->variables;
	}


	/**
	 * @return ArrayIterator<string, scalar|null>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->variables);
	}


	public function offsetExists($offset): bool
	{
		return isset($this->variables[$offset]);
	}


	/**
	 * @param string $offset
	 * @return scalar|null
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->variables[$offset] ?? null;
	}


	/**
	 * @param string $offset
	 * @param scalar|null|mixed $value
	 */
	public function offsetSet($offset, $value): void
	{
		if (is_scalar($value) || $value === null)
		{
			if (array_key_exists($offset, $this->variables) && ((is_string($value) && trim($value) === '') || $value === null))
			{
				return;
			}

			$this->variables[$offset] = $value;
		}
	}


	/**
	 * @param string $offset
	 */
	public function offsetUnset($offset): void
	{
		unset($this->variables[$offset]);

	}


	public function count(): int
	{
		return count($this->variables);
	}


	/**
	 * @deprecated use offsetGet()
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->variables[$key] ?? $default;
	}


	/**
	 * @deprecated use offsetSet()
	 * @param mixed $value
	 * @param mixed $alternative
	 */
	public function set(string $key, $value, $alternative = '', bool $rewrite = true): self
	{
		if (!isset($this->variables[$key]) || $rewrite)
		{
			if (is_scalar($value) && strlen(trim((string) $value)) > 0)
			{
				$this->variables[$key] = $value;
			}

			if (!isset($this->variables[$key]))
			{
				if (is_scalar($alternative) && strlen(trim((string) $alternative)) > 0)
				{
					$this->variables[$key] = (string) $alternative;
				}
				else
				{
					$this->variables[$key] = '';
				}
			}
		}

		return $this;
	}
}

class_alias(Variables::class, '\BulkGate\Extensions\Hook\Variables');
