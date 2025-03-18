<?php declare(strict_types=1);

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Event\Dispatcher, Settings\Settings, Event\Asynchronous};

class bg_prestasmsAsynchronousAssetModuleFrontController extends ModuleFrontController
{
	public function initContent(): void
	{
		header('Content-Type: application/javascript');
		header('Cache-Control: no-store');
		parent::initContent();
	}


	public function display(): bool
	{
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

			echo "// Asynchronous task consumer has processed $count tasks";
		}
		else
		{
			echo '// Asynchronous task consumer is disabled';
		}

		return true;
	}
}
