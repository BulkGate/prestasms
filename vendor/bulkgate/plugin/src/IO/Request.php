<?php declare(strict_types=1);

namespace BulkGate\Plugin\IO;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{JsonException, Strict, Utils\Json};

class Request
{
	use Strict;

	public string $url;

	/**
	 * @var array<string, mixed>
	 */
	public array $data;

	public string $content_type;

	public int $timeout;


	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(string $url, array $data = [], string $content_type = 'application/json', int $timeout = 20)
	{
		$this->url = $url;
		$this->data = $data;
		$this->content_type = $content_type;
		$this->timeout = $timeout;
	}


	public function serialize(): string
	{
		try
		{
			return Json::encode($this->data);
		}
		catch (JsonException $e)
		{
			return '[]';
		}
	}
}
