<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Ajax;

use BulkGate\Plugin\Settings\Settings;
use BulkGate\Plugin\Strict;
use BulkGate\Plugin\User\Sign;
use BulkGate\Plugin\Utils\JsonResponse;

/**
 * @author Lukáš Piják 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
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
                ['token' => $this->sign->authenticate(false, ['expire' => \time() + 300])]
        );
    }
}
