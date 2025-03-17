<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Ajax;

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Settings\Settings, Strict, User\Sign, Utils\JsonResponse};
use function time;

class Authenticate
{
	use Strict;

	private Settings $settings;

	private Sign $sign;

	public function __construct(Settings $settings, Sign $sign)
	{
		$this->settings = $settings;
		$this->sign = $sign;
	}


	/**
	 * @return never
	 */
	public function run(string $invalid_redirect): void
	{
		JsonResponse::send(
			$this->settings->load('static:application_token') === null ?
				['redirect' => $invalid_redirect] :
				['token' => $this->sign->authenticate(false, ['expire' => time() + 300])]
		);
	}
}
