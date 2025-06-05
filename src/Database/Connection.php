<?php

declare(strict_types=1);

namespace BulkGate\PrestaSms\Database;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Database;
use BulkGate\Plugin\Database\ResultCollection;
use BulkGate\Plugin\Strict;
use Doctrine\DBAL;

class Connection implements Database\Connection
{
    use Strict;

    private DBAL\Connection $db;

    private array $prepare_parameters = [];

    /**
     * @var list<string>
     */
    private array $sql = [];

    public function __construct(DBAL\Connection $db)
    {
        $this->db = $db;
    }

    public function execute(string $sql): ?ResultCollection
    {
        $output = new ResultCollection();

        $this->sql[] = $sql;

        $query = $this->db->executeQuery($sql, $this->prepare_parameters);

		if (method_exists($query, 'fetchAllAssoc'))
		{
			$result = $query->fetchAllAssoc();
		}
		else
		{
			$result = $query->fetchAll(\PDO::FETCH_ASSOC);
		}

        $this->prepare_parameters = [];

        foreach ($result as $key => $item) {
            $output[$key] = (array) $item;
        }

        return $output;
    }

    public function lastId()
    {
        return $this->db->lastInsertId();
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

    public function prepare(string $sql, ...$parameters): string
    {
        $this->prepare_parameters = $parameters;

        // plugin's SQL queries are using %s for placeholder values, but Doctrine uses "?" character as placeholder
        return str_replace('%s', '?', $sql);
    }

    public function escape(string $string): string
    {
        return $string;
    }
}
