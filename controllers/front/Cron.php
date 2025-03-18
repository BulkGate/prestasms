<?php declare(strict_types=1);

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Event\Dispatcher, Settings\Settings, Exception, Event\Asynchronous};

class bg_prestasmsCronModuleFrontController extends ModuleFrontController
{
	public $auth = false;

	public $ajax = true;

	public function display(): bool
	{
		if (!Tools::isPHPCLI())
		{
			throw new Exception('This module can only be run from the command line.');
		}

		/**
		 * @var Settings $settings
		 */
		$settings = $this->get('bulkgate.plugin.settings.settings');

		if (in_array($settings->load('main:dispatcher'), [Dispatcher::Cron, Dispatcher::Asset]))
		{
			/**
			 * @var Asynchronous $asynchronous
			 */
			$asynchronous = $this->get('bulkgate.plugin.event.asynchronous');

			$count = $asynchronous->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));

			echo "// Asynchronous task consumer has processed $count tasks\n";
		}
		else
		{
			echo "// Asynchronous task consumer is disabled\n";
		}

		return true;
	}
}