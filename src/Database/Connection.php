<?php

declare(strict_types=1);

namespace BulkGate\PrestaShop\Database;

use BulkGate\Plugin\Database;
use BulkGate\Plugin\Database\ResultCollection;
use BulkGate\Plugin\Strict;
use Doctrine\DBAL;

/**
 * @author Martin Kreizl 2025 TOPefekt s.r.o.
 *
 * @see https://www.bulkgate.com/
 */
class Connection implements Database\Connection
{
    use Strict;

    private DBAL\Connection $db;

    /**
     * @var array<array-key, mixed>
     */
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

        $result = \method_exists($query, 'fetchAllAssoc') ? $query->fetchAllAssoc() : $query->fetchAll(\PDO::FETCH_ASSOC);

        $this->prepare_parameters = [];

        foreach ($result as $key => $item) {
            $output[$key] = (array) $item;
        }

        return $output;
    }

    public function lastId()
    {
        /**
         * @var mixed $id
         */
        $id = $this->db->lastInsertId();

        if (!is_string($id) && !is_int($id)) {
            return 0;
        }

        return $id;
    }

    public function prefix(): string
    {
        /**
         * @var literal-string $prefix
         */
        $prefix = _DB_PREFIX_;

        return $prefix;
    }

    public function getSqlList(): array
    {
        return $this->sql;
    }

    public function table(string $table): string
    {
        return $this->prefix() . $table;
    }

    /**
     * @param mixed ...$parameters
     */
    public function prepare(string $sql, ...$parameters): string
    {
        $this->prepare_parameters = $parameters;

        // plugin's SQL queries are using %s for placeholder values, but Doctrine uses "?" character as placeholder
        /**
         * @var literal-string $s
         */
        $s = str_replace('%s', '?', $sql);

        return $s;
    }

    public function escape(string $string): string
    {
        /**
         * @var literal-string $s
         */
        $s = (string) $this->db->quote($string);

        return $s;
    }
}
