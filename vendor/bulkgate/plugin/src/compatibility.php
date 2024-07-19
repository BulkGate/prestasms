<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

namespace BulkGate\Extensions\Hook;

use BulkGate\Plugin\Event\Variables as PluginVariables;

/**
 * @phpstan-ignore-next-line
 */
if (false)
{
	/**
	 * @deprecated use BulkGate\Plugin\Event\Variables
	 */
	class Variables extends PluginVariables
	{
	}
}

namespace BulkGate\Extensions\Database;

use BulkGate\Plugin\Database\Connection;

/**
 * @phpstan-ignore-next-line
 */
if (false)
{
	/**
	 * @deprecated use BulkGate\Plugin\Database\Connection
	 */
	interface IDatabase extends Connection
	{
	}
}
