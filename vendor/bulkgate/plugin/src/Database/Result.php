<?php declare(strict_types=1);

namespace BulkGate\Plugin\Database;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use ArrayAccess;
use BulkGate\Plugin\{Strict, Structure};

/**
 * @implements ArrayAccess<array-key, mixed>
 */
class Result implements ArrayAccess
{
	use Strict;

	/**
	 * @use Structure\ArrayAccess<array-key, mixed>
	 */
	use Structure\ArrayAccess;

	/**
	 * @use Structure\ArrayCast<array-key, mixed>
	 */
	use Structure\ArrayCast;

	/**
	 * @var array<array-key, mixed>
	 */
	protected array $list;


	/**
	 * @param array<array-key, mixed> $list
	 */
	public function __construct(array $list)
	{
		$this->list = $list;
	}


	/**
	 * @param array-key $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this[$name];
	}


	/**
	 * @param mixed $value
	 */
	public function __set(string $name, $value): void
	{
		$this[$name] = $value;
	}
}