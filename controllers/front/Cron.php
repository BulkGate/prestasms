<?php

use BulkGate\Plugin;
use BulkGate\PrestaSms\DI\Container;

class bg_prestasmsCronModuleFrontController extends ModuleFrontController
{
	public $auth = false;

	public $ajax = true;

	use Container;

	public function display()
	{
		if (!Tools::isPHPCLI()) {
			throw new Plugin\Exception('This module can only be run from the command line.');
		}

		$settings = $this->getBulkGateContainer()->getByClass(Plugin\Settings\Settings::class);

		if (in_array($settings->load('main:dispatcher'), [Plugin\Event\Dispatcher::Cron, Plugin\Event\Dispatcher::Asset])) {
			$count = $this->getBulkGateContainer()->getByClass(Plugin\Event\Asynchronous::class)->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));

			echo "// Asynchronous task consumer has processed $count tasks";
		} else {
			echo '// Asynchronous task consumer is disabled';
		}

		echo "\n";
	}
}