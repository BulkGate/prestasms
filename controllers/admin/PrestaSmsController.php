<?php

use BulkGate\PrestaSms, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
abstract class PrestaSmsController extends ModuleAdminController
{
    /** @var PrestaSms\DIContainer */
    protected $ps_di;

    /** @var PrestaSms\PrestaSMS */
    protected $ps_module;

    /** @var Extensions\Settings */
    protected $ps_settings;

    /** @var Extensions\Translator */
    protected $ps_translator;

    /** @var array */
    protected $ps_proxy = array();

    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';

        parent::__construct();

        if (!$this->module->active)
        {
            \Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->ps_di = new PrestaSms\DIContainer(\Db::getInstance());

        $this->ps_module = $this->ps_di->getModule();
        $this->ps_settings = $this->ps_di->getSettings();
        $this->ps_translator = $this->ps_di->getTranslator();
        $this->ps_proxy = new PrestaSms\ProxyGenerator($this->controller_name, \Tools::getAdminTokenLite($this->controller_name));
    }

    public function setMedia($isNewTheme = false)
    {
        $this->addCSS(array(
            $this->ps_module->getUrl('/dist/css/devices.min.css'),
            $this->ps_module->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/css/bulkgate-prestasms.css'),
            'https://fonts.googleapis.com/icon?family=Material+Icons|Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i'
        ));
        parent::setMedia($isNewTheme);
    }

    protected function prestaSmsView($presenter, $action, $box = false)
    {
        $this->synchronize();
        $this->context->smarty->registerPlugin('modifier', 'prestaSmsEscapeHtml', array('BulkGate\Extensions\Escape', 'html'));
        $this->context->smarty->registerPlugin('modifier', 'prestaSmsEscapeJs', array('BulkGate\Extensions\Escape', 'js'));
        $this->context->smarty->registerPlugin('modifier', 'prestaSmsEscapeUrl', array('BulkGate\Extensions\Escape', 'url'));
        $this->context->smarty->registerPlugin('modifier', 'prestaSmsEscapeHtmlAttr', array('BulkGate\Extensions\Escape', 'htmlAttr'));
        $this->context->smarty->registerPlugin('modifier', 'prestaSmsTranslate', array($this->ps_di->getTranslator(), 'translate'));

        return $this->prestaSmsTemplate(array(
            'application_id' => $this->ps_settings->load('static:application_id', ''),
            'language' => $this->ps_settings->load('main:language', 'en'),
            'presenter' => $presenter,
            'action' => $action,
            'title' => $this->meta_title,
            'mode' => defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist',
            'box' => $box,
            'widget_api_url' => $this->ps_module->getUrl('/'.(defined('BULKGATE_DEV_MODE') ? 'dev' : 'dist').'/widget-api/widget-api.js'),
            'logo' => $this->ps_module->getUrl('/images/products/ps.svg'),
            'proxy' => $this->ps_proxy->get(),
            'salt' => Extensions\Compress::compress(PrestaSms\Helpers::generateTokens()),
            'authenticate' => array(
                'ajax' => true,
                'controller' => $this->controller_name,
                'action' => 'authenticate',
                'token'  => \Tools::getAdminTokenLite($this->controller_name),
            ),
            'homepage' => $this->context->link->getAdminLink('AdminPrestaSmsDashboardDefault'),
            'info' => $this->ps_module->info()
        ));
    }

    public function ajaxProcessAuthenticate()
    {
        try
        {
            Extensions\JsonResponse::send($this->ps_di->getProxy()->authenticate());
        }
        catch (Extensions\IO\AuthenticateException $e)
        {
            Extensions\JsonResponse::send(array('redirect' => $this->context->link->getAdminLink('AdminPrestaSmsSignIn')));
        }
    }

    protected function prestaSmsTemplate(array $data = array(), $template = 'base')
    {
        return $this->context->smarty->createTemplate(_BG_PRESTASMS_DIR_.'/templates/'.$template.'.tpl', null, null, $data)->fetch();
    }

    protected function synchronize($now = false)
    {
        if($this->ps_settings->load('static:application_token'))
        {
            $status = $this->ps_module->statusLoad(); $language = $this->ps_module->languageLoad(); $store = $this->ps_module->storeLoad();

            $now = $now || $status || $language || $store;

            try
            {
                $this->ps_di->getSynchronize()->run($this->ps_module->getUrl('/module/settings/synchronize'), $now);

                return true;
            }
            catch (Extensions\IO\InvalidResultException $e)
            {
            }
        }
        return false;
    }

    protected function _($translate, $default = null)
    {
        return $this->ps_translator->translate($translate, $default);
    }
}
