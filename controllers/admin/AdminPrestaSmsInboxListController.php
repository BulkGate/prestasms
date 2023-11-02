<?php

require_once __DIR__.'/../../src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsInboxListController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('inbox', 'Inbox');
    }

    public function renderView()
    {
        return $this->prestaSmsView("Inbox", "list", true);
    }
}
