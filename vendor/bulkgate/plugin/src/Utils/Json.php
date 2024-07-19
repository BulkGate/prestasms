<?php declare(strict_types=1);

namespace BulkGate\Plugin\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;

/*
 * Taken over from Nette (https://nette.org)
 */

class Json
{
	use Plugin\Strict;

	/**
	 * @param mixed $value
	 * @throws Plugin\JsonException
	 */
	public static function encode($value, bool $pretty = false, bool $escapeUnicode = false): string
	{
		$flags = 0;

		$flags |= ($escapeUnicode ? 0 : JSON_UNESCAPED_UNICODE)
			| ($pretty ? JSON_PRETTY_PRINT : 0)
			| JSON_UNESCAPED_SLASHES
			| (defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0);

		$json = json_encode($value, $flags);

		if ($error = json_last_error())
		{
			throw new Plugin\JsonException(json_last_error_msg(), $error);
		}

		return $json ?: '[]';
	}


	/**
	 * @return mixed
	 * @throws Plugin\JsonException
	 */
	public static function decode(string $json, bool $forceArray = true)
	{
		$flags = $forceArray ? JSON_OBJECT_AS_ARRAY : 0;

		$value = json_decode($json, null, 512, $flags | JSON_BIGINT_AS_STRING);

		if ($error = json_last_error())
		{
			throw new Plugin\JsonException(json_last_error_msg(), $error);
		}

		return $value;
	}
}
