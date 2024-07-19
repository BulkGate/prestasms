<?php declare(strict_types=1);

namespace BulkGate\Plugin\Settings;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Settings\Repository\Entity\Setting, Strict, Structure\Collection};
use function array_key_exists, array_merge, preg_match;

class Settings
{
	use Strict;

	/**
	 * @var array<string, Collection<string, Repository\Entity\Setting>>
	 */
	private array $settings = [];

	/**
	 * @var array<string, mixed>
	 */
	private array $default_settings = [];

	private Repository\Settings $repository;

	public function __construct(Repository\Settings $repository)
	{
		$this->repository = $repository;
	}


	/**
	 * @param array<array-key, mixed> $settings
	 */
	public function setDefaultSettings(array $settings): void
	{
		$this->default_settings = [];

		foreach ($settings as $key => $value) if (is_string($key) && preg_match('~^[\w_-]+?:?[\w_-]+?$~U', $key))
		{
			$this->default_settings[$key] = $value;
		}
	}



	/**
	 * @return mixed
	 */
	public function load(string $settings_key, bool $reload = false)
	{
		[$scope, $key] = Helpers::key($settings_key);

		if (!array_key_exists($scope, $this->settings) || $reload)
		{
			$this->settings[$scope] = $this->repository->load($scope);
		}

		if ($key !== null)
		{
			if (isset($this->settings[$scope][$key]) && $this->settings[$scope][$key] instanceof Setting)
			{
				return ($this->settings[$scope][$key]->value);
			}
			return $this->default_settings[$settings_key] ?? null;
		}
		return $this->settings[$scope]->toArray();
	}


	/**
	 * @param mixed $value
	 * @param array<string, mixed> $parameters
	 */
	public function set(string $settings_key, $value, array $parameters): void
	{
		[$scope, $key] = Helpers::key($settings_key);

		$this->repository->save(new Repository\Entity\Setting(array_merge($parameters, [
			'scope' => $scope,
			'key' => $key,
			'value' => $value
		])));
	}


	public function delete(string $settings_key): void
	{
		[$scope, $key] = Helpers::key($settings_key);

		if ($key !== null)
		{
			$this->repository->remove($scope, $key);
		}
	}


	public function cleanup(): void
	{
		$this->repository->cleanup();
	}


	public function install(bool $update = false): void
	{
		$this->repository->createTable();

		if (!$update)
		{
			$this->set('static:synchronize', 0, ['type' => 'int']);
		}
	}


	public function uninstall(): void
	{
		if ($this->load('main:delete_db') ?? false)
		{
			$this->repository->dropTable();
		}
	}
}
