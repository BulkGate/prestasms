<?php declare(strict_types=1);

namespace BulkGate\Plugin\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;
use function header;

class JsonResponse
{
	use Strict;

	/**
	 * @param array<array-key, mixed> $data
	 * @return never
	 */
	public static function send(array $data): void
	{
		header('Content-Type: application/json');

		echo JsonArray::encode($data);

		exit;
	}
}
