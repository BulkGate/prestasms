<?php declare(strict_types=1);

namespace BulkGate\Plugin;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use function preg_match, array_reduce;
use const PREG_UNMATCHED_AS_NULL;

class Helpers
{
	use Strict;

	public const DefaultReducer = '_generic';

	public const DefaultContainer = 'server';

	public const DefaultVariable = '_empty';


	/**
	 * @return array{string, string, string}
	 */
	public static function path(string $key): array
	{
		if (preg_match('~^(\w+)?:?(\w+)?:?(\w+)?$~U', $key, $match, PREG_UNMATCHED_AS_NULL))
		{
			[, $reducer, $container, $name] = $match;
		}

		return [$reducer ?? self::DefaultReducer, $container ?? self::DefaultContainer, $name ?? self::DefaultVariable];
	}


	/**
	 * @param array<string, mixed> $structure
	 * @return array<array-key, mixed>|scalar|null
	 */
	public static function reduceStructure(array $structure, string $path)
	{
		return array_reduce(self::path($path), fn ($item, string $key) => $key === self::DefaultVariable ? $item : ($item[$key] ?? null), $structure);
	}
}
