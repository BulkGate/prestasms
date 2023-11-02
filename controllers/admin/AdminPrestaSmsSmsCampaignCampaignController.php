<?php

require_once __DIR__.'/../../src/init.php';

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class AdminPrestaSmsSmsCampaignCampaignController extends PrestaSmsController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = $this->_('sms_campaign', 'Sms Campaign');
    }

    public function renderView()
    {
        $this->ps_proxy->add('loadModuleData', 'loadModuleData', 'campaign');
        $this->ps_proxy->add('saveModuleCustomers', 'saveModuleCustomers', 'campaign');
        $this->ps_proxy->add('addModuleFilter', 'addModuleFilter', 'campaign');
        $this->ps_proxy->add('removeModuleFilter', 'removeModuleFilter', 'campaign');
        return $this->prestaSmsView("SmsCampaign", "campaign", true);
    }

    public function ajaxProcessLoadModuleData()
    {
        $post = \Tools::getValue('__bulkgate');

        Extensions\JsonResponse::send($this->ps_di->getProxy()->loadCustomersCount(
            isset($post['application_id']) ? $post['application_id'] : null,
            isset($post['campaign_id']) ? $post['campaign_id'] : null
        ));
    }

    public function ajaxProcessSaveModuleCustomers()
    {
        $post = \Tools::getValue('__bulkgate');

        Extensions\JsonResponse::send($this->ps_di->getProxy()->saveModuleCustomers(
            isset($post['application_id']) ? $post['application_id'] : null,
            isset($post['campaign_id']) ? $post['campaign_id'] : null
        ));
    }

    public function ajaxProcessAddModuleFilter()
    {
        $post = \Tools::getValue('__bulkgate');

        Extensions\JsonResponse::send($this->ps_di->getProxy()->loadCustomersCount(
            isset($post['application_id']) ? $post['application_id'] : null,
            isset($post['campaign_id']) ? $post['campaign_id'] : null,
            'addFilter',
            $post
        ));
    }

    public function ajaxProcessRemoveModuleFilter()
    {
        $post = \Tools::getValue('__bulkgate');

        Extensions\JsonResponse::send($this->ps_di->getProxy()->loadCustomersCount(
            isset($post['application_id']) ? $post['application_id'] : null,
            isset($post['campaign_id']) ? $post['campaign_id'] : null,
            'removeFilter',
            $post
        ));
    }
}
