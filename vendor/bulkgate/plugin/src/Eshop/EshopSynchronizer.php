<?php declare(strict_types=1);

namespace BulkGate\Plugin\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Settings\Settings, Strict, Settings\Synchronizer};

class EshopSynchronizer
{
	use Strict;

	private Synchronizer $synchronizer;

	private Settings $settings;

	private OrderStatus $order_status;

	private ReturnStatus $return_status;

	private Language $language;

	private MultiStore $multi_store;

	public function __construct(Synchronizer $synchronizer, Settings $settings, OrderStatus $order_status, ReturnStatus $return_status, Language $language, MultiStore $multi_store)
	{
		$this->synchronizer = $synchronizer;
		$this->settings = $settings;
		$this->order_status = $order_status;
		$this->return_status = $return_status;
		$this->language = $language;
		$this->multi_store = $multi_store;
	}


	public function run(bool $immediately = false): void
	{
		if ($this->settings->load('static:application_token') !== null)
		{
			$run = $immediately;
			$run = $this->checkStatusList() || $run;
			$run = $this->checkReturnStatusList() || $run;
			$run = $this->checkLanguage() || $run;
			$run = $this->checkMultiStore() || $run;

			$this->synchronizer->synchronize($run);
		}
	}


	private function checkStatusList(): bool
	{
		$status_list = $this->order_status->load();

		if ($this->settings->load(':order_status_list') !== $status_list)
		{
			$this->settings->set(':order_status_list', $status_list, ['type' => 'array']);

			return true;
		}

		return false;
	}


	private function checkReturnStatusList(): bool
	{
		$status_list = $this->return_status->load();

		if ($this->settings->load(':return_status_list') !== $status_list)
		{
			$this->settings->set(':return_status_list', $status_list, ['type' => 'array']);

			return true;
		}

		return false;
	}


	private function checkLanguage(): bool
	{
		$language = $this->language->load();

		if ($this->settings->load(':languages') !== $language)
		{
			$this->settings->set(':languages', $language, ['type' => 'array']);

			return true;
		}

		return false;
	}


	private function checkMultiStore(): bool
	{
		$multi_store = $this->multi_store->load();

		if ($this->settings->load(':stores') !== $multi_store)
		{
			$this->settings->set(':stores', $multi_store, ['type' => 'array']);

			return true;
		}

		return false;
	}
}
