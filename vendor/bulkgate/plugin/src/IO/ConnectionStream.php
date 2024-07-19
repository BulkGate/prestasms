<?php declare(strict_types=1);

namespace BulkGate\Plugin\IO;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, AuthenticateException, InvalidResponseException};
use function implode;

class ConnectionStream implements Connection
{
	use Strict;

	/**
	 * @var callable(): string $jwt_factory
	 */
	public $jwt_factory;

	private string $jwt_token;

	public function __construct(callable $jwt_factory)
	{
		$this->jwt_factory = $jwt_factory;
	}


	/**
	 * @throws AuthenticateException
	 * @throws InvalidResponseException
	 */
	public function run(Request $request): Response
	{
		$this->jwt_token ??= ($this->jwt_factory)();

		$context = stream_context_create(['http' => [
			'method' => 'POST',
			'header' => [
				"Content-type: $request->content_type",
				"Authorization: Bearer $this->jwt_token",
			],
			'content' => $request->serialize(),
			'ignore_errors' => true,
			'timeout' => $request->timeout
		]]);

		$connection = fopen($request->url, 'r', false, $context);

		try
		{
			if ($connection)
			{
				$response = (string) stream_get_contents($connection);

				$meta = stream_get_meta_data($connection);

				if (isset($meta['wrapper_data']))
				{
					return new Response($response, Helpers::parseContentType(implode("\n", $meta['wrapper_data'])) ?? 'application/json');
				}
			}
			return new Response('{"error":"Server Unavailable. Try contact your hosting provider."}', 'application/json');
		}
		finally
		{
			$connection !== false && fclose($connection);
		}
	}
}
