<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\DI;

use BulkGate\Plugin;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
trait Container
{
    protected ?Plugin\DI\Container $bulkgate_container = null;

    public function getBulkGateContainer(): Plugin\DI\Container
    {
        if ($this->bulkgate_container !== null) {
            return $this->bulkgate_container;
        }

        /**
         * @phpstan-ignore function.alreadyNarrowedType
         */
        $symfony_di = SymfonyContainer::getInstance() ?? (method_exists($this, 'getContainer') ? $this->getContainer() : $this->container);

        Factory::setup(fn () => [
            'symfony_di' => $symfony_di,
            'api_version' => BulkGateApiVersion,
            'module_version' => BulkGateModuleVersion,
            'platform_version' => _PS_VERSION_,
            'gate_url' => BulkGateWhiteLabelUrl,
            'default_settings' => [
                'main:dispatcher' => 'asset',
                'main:synchronization' => 'all',
                'main:language' => 'auto',
                'main:language_mutation' => false,
                'main:delete_db' => false,
                'main:address_preference' => 'delivery',
                'main:marketing_message_opt_in_enabled' => false,
                'main:marketing_message_opt_in_label' => '',
                'main:marketing_message_opt_in_default' => false,
                'main:marketing_message_opt_in_url' => '',
            ],
        ]);

        return $this->bulkgate_container = Factory::get();
    }
}
