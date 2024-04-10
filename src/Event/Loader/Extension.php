<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Event\Variables, Strict, Event\DataLoader};
use BulkGate\PrestaSms\Database\Connection;

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
		\Hook::exec('actionPrestaSmsExtendsVariables', [
			'variables' => $variables,
			'database' => $this->database //todo: nejsem si jistej, jestli je tohle vhodny. Ja bych jim asi nedaval nase database API. At si data zajisti zvenku sami.
		], null, false, true, false, $variables['store_id']);
	}
}
