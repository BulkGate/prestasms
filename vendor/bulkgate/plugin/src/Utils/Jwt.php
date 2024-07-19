<?php declare(strict_types=1);

namespace BulkGate\Plugin\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, JsonException};
use function base64_encode, hash_hmac, strtr, rtrim;

class Jwt
{
	use Strict;

	private string $secret_key;

	public function __construct(string $secret_key)
	{
		$this->secret_key = $secret_key;
	}


	/**
	 * @param array<array-key, mixed> $data
	 */
	public function create(array $data, ?string $secret_key = null): ?string
	{
		return self::encode($data, $secret_key ?? $this->secret_key);
	}


	/**
	 * @param array<array-key, mixed> $data
	 */
	public static function encode(array $data, string $secret_key): ?string
	{
		try
		{
			$header = self::baseEncode(Json::encode([
				'alg' => 'HS256',
				'typ' => 'JWT'
			]));

			$payload = self::baseEncode(Json::encode($data));

			$signature = self::baseEncode(hash_hmac('sha256', "$header.$payload", $secret_key, true));

			return "$header.$payload.$signature";
		}
		catch (JsonException $e)
		{
			return null;
		}
	}


	private static function baseEncode(string $data): string
	{
		$base64 = base64_encode($data);

		$url = strtr($base64, '+/', '-_');

		return rtrim($url, '=');
	}
}
