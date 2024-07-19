<?php declare(strict_types=1);

namespace BulkGate\Plugin\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{Plugin\Strict, Plugin\JsonException};
use function is_array;

class JsonArray
{
	use Strict;

	/**
	 * @param array<array-key, mixed> $data
	 */
	public static function encode(array $data): string
	{
		try
		{
			return Json::encode($data);
		}
		catch (JsonException $e)
		{
			return '[]';
		}
	}


	/**
	 * @return array<array-key, mixed>
	 */
	public static function decode(string $json): array
	{
		try
		{
			$decoded = Json::decode($json);

			if (is_array($decoded))
			{
				return $decoded;
			}
		}
		catch (JsonException $e)
		{
		}

		return [];
	}
}
