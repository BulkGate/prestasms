<?php declare(strict_types=1);

namespace BulkGate\Plugin\DI;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use ArrayAccess, Countable;
use BulkGate\Plugin\Strict;
use ReflectionClass, ReflectionException;
use function array_key_exists, class_exists, interface_exists, is_array, is_string, is_subclass_of, uniqid, count;

/**
 * @implements ArrayAccess<string, object|class-string<object>|array{name: string, factory?: class-string<object>, auto_wiring?: bool, parameters?: array<string, mixed>, factory_method?: callable(mixed ...$parameters):object|null, instantiable: bool, factory_method?: callable(mixed ...$parameters):object|null}>
 */
abstract class ContainerBase implements ArrayAccess, Countable
{
	use Strict;

	/**
	 * @var array<string, array{name: string, factory?: class-string<object>, auto_wiring?: bool, parameters?: array<string, mixed>, reflection: ReflectionClass<object>, instantiable: bool, factory_method?: callable(mixed ...$parameters):object|null}>
	 */
	private array $services = [];

	/**
	 * @var array<class-string<object>, array{name: string, factory?: class-string<object>, auto_wiring?: bool, parameters?: array<string, mixed>, reflection: ReflectionClass<object>, instantiable: bool, factory_method?: callable(mixed ...$parameters):object|null}>
	 */
	private array $auto_wiring = [];

	/**
	 * @var string 'strict'|'rewrite'|'ignore'
	 */
	private string $auto_wiring_mode;

	/**
	 * @var array<string, object>
	 */
	private array $instances = [];


	/**
	 * @param string $auto_wiring_mode 'strict'|'rewrite'|'ignore'
	 */
	public function __construct(string $auto_wiring_mode = 'ignore')
	{
		$this->auto_wiring_mode = $auto_wiring_mode;
	}


	/**
	 * @param array<array-key, mixed> $config
	 */
	public function setConfig(array $config): void
	{
		foreach ($config as $name => $service) if (is_string($service) || is_array($service))
		{
			/**
			 * @var class-string<object>|array{factory?: class-string<object>, parameters?: array<string, mixed>, wiring?: class-string<object>, auto_wiring?: bool, factory_method?: callable(mixed ...$parameters):object|null} $service
			 */
			$this[is_string($name) ? $name : uniqid('class-')] = $service;
		}
	}


	/**
	 * @param class-string<object> $factory
	 * @param array<string, mixed> $parameters
	 * @param class-string<object>|null $wiring
	 * @throws AutoWiringException|InvalidStateException
	 */
	public function add(string $factory, array $parameters = [], ?string $name = null, ?string $wiring = null, bool $auto_wiring = true, ?callable $factory_method = null): void
	{
		try
		{
			$name ??= uniqid('class-');

			$this->services[$name] = [
				'name' => $name,
				'factory' => $factory,
				'auto_wiring' => $auto_wiring,
				'parameters' => $parameters,
				'reflection' => $reflection = new ReflectionClass($factory),
				'instantiable' => $reflection->isInstantiable(),
				'factory_method' => $factory_method
			];

			$this->setAutoWiring($factory, $name);

			if ($wiring !== null && interface_exists($wiring) && is_subclass_of($factory, $wiring))
			{
				$this->setAutoWiring($wiring, $name);
			}
			else if ($auto_wiring) foreach ($this->services[$name]['reflection']->getInterfaces() as $interface)
			{
				/**
				 * @var class-string<object> $interface_factory
				 */
				$interface_factory = $interface->getName();

				$this->setAutoWiring($interface_factory, $name);
			}
		}
		catch (ReflectionException $e)
		{
			throw new InvalidStateException($e->getMessage());
		}
	}

	/**
	 * @param class-string<object> $factory
	 * @throws AutoWiringException
	 */
	public function setAutoWiring(string $factory, string $name): void
	{
		if (!array_key_exists($factory, $this->auto_wiring) || $this->auto_wiring_mode === 'rewrite')
		{
			$this->auto_wiring[$factory] = &$this->services[$name];
		}
		else if ($this->auto_wiring_mode === 'strict')
		{
			throw new AutoWiringException("Auto wiring conflict: '$factory' is already registered");
		}
	}


