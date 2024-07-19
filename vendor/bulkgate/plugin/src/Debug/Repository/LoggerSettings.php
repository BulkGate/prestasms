<?php declare(strict_types=1);

namespace BulkGate\Plugin\Debug\Repository;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Settings\Settings};
use function array_slice, is_array, count, is_scalar;

class LoggerSettings implements Logger
{
	use Strict;

	private const Key = 'static:log_';

	private Settings $settings;

	private int $limit = 100;

	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
	}


	public function setup(?int $limit = null): void
	{
		$this->limit = $limit ?? $this->limit;
	}


	public function log(string $message, int $created, string $level = 'error'): void
	{
		$list = $this->settings->load(self::Key . $level);

		if (!is_array($list))
		{
			$list = [];
		}

		$list[] = [
			'message' => $message,
			'created' => $created,
		];

		$this->settings->set(self::Key  . $level, count($list) > $this->limit ? array_slice($list, 1, $this->limit) : $list, ['type' => 'array']);
	}


	public function getList(string $level = 'error'): array
	{
		$output = [];

		$list = $this->settings->load(self::Key . $level) ?? [];

		if (is_array($list)) foreach ($list as $item) if (is_array($item))
		{
			$output[] = [
				'message' => isset($item['message']) && is_scalar($item['message']) ? (string) $item['message'] : '',
				'created' => isset($item['created']) && is_scalar($item['created']) ? (int) $item['created'] : 0,
			];
		}

		return $output;
	}
}
