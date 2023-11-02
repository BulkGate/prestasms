<?php
namespace BulkGate\PrestaSms;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Database extends BulkGate\Extensions\Strict implements BulkGate\Extensions\Database\IDatabase
{
    /** @var \Db */
    private $db;

    private $sql = array();

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    public function execute($sql)
    {
        $output = array();

        $this->sql[] = $sql;

        if(strpos(strtolower(trim($sql)), "select") === 0)
        {
            $result = $this->db->executeS($sql);

            if(is_array($result) && count($result))
            {
                foreach ($result as $key => $item)
                {
                    $output[$key] = (object) $item;
                }
            }
        }
        else
        {
            $this->db->execute($sql);
        }

        return new BulkGate\Extensions\Database\Result($output);
    }

    public function lastId()
    {
        return $this->db->Insert_ID();
    }

    public function escape($string)
    {
        return $this->db->_escape($string);
    }

    public function prefix()
    {
        return _DB_PREFIX_;
    }

    public function getSqlList()
    {
        return $this->sql;
    }

    public function prepare($sql, array $params = [])
    {
        foreach($params as $param)
        {
            $sql = preg_replace("/%s/", "'".$this->db->_escape((string) $param)."'", $sql, 1);
        }
        return $sql;
    }

    public function table($table)
    {
        return $this->prefix().$table;
    }
}
