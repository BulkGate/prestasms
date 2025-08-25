<?php

declare(strict_types=1);

use BulkGate\Plugin;
use BulkGate\PrestaShop\DI\Container;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class bulkgate_sms_module_for_prestashopCronModuleFrontController extends ModuleFrontController
{
    use Container;
    public $auth = false;

    public $ajax = true;

    public function display(): bool
    {
        if (!Tools::isPHPCLI()) {
            throw new Plugin\Exception('This module can only be run from the command line.');
        }

        $settings = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class);

        if (in_array($settings->load('main:dispatcher'), [Plugin\Event\Dispatcher::Cron, Plugin\Event\Dispatcher::Asset])) {
            $count = $this->getBulkGateContainer()->getByClass(Plugin\Event\Asynchronous::class)->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));

            echo "// Asynchronous task consumer has processed $count tasks\n";
        } else {
            echo "// Asynchronous task consumer is disabled\n";
        }

        return true;
    }
}
