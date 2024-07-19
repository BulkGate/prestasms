<?php declare(strict_types=1);

namespace BulkGate\Plugin\IO;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{InvalidJwtException, Settings\Settings, Strict, Utils\Jwt};

class ConnectionFactory
{
	use Strict;

	private string $application_url;

	private string $application_product;

	private Settings $settings;

	public function __construct(string $application_url, string $application_product, Settings $settings)
	{
		$this->application_url = $application_url;
		$this->application_product = $application_product;
		$this->settings = $settings;
	}


	public function create(): Connection
	{
		$token_factory = function (): string
		{
			$application_token = $this->settings->load('static:application_token');

			$jwt = Jwt::encode([
				'application_id' => $this->settings->load('static:application_id'),
				'application_url' => $this->application_url,
				'application_product' => $this->application_product,
				'application_language' => $this->settings->load('static:language') ?? 'en',
			], $application_token ?? '');

			if ($jwt === null)
			{
				throw new InvalidJwtException('Unable to create JWT');
			}

			return $jwt;
		};

		return extension_loaded('curl') ? new ConnectionCurl($token_factory) : new ConnectionStream($token_factory);
	}
}
