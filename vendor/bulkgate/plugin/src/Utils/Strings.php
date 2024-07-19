<?php declare(strict_types=1);

namespace BulkGate\Plugin\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;
use function extension_loaded, mb_strtolower, mb_strtoupper, strlen, strtolower, ord, is_string, preg_replace, strtoupper;

class Strings
{
	use Strict;

	public static function length(string $s, string $encoding = 'UTF-8'): int
	{
		return extension_loaded('mbstring') ? (int) mb_strlen($s, $encoding) : self::strlenPolyfill($s);
	}


	public static function lower(string $s, string $encoding = 'UTF-8'): string
	{
		return extension_loaded('mbstring') ? mb_strtolower($s, $encoding) : self::strToLowerPolyfill($s);
	}


	public static function upper(string $s, string $encoding = 'UTF-8'): string
	{
		return extension_loaded('mbstring') ? mb_strtoupper($s, $encoding) : self::strToUpperPolyfill($s);
	}


	public static function strlenPolyfill(string $s): int
	{
		$char_count = 0;
		$length = strlen($s);
		$position = 0;

		while ($position < $length)
		{
			$char = ord($s[$position]);

			if ($char < 0x80)
			{
				$position++;
			}
			else if (($char >> 5) === 0x06)
			{
				$position += 2;
			}
			else if (($char >> 4) === 0x0E)
			{
				$position += 3;
			}
			else if (($char >> 3) === 0x1E)
			{
				$position += 4;
			}
			else
			{
				$position++;
			}

			$char_count++;
		}

		return $char_count;
	}


	public static function strToLowerPolyfill(string $s): string
	{
		$s = preg_replace('~[\x00-\x1F\x7F]~u', '', $s);

		return is_string($s) ? strtolower($s) : '';
	}


	public static function strToUpperPolyfill(string $s): string
	{
		$s = preg_replace('~[\x00-\x1F\x7F]~u', '', $s);

		return is_string($s) ? strtoupper($s) : '';
	}
}
