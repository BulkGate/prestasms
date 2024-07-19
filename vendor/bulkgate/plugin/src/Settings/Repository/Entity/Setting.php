<?php declare(strict_types=1);

namespace BulkGate\Plugin\Settings\Repository\Entity;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Structure\Entity, Settings\Helpers, Strict};
use function is_string, time;

class Setting implements Entity
{
	use Strict;

	private const SynchronizeDefault = 'none';

	private const SynchronizeFlags = ['none', 'add', 'change', 'delete'];

	public string $scope = 'main';

	public string $key;

	public string $type;

	/**
	 * @var string|bool|float|int|array<array-key, mixed>|null
	 */
	public $value;

	public int $datetime;

	public int $order;

	public string $synchronize_flag = self::SynchronizeDefault;


	/**
	 * @param array<array-key, scalar|null> $list
	 */
	public function __construct(array $list = [])
	{
		$this->scope = (string) ($list['scope'] ?? 'main');
		$this->key = (string) ($list['key'] ?? 'unknown');
		$this->type = (string) ($list['type'] ?? Helpers::detectType($list['value'] ?? '') ?? 'string');
		$this->value = is_string($list['value'] ?? '') ? Helpers::deserializeValue($list['value'] ?? '', $this->type) : $list['value'] ?? '';
		$this->datetime = (int) ($list['datetime'] ?? time());
		$this->order = (int) ($list['order'] ?? 0);
		$this->synchronize_flag = Helpers::checkEnum((string) ($list['synchronize_flag'] ?? self::SynchronizeDefault), self::SynchronizeFlags, self::SynchronizeDefault);
	}
}
