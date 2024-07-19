<?php declare(strict_types=1);

namespace BulkGate\Plugin\Event\Repository\Entity;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Settings\Helpers, Structure\Entity};
use function is_array, is_string, time;

class Task implements Entity
{
	use Strict;

	public string $key;

	/**
	 * @var array{category?: string|mixed, endpoint?: string|mixed, variables?: array<string, scalar>|mixed}
	 */
	public array $value;

	public int $datetime = 0;

	public int $order = 0;

	/**
	 * @param array<array-key, mixed> $parameters
	 */
	public function __construct(array $parameters)
	{
		$value = is_string($parameters['value'] ?? null) ? Helpers::deserializeValue($parameters['value'], 'array') : [];

		$this->key = $parameters['key'] ?? '';
		$this->value = is_array($value) ? $value : [];
		$this->datetime = isset($parameters['datetime']) ? (int) $parameters['datetime'] : time();
		$this->order = isset($parameters['order']) ? (int) $parameters['order'] : 0;
	}
}
