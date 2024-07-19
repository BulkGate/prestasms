<?php declare(strict_types=1);

namespace BulkGate\Plugin\IO;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{AuthenticateException, Helpers, InvalidResponseException, Strict, Utils\JsonArray};
use function array_key_exists, is_string, array_key_first, is_array;

class Response
{
	use Strict;

	/**
	 * @var array<string, mixed>
	 */
	public array $data = [];


	/**
	 * @throws AuthenticateException
	 * @throws InvalidResponseException
	 */
	public function __construct(string $data, string $content_type = 'application/json')
	{
		if ($content_type === 'application/json')
		{
			$this->setData(JsonArray::decode($data));
		}
		else
		{
			throw new InvalidResponseException('invalid_content_type');
		}
	}


	/**
	 * @param array<array-key, mixed> $decoded
	 * @throws AuthenticateException
	 * @throws InvalidResponseException
	 */
	private function setData(array $decoded): void
	{
		if ($decoded === [])
		{
			throw new InvalidResponseException('empty_response');
		}

		$this->checkError($decoded);

		if (isset($decoded['signal']) && $decoded['signal'] === 'authenticate')
		{
			throw new AuthenticateException('authenticate');
		}

		$this->data = $decoded;
	}


	/**
	 * @param array<array-key, mixed> $array
	 * @throws InvalidResponseException
	 */
	private function checkError(array $array): void
	{
		if (array_key_exists('error', $array))
		{
			if (is_string($array['error']))
			{
				throw new InvalidResponseException($array['error']);
			}
			else if (is_array($array['error']) && !empty($array['error']))
			{
				$key = array_key_first($array['error']);

				throw new InvalidResponseException($array['error'][$key]);
			}
		}
	}
}
