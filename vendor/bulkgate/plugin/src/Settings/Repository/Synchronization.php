<?php declare(strict_types=1);

namespace BulkGate\Plugin\Settings\Repository;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{AuthenticateException, InvalidResponseException, Structure\Collection};

interface Synchronization
{
	/**
	 * @return Collection<array-key, Entity\Setting>
	 */
	public function loadPluginSettings(): Collection;


	/**
	 * @param Collection<array-key, Entity\Setting> $plugin_settings
	 * @return Collection<array-key, Entity\Setting>
	 * @throws AuthenticateException|InvalidResponseException
	 */
	public function loadServerSettings(string $url, Collection $plugin_settings, int $timeout = 20): Collection;
}
