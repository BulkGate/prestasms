<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Database;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Database\ResultCollection, Strict, Database\Connection};
use function count, is_array;

class ConnectionPrestashop implements Connection
{
	use Strict;

	private $db;

	/**
	 * @var list<string>
	 */
	private array $sql = [];

	public function __construct($db)
	{
		$this->db = $db;
	}


	public function execute(string $sql): ?ResultCollection
	{
		$output = new ResultCollection();

		$this->sql[] = $sql;

		$result = $this->db->executeS($sql);

		if (is_array($result) && count($result) > 0)
		{
			foreach ($result as $key => $item)
			{
				$output[$key] = (array) $item;
			}
		}

		return $output;
	}


	public function prepare(string $sql, ...$parameters): string
	{
        foreach($parameters as $param)
        {
            $sql = preg_replace("/%s/", "'".$this->db->_escape((string) $param)."'", $sql, 1);
        }

        return $sql;
	}


	public function lastId()
	{
        return $this->db->Insert_ID();
	}


	/**
	 * @param scalar|null $string
	 */
	public function escape($string): string
	{
		return $this->db->_escape((string) $string);
	}


	public function prefix(): string
	{
        return _DB_PREFIX_;
	}


	public function getSqlList(): array
	{
		return $this->sql;
	}


	public function table(string $table): string
	{
		return $this->prefix() . $table;
	}
}
