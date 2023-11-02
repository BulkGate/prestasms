<?php

require_once __DIR__.'/../../src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsHistoryListController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('history', 'History');
    }

    public function renderView()
    {
        return $this->prestaSmsView("History", "list", true);
    }
}
