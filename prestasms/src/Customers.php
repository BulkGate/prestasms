<?php
namespace BulkGate\PrestaSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Customers extends Extensions\Customers
{
    public function __construct(Extensions\Database\IDatabase $db)
    {
        parent::__construct($db);
        $this->table_user_key = 'id_customer';
    }


    public function getTotal()
    {
        return (int) $this->db->execute("SELECT COUNT(`id_customer`) AS `total` FROM `{$this->db->table('address')}` WHERE `id_customer` != 0 AND (`phone_mobile` OR `phone`) AND `active` = 1")->getRow()->total;
    }


    public function getFilteredTotal(array $customers)
    {
        return (int) $this->db->execute("SELECT COUNT(`id_customer`) AS `total` FROM `{$this->db->table('address')}` WHERE `id_customer` IN ('".implode("','", $customers)."') AND (`phone_mobile` OR `phone`) AND `active` = 1")->getRow()->total;
    }


    protected function loadCustomers(array $customers, $limit = null)
    {
        return $this->db->execute("
                SELECT 
                    `{$this->db->table('customer')}`.`email`,
                    `{$this->db->table('address')}`.`firstname` AS `first_name`,
                    `{$this->db->table('address')}`.`lastname` AS `last_name`,
                    `{$this->db->table('address')}`.`address1` AS `street1`,
                    `{$this->db->table('address')}`.`address2` AS `street2`,
                    `{$this->db->table('address')}`.`postcode` AS `zip`,
                    `{$this->db->table('address')}`.`city`,
                    `{$this->db->table('address')}`.`phone_mobile`,
                    `{$this->db->table('address')}`.`phone`,
                    `{$this->db->table('address')}`.`company` AS `company_name`,
                    `{$this->db->table('address')}`.`vat_number` AS `company_vat`,
                     LOWER(`{$this->db->table('country')}`.`iso_code`) AS `country`,
                    `{$this->db->table('state')}`.`name` AS `state`
                FROM `{$this->db->table('customer')}`
                LEFT JOIN `{$this->db->table('address')}` ON `{$this->db->table('customer')}`.`id_customer` = `{$this->db->table('address')}`.`id_customer`
                LEFT JOIN `{$this->db->table('country')}` ON `{$this->db->table('address')}`.`id_country` = `{$this->db->table('country')}`.`id_country`
                LEFT JOIN `{$this->db->table('state')}` ON `{$this->db->table('address')}`.`id_state` = `{$this->db->table('state')}`.`id_state`
                WHERE 
                    ". (count($customers) > 0 ? "`{$this->db->table('customer')}`.`id_customer` IN ('".implode("','", $customers)."') AND" : "") . "
                    `{$this->db->table('address')}`.`active` = 1 AND 
                    (`{$this->db->table('address')}`.`phone_mobile` OR `{$this->db->table('address')}`.`phone`)
                    ". ($limit !== null ? ("LIMIT ".(int) $limit) : "")
        )->getRows();
    }


    protected function filter(array $filters)
    {
        $customers = array(); $filtered = false;

        foreach($filters as $key => $filter)
        {
            if(isset($filter['values']) && count($filter['values']) > 0 && !$this->empty)
            {
                switch ($key)
                {
                    case 'first_name':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('address')}` WHERE {$this->getSql($filter, 'firstname')}"), $customers);
                        break;
                    case 'last_name':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('address')}` WHERE {$this->getSql($filter, 'lastname')}"), $customers);
                        break;
                    case 'country':
                        $customers = $this->getCustomers($this->db->execute("SELECT `{$this->db->table('address')}`.`id_customer` FROM `{$this->db->table('address')}` LEFT JOIN `{$this->db->table('country')}` ON `{$this->db->table('address')}`.`id_country` = `{$this->db->table('country')}`.`id_country` WHERE {$this->getSql($filter, 'iso_code', 'country')}"), $customers);
                        break;
                    case 'city':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('address')}` WHERE {$this->getSql($filter, 'city')}"), $customers);
                        break;
                    case 'company_name':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('address')}` WHERE {$this->getSql($filter, 'company')}"), $customers);
                        break;
                    case 'gender':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'id_gender')}"), $customers);
                        break;
                    case 'advert':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'optin')}"), $customers);
                        break;
                    case 'newsletter':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'newsletter')}"), $customers);
                        break;
                    case 'order_amount':
                        $customers = $this->getCustomers($this->db->execute("SELECT id_customer, MAX(`total_paid`) AS `total` FROM `{$this->db->table('orders')}` GROUP BY `{$this->db->table('orders')}`.`id_customer` HAVING {$this->getSql($filter, 'total')}"), $customers);
                        break;
                    case 'all_orders_amount':
                        $customers = $this->getCustomers($this->db->execute("SELECT id_customer, SUM(`total_paid`) AS `total` FROM `{$this->db->table('orders')}` GROUP BY `{$this->db->table('orders')}`.`id_customer` HAVING {$this->getSql($filter, 'total')}"), $customers);
                        break;
                    case 'product':
                        $customers = $this->getCustomers($this->db->execute("SELECT `{$this->db->table('orders')}`.`id_customer` FROM `{$this->db->table('order_detail')}` INNER JOIN `{$this->db->table('orders')}` ON `{$this->db->table('orders')}`.`id_order` = `{$this->db->table('order_detail')}`.`id_order` INNER JOIN `{$this->db->table('product_lang')}` ON `{$this->db->table('product_lang')}`.`id_product` = `{$this->db->table('order_detail')}`.`product_id` WHERE {$this->getSql($filter, 'name', 'product_lang')} GROUP BY `{$this->db->table('orders')}`.`id_customer`"), $customers);
                        break;
                    case 'born_date':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'birthday')}"), $customers);
                        break;
                    case 'product_reference':
                        $customers = $this->getCustomers($this->db->execute("SELECT `{$this->db->table('orders')}`.`id_customer` FROM `{$this->db->table('order_detail')}` INNER JOIN `{$this->db->table('orders')}` ON `{$this->db->table('orders')}`.`id_order` = `{$this->db->table('order_detail')}`.`id_order` INNER JOIN `{$this->db->table('product')}` ON `{$this->db->table('product')}`.`id_product` = `{$this->db->table('order_detail')}`.`product_id` WHERE {$this->getSql($filter, 'reference', 'product')} GROUP BY `{$this->db->table('orders')}`.`id_customer`"), $customers);
                        break;
                    case 'registration_date':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'date_add')}"), $customers);
                        break;
                    case 'order_date':
                        $customers = $this->getCustomers($this->db->execute("SELECT `id_customer` FROM `{$this->db->table('orders')}` WHERE {$this->getSql($filter, 'date_add')}"), $customers);
                        break;
                }
                $filtered = true;
            }
        }
        file_put_contents(__DIR__.'/log.log', implode("\n", $this->db->getSqlList()));
        return array(array_unique($customers), $filtered);
    }
}
