<?php
namespace BulkGate\PrestaSms;

use BulkGate, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @method Database getDatabase()
 * @method PrestaSMS getModule()
 * @method Customers getCustomers()
 */
class DIContainer extends Extensions\DIContainer
{
    /** @var \Db */
    private $db;

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }


    /**
     * @return Database
     */
    protected function createDatabase()
    {
        return new Database($this->db);
    }


    /**
     * @return PrestaSMS
     */
    protected function createModule()
    {
        return new PrestaSMS($this->getService('settings'), $this->getService('database'));
    }


    /**
     * @return Customers
     */
    protected function createCustomers()
    {
        return new Customers($this->getService('database'));
    }
}
