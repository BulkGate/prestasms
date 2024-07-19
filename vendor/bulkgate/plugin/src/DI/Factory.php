<?php declare(strict_types=1);

namespace BulkGate\Plugin\DI;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface Factory
{
	/**
	 * @param callable(): array<string, mixed> $callback
	 */
	public static function setup(callable $callback): void;


	/**
	 * @return Container
	 */
	public static function get(): Container;
}
