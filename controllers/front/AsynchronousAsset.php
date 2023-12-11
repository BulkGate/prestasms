<?php

use BulkGate\Plugin\Event\{Asynchronous, Dispatcher};

class bg_prestasmsAsynchronousAssetModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		header('Content-Type: application/javascript');
		header('Cache-Control: no-store');
		parent::initContent();
	}

	public function display()
	{
		$settings = $this->get('bulkgate.plugin.settings.settings');

		if (($settings->load('main:dispatcher') ?? Dispatcher::$default_dispatcher) === Dispatcher::Asset)
		{
			$count = $this->get('bulkgate.plugin.event.asynchronous')->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));

			echo "// Asynchronous task consumer has processed $count tasks";
		}
		else
		{
			echo "// Asynchronous task consumer is disabled";
		}
	}
}