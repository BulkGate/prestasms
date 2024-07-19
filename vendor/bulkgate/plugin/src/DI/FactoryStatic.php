<?php declare(strict_types=1);

namespace BulkGate\Plugin\DI;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use function is_callable;

trait FactoryStatic
{
	private static Container $container;

	/**
	 * @var callable(): array<string, mixed>
	 */
	private static $parameters_callback;

	public static function setup(callable $callback): void
	{
		self::$parameters_callback = $callback;
	}


	public static function get(): Container
	{
		if (!isset(self::$container))
		{
			self::$container = self::createContainer(isset(self::$parameters_callback) && is_callable(self::$parameters_callback) ? (self::$parameters_callback)() : []);
		}

		return self::$container;
	}


	abstract protected static function createContainer(array $parameters = []): Container;
}
