<?php declare(strict_types=1);

namespace BulkGate\PrestaSms\Database;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Database\ResultCollection, Strict, Database};
use Doctrine\DBAL;

class Connection //implements Database\Connection
{
	use Strict;

	private $db;

	/**
	 * @var list<string>
	 */
	private array $sql = [];

	public function __construct(DBAL\Connection $db)
	{
		$this->db = $db;
	}


	public function execute(string $sql, array $parameters = []): ?ResultCollection
	{
		$output = new ResultCollection();

		$this->sql[] = $sql;

		$result = $this->db->executeQuery($sql, $parameters)->fetchAllAssociative();

        foreach ($result as $key => $item)
        {
            $output[$key] = (array) $item;
        }

		return $output;
	}

	public function lastId()
	{
        return $this->db->Insert_ID();
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
