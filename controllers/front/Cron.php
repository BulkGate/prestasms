<?php

use BulkGate\Plugin\Event\Dispatcher;

class bg_prestasmsCronModuleFrontController extends ModuleFrontController
{
	public $auth = false;

	public $ajax = true;

	public function display()
	{
		if (!Tools::isPHPCLI()) {
			throw new \BulkGate\Plugin\Exception('This module can only be run from the command line.');
		}

		$settings = $this->get('bulkgate.plugin.settings.settings');

		if (in_array($settings->load('main:dispatcher'), [Dispatcher::Cron, Dispatcher::Asset])) {
			$count = $this->get('bulkgate.plugin.event.asynchronous')->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));

			echo "// Asynchronous task consumer has processed $count tasks";
		} else {
			echo '// Asynchronous task consumer is disabled';
		}

		echo "\n";
	}
}