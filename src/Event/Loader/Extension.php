<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Hook;
use BulkGate\{Plugin\Event\DataLoader, Plugin\Event\Variables, Plugin\Strict, PrestaSms\Database\Connection};

class Extension implements DataLoader
{
	use Strict;

	private Connection $database;

	public function __construct(Connection $database)
	{
		$this->database = $database;
	}


	public function load(Variables $variables, array $parameters = []): void
	{
		Hook::exec('actionPrestaSmsExtendsVariables', [
			'variables' => $variables,
			'database' => $this->database,
		], null, false, true, false, $variables['shop_id']);
	}
}
