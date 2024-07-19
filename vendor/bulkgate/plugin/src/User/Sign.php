<?php declare(strict_types=1);

namespace BulkGate\Plugin\User;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{Plugin\AuthenticateException, Plugin\Debug\Logger, Plugin\Eshop\Configuration, Plugin\InvalidResponseException, Plugin\IO\Connection, Plugin\IO\Request, Plugin\IO\Url, Plugin\Localization\Language, Plugin\Settings\Settings, Plugin\Strict, Plugin\Utils\Jwt};
use function array_merge;

class Sign
{
	use Strict;

	private Settings $settings;

	private Connection $connection;

	private Url $url;

    private Configuration $configuration;

	private Language $language;

	private Logger $logger;


	public function __construct(Settings $settings, Connection $connection, Url $url, Configuration $configuration, Language $language, Logger $logger)
	{
		$this->settings = $settings;
		$this->connection = $connection;
		$this->url = $url;
		$this->configuration = $configuration;
		$this->language = $language;
		$this->logger = $logger;
	}


	/**
	 * @param array<string, mixed> $parameters
	 */
	public function authenticate(bool $reload = false, array $parameters = []): ?string
	{
		$token = $this->settings->load('static:application_token', $reload);

		return Jwt::encode([
			'application_id' => $this->settings->load('static:application_id'),
			'application_installation' => $this->configuration->url(),
			'application_product' => $this->configuration->product(),
			'application_language' => $this->language->get(),
			'application_version' => $this->configuration->version(),
			'application_parameters' => array_merge([
				'guest' => $token === null,
			], $parameters),
		], $token ?? '');
	}


	/**
	 * @return array{token: string|null, data: array{redirect: string|null}}|array{error: list<string>}
	 */
	public function in(string $email, string $password, ?string $success_redirect = null): array
	{
		try
		{
			$response = $this->connection->run(new Request($this->url->get('api/1.0/token/get/permanent'), [
				'email' => $email,
				'password' => $password,
				'name' => $this->configuration->name(),
				'url' => $this->configuration->url(),
			], 'application/json', 20));

			if (!isset($response->data['data']['application_id']) || !isset($response->data['data']['application_token']))
			{
				return ['error' => ['unknown_error']];
			}

			$this->settings->install();

			$this->settings->set('static:application_id', $response->data['data']['application_id'], ['type' => 'int']);
			$this->settings->set('static:application_token', $response->data['data']['application_token'], ['type' => 'string']);
			$this->settings->set('static:synchronize', 0, ['type' => 'int']);

			return ['token' => $this->authenticate(true), 'data' => ['redirect' => $success_redirect]];
		}
		catch (InvalidResponseException|AuthenticateException $e)
		{
			$this->logger->log("Sign Error: {$e->getMessage()}");

			return ['error' => [$e->getMessage()]];
		}
	}


	/**
	 * @return array{token: string|null, data: array{redirect: string}}
	 */
	public function out(string $success_redirect): array
	{
		$this->settings->delete('static:application_token');

		return ['token' => $this->authenticate(true), 'data' => ['redirect' => $success_redirect]];
	}
}
