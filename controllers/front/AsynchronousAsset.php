<?php

declare(strict_types=1);

use BulkGate\Plugin;
use BulkGate\PrestaShop\DI\Container;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class bg_prestasmsAsynchronousAssetModuleFrontController extends ModuleFrontController
{
    use Container;

    public function initContent(): void
    {
        header('Content-Type: application/javascript');
        header('Cache-Control: no-store');
        parent::initContent();
    }

    public function display(): bool
    {
        $settings = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class);

        if (in_array($settings->load('main:dispatcher'), [Plugin\Event\Dispatcher::Cron, Plugin\Event\Dispatcher::Asset])) {
            $count = $this->getBulkGateContainer()->getByClass(Plugin\Event\Asynchronous::class)->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));

            echo "// Asynchronous task consumer has processed $count tasks";
        } else {
            echo '// Asynchronous task consumer is disabled';
        }

        return true;
    }
}
