<?php

namespace BulkGate\PrestaSms\DI;

use BulkGate\Plugin;

trait Container
{
	protected ?Plugin\DI\Container $bulkgate_container = null;

	public function getBulkGateContainer(): Plugin\DI\Container
	{
		if ($this->bulkgate_container !== null) {
			return $this->bulkgate_container;
		}

		$symfony_di = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance() ?? (method_exists($this, 'getContainer') ? $this->getContainer() : $this->container);

		Factory::setup(fn () => [
			'symfony_di' => $symfony_di,
			'api_version' => '1.0',
			'module_version' => '6.0.0',
			'platform_version' => _PS_VERSION_,
			/** @phpstan-ignore property.notFound */
			'gate_url' => 'http://192.168.16.1', //BulkGateWhiteLabelUrl,
			'default_settings' => [
				"main:dispatcher" => 'asset',
				"main:synchronization" => 'all',
				"main:language" => 'auto',
				"main:language_mutation" => false,
				"main:delete_db" => false,
				"main:address_preference" => 'delivery',
				"main:marketing_message_opt_in_enabled" => false,
				"main:marketing_message_opt_in_label" => '',
				"main:marketing_message_opt_in_default" => false,
				"main:marketing_message_opt_in_url" => ''
			]
		]);

		return $this->bulkgate_container = Factory::get();
	}
}