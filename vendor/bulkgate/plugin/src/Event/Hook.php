<?php declare(strict_types=1);

namespace BulkGate\Plugin\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{AuthenticateException, Debug\Logger, InvalidResponseException, IO\Request, IO\Response, IO\Url, Strict, IO\Connection, Utils\JsonArray};
use function str_replace;

class Hook
{
	use Strict;

	private string $version;

	private Connection $connection;

	private Url $url;

	private Logger $logger;

	public function __construct(string $version, Connection $connection, Url $url, Logger $logger)
	{
		$this->version = $version;
		$this->connection = $connection;
		$this->url = $url;
		$this->logger = $logger;
	}


	/**
	 * @param string $category order, customer, cart, product, return
	 * @param array<string, scalar|null> $variables
	 */
	public function dispatch(string $category, string $endpoint, array $variables): void
	{
		$category = str_replace('_', '-', $category);
		$endpoint = str_replace('_', '-', $endpoint);

		$this->send("api/$this->version/eshop/$category/$endpoint", ['language' => $variables['lang_id'] ?? null, 'shop_id' => $variables['shop_id'] ?? null, 'variables' => $variables]);
	}


	/**
	 * @param array<string, mixed> $data
	 */
	public function send(string $path, array $data): ?Response
	{
		try
		{
			return $this->connection->run(
				new Request(
					$this->url->get($path),
					$data
				)
			);
		}
		catch (AuthenticateException|InvalidResponseException $e)
		{
			$this->logger->log("Hook Error: '$path' - {$e->getMessage()}, " . JsonArray::encode($data));

			return null;
		}
	}
}
