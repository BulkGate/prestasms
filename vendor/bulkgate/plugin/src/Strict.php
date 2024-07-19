<?php declare(strict_types=1);

namespace BulkGate\Plugin;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

trait Strict
{
	/**
	 * @param string $name
	 * @return mixed
	 * @throws StrictException
	 */
	public function __get(string $name)
	{
		throw new StrictException('You can\'t read from undeclared property ' . __CLASS__ . '::$' . $name);
	}


	/**
	 * @param mixed $value
	 * @throws StrictException
	 */
	public function __set(string $name, $value): void
	{
		throw new StrictException('You can\'t write to undeclared property ' . __CLASS__ . '::$' . $name);
	}


	public function __isset(string $name): bool
	{
		return false;
	}


	/**
	 * @throws StrictException
	 */
	public function __unset(string $name): void
	{
		throw new StrictException('You can\'t unset undeclared property ' . __CLASS__ . '::$' . $name);
	}


	/**
	 * @param array<array-key, mixed> $arguments
	 * @return mixed
	 * @throws StrictException
	 */
	public function __call(string $name, array $arguments)
	{
		throw new StrictException('You can\'t call undeclared method ' . __CLASS__ . '::$' . $name);
	}


	/**
	 * @param array<array-key, mixed> $arguments
	 * @return mixed
	 * @throws StrictException
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		throw new StrictException('You can\'t statically call undeclared method ' . __CLASS__ . '::$' . $name);
	}
}