	/**
	 * @template TClassObject
	 * @param class-string<TClassObject> $class
	 * @return TClassObject&object
	 */
	public function getByClass(string $class): object
	{
		if (!isset($this->auto_wiring[$class]))
		{
			throw new MissingServiceException("Service '$class' not found");
		}

		/**
		 * @var TClassObject&object $service
		 */
		$service = $this->getService($this->auto_wiring[$class]['name']);

		return $service;
	}


	/**
	 * @return object
	 * @throws MissingServiceException|MissingParameterException
	 */
	public function getService(string $name): object
	{
		if (isset($this->instances[$name]))
		{
			return $this->instances[$name];
		}

		/**
		 * @var array{factory: class-string<object>, parameters: array<string, mixed>, reflection: ReflectionClass<object>, instantiable: bool, factory_method?: callable(mixed ...$parameters):object|null}|null $service
		 */
		$service = $this->services[$name] ?? null;

		if ($service === null)
		{
			throw new MissingServiceException("Service '$name' not found");
		}

		/**
		 * @var class-string<object> $factory
		 */
		$factory = $service['factory'];

		$parameters = [];

		try
		{
			$constructor = $service['reflection']->getMethod('__construct');

			foreach ($constructor->getParameters() as $parameter)
			{
				$type_reflection = $parameter->getType() ?? null;

				/**
				 * @var string|null $parameter_type
				 */
				$parameter_type = $type_reflection instanceof \ReflectionNamedType ? $type_reflection->getName() : null;

				if (isset($service['parameters'][$parameter->getName()]) && $service['parameters'][$parameter->getName()] instanceof $parameter_type)
				{
					$parameters[] = $service['parameters'][$parameter->getName()];
				}
				else if ($parameter_type !== null && (class_exists($parameter_type) || interface_exists($parameter_type)))
				{
					/**
					 * @var class-string<object> $parameter_type
					 */
					$parameters[] = $this->getByClass($parameter_type);
				}
				else
				{
					$static_parameter = $service['parameters'][$parameter->getName()] ?? null;

					if ($static_parameter === null && !isset($service['factory_method']))
					{
						$parameter_type ??= 'unknown';

						throw new MissingParameterException("Missing '$parameter_type' parameter '$factory::\${$parameter->getName()}'");
					}

					$parameters[] = $static_parameter;
				}
			}

			return $this->createInstance($factory, $name, $service['factory_method'] ?? null, ...$parameters);
		}
		catch (ReflectionException $e)
		{
			return $this->createInstance($factory, $name, $service['factory_method'] ?? null);
		}
	}


	/**
	 * @param class-string<object> $factory
	 * @param string $name
	 * @param callable(mixed ...$parameters):object|null $factory_method
	 * @param mixed ...$parameters
	 * @return object
	 * @throws MissingParameterException
	 */
	private function createInstance(string $factory, string $name, ?callable $factory_method, ...$parameters): object
	{
		if ($factory_method !== null)
		{
			$instance = $factory_method(...$parameters);

			if (!$instance instanceof $factory)
			{
				throw new MissingParameterException("Factory method must return instance of '$factory'");
			}

			return $this->instances[$name] = $instance;
		}
		else
		{
			return $this->instances[$name] = new $factory(...$parameters);
		}
	}


	/**
	 * @param array-key $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->services[$offset]);
	}


	/**
	 * @param array-key $offset
	 * @throws MissingServiceException|MissingParameterException
	 */
	public function offsetGet($offset): object
	{
		return $this->getService($offset);
	}


	/**
	 * @param array-key $offset
	 * @param class-string<object>|array{factory?: class-string<object>, parameters?: array<string, mixed>, wiring?: class-string<object>, auto_wiring?: bool, factory_method?: callable(mixed ...$parameters):object|null} $value
	 * @throws AutoWiringException|InvalidStateException
	 */
	public function offsetSet($offset, $value): void
	{
		if (is_string($value) && (class_exists($value) || interface_exists($value)))
		{
			$this->add($value, [], $offset);
		}
		else if (is_array($value) && isset($value['factory']))
		{
			$this->add($value['factory'], $value['parameters'] ?? [], $offset, $value['wiring'] ?? null, $value['auto_wiring'] ?? true, $value['factory_method'] ?? null);
		}
		else
		{
			throw new InvalidStateException('Invalid service factory');
		}
	}

	/**
	 * @param array-key $offset
	 * @throws InvalidStateException
	 */
	public function offsetUnset($offset): void
	{
		throw new InvalidStateException('Invalid unset operation');
	}


	public function count(): int
	{
		return count($this->services);
	}
}
