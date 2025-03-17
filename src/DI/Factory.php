<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\DI;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\PrestaSms\Database\Connection;
use BulkGate\Plugin\{DI\Container, DI\Factory as DIFactory, DI\FactoryStatic, Settings, Strict};

class Factory implements DIFactory
{
	use Strict;
	use FactoryStatic;

	/**
	 * @param array<string, mixed> $parameters
	 */
	protected static function createContainer(array $parameters = []): Container
	{
		$container = new Container($parameters['mode'] ?? 'strict');

		// Database
		$container['database.connection'] = ['factory' => Connection::class, 'parameters' => ['db' => $parameters['db']]];

		// Settings
		$container['settings.repository.database'] = Settings\Repository\SettingsDatabase::class;
		$container['settings.repository.synchronizer'] = Settings\Repository\SynchronizationDatabase::class;
		$container['settings.settings'] = Settings\Settings::class;
		$container['settings.synchronizer'] = Settings\Synchronizer::class;

		return $container;
	}
}
