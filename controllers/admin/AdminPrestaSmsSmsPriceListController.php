<?php

require_once __DIR__.'/../../src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsSmsPriceListController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('price_list', 'Price list');
    }

    public function renderView()
    {
        return $this->prestaSmsView("SmsPrice", "list");
    }
}
