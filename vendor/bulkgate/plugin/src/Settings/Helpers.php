<?php declare(strict_types=1);

namespace BulkGate\Plugin\Settings;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Utils\JsonArray};
use function is_array, is_bool, is_float, is_int, is_scalar, is_string, preg_match, in_array;
use function strtolower;
use const PREG_UNMATCHED_AS_NULL;

class Helpers
{
	use Strict;

	public const DefaultScope = 'main';


	/**
	 * @return array{string, string|null}
	 */
	public static function key(string $key): array
	{
		if (preg_match('~^([\w_-]+)?:?([\w_-]+)?$~U', $key, $match, PREG_UNMATCHED_AS_NULL))
		{
			[, $scope, $name] = $match;
		}

		return [$scope ?? self::DefaultScope, $name ?? null];
	}


	/**
	 * @param list<string> $enum_list
	 */
	public static function checkEnum(string $value, array $enum_list, string $default): string
	{
		return in_array($value, $enum_list, true) ? $value : $default;
	}


	/**
	 * @param array<array-key, mixed>|scalar|null $value
	 */
	public static function detectType($value): ?string
	{
		if (is_string($value))
		{
			return 'string';
		}
		else if (is_int($value))
		{
			return 'int';
		}
		else if (is_array($value))
		{
			return 'array';
		}
		else if (is_bool($value))
		{
			return 'bool';
		}
		else if (is_float($value))
		{
			return 'float';
		}
		return null;
	}


	/**
	 * @param mixed $value
	 */
	public static function serializeValue($value, ?string $type = null): string
	{
		$type ??= self::detectType($value);

		if (in_array($type, ['array', 'json'], true))
		{
			return JsonArray::encode($value);
		}
		else if ($type === 'bool')
		{
			return $value ? '1' : '0';
		}
		else if (is_scalar($value) || $value === null)
		{
			return (string)$value;
		}
		return '';
	}


	/**
	 * @param string $value
	 * @param string $type
	 * @return scalar|array<array-key, mixed>|null
	 */
	public static function deserializeValue(string $value, string $type)
	{
		if ($type === 'int')
		{
			return (int) $value;
		}
		else if (in_array($type, ['string', 'text'], true))
		{
			return $value;
		}
		else if ($type === 'bool')
		{
			return in_array(strtolower($value), ['1', 'true', 'on', 'yes'], true);
		}
		else if ($type === 'float')
		{
			return (float) $value;
		}
		else if (in_array($type, ['array', 'json'], true))
		{
			return JsonArray::decode($value);
		}
		return null;
	}
}
