<?php declare(strict_types=1);

namespace BulkGate\Plugin\IO;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, AuthenticateException, InvalidResponseException};
use function is_string;
use const CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST, CURLOPT_HTTP_VERSION, CURLOPT_HTTPHEADER, CURLOPT_MAXREDIRS, CURLOPT_POSTFIELDS, CURLOPT_RETURNTRANSFER, CURLOPT_SSL_VERIFYPEER, CURLOPT_TIMEOUT, CURLOPT_URL;

class ConnectionCurl implements Connection
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

		$curl = curl_init();

		try
		{
			curl_setopt_array($curl, [
				CURLOPT_URL => $request->url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => $request->timeout,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $request->serialize(),
				CURLOPT_HTTPHEADER => [
					"Content-Type: $request->content_type",
					"Authorization: Bearer $this->jwt_token"
				],
			]);

			$response = curl_exec($curl);

			if (!is_string($response))
			{
				return new Response('{"error":"Server Unavailable. Try contact your hosting provider."}', 'application/json');
			}

			$content_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

			return new Response($response, is_string($content_type) ? Helpers::getContentTypeWithoutCoding($content_type) : 'application/json');
		}
		finally
		{
			curl_close($curl);
		}
	}
}
