<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\DI;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{
	Plugin\DI\FactoryStatic,
	Plugin\Exception,
	Plugin\Settings,
	Plugin\Strict,
	Plugin\DI\Container,
	Plugin\DI\Factory as DIFactory,
	};
use BulkGate\PrestaSms\Database\Connection;
use BulkGate\PrestaSms\Settings\Repository\{SettingsDatabase, SynchronizationDatabase};

class Factory implements DIFactory
{
	use Strict;
	use FactoryStatic;

	/**
	 * @param array<string, mixed> $parameters
	 * @throws Exception
	 */
	protected static function createContainer(array $parameters = []): Container
	{
		$container = new Container($parameters['mode'] ?? 'strict');

		/*$parameters['language'] ??= 'en';

		// Ajax
		$container['ajax.authenticate'] = Authenticate::class;
		$container['ajax.plugin_settings'] = PluginSettingsChange::class;

		// Debug
		$container['debug.repository.logger'] = ['factory' => LoggerSettings::class, 'factory_method' => function () use ($container, $parameters): LoggerSettings
		{
			$service = new LoggerSettings($container->getByClass(Settings\Settings::class));
			$service->setup(is_int($parameters['logger_limit'] ?? null) ? $parameters['logger_limit'] : 100);
			return $service;
		}];

		// Eshop
		$container['eshop.synchronizer'] = Eshop\EshopSynchronizer::class;
		$container['eshop.configuration'] = ['factory' => Eshop\Configuration::class, 'factory_method' => fn () => new ConfigurationWordpress($parameters['plugin_data'] ?? [], $parameters['url'], $parameters['name'] ?? 'Store')];
		$container['eshop.order_status'] = OrderStatusWordpress::class;
		$container['eshop.return_status'] = ReturnStatusWordpress::class;
        $container['eshop.language'] = LanguageWordpress::class;
        $container['eshop.multistore'] = MultiStoreWordpress::class;

        // Event loaders
		$container['event.loader.extension'] = ['factory' => Extension::class, 'auto_wiring' => false];
		$container['event.loader.shop'] = ['factory' => Shop::class, 'auto_wiring' => false];
		$container['event.loader.order'] = ['factory' => Order::class, 'auto_wiring' => false];
		$container['event.loader.order_status'] = ['factory' => OrderStatus::class, 'auto_wiring' => false];
		$container['event.loader.customer'] = ['factory' => Customer::class, 'auto_wiring' => false];
		$container['event.loader.product'] = ['factory' => Product::class, 'auto_wiring' => false];
		$container['event.loader.post'] = ['factory' => Post::class, 'auto_wiring' => false];

		// Event
		$container['event.hook'] = ['factory' => Event\Hook::class, 'parameters' => ['version' => $parameters['api_version'] ?? '1.0']];
		$container['event.asynchronous.repository'] = Event\Repository\AsynchronousDatabase::class;
		$container['event.asynchronous'] = Event\Asynchronous::class;
		$container['event.loader'] = ['factory' => Event\Loader::class, 'factory_method' => fn () => new Event\Loader([
			$container->getByClass(Order::class),
			$container->getByClass(OrderStatus::class),
			$container->getByClass(Customer::class),
			$container->getByClass(Shop::class),
			$container->getByClass(Product::class),
			$container->getByClass(Post::class),
			$container->getByClass(Extension::class),
		])];
		$container['event.dispatcher'] = Event\Dispatcher::class;*/

        // Database
        $container['database.connection'] = ['factory' => Connection::class, 'parameters' => ['db' => $parameters['db']]];

		// Settings
		$container['settings.repository.database'] = SettingsDatabase::class;
		$container['settings.settings'] = Settings\Settings::class;
		$container['settings.repository.synchronizer'] = SynchronizationDatabase::class;
		$container['settings.synchronizer'] = Settings\Synchronizer::class;

		// User
		//$container['user.sign'] = User\Sign::class;

		return $container;
	}
}
