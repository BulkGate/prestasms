<?php
namespace BulkGate\PrestaSms;

use BulkGate\Extensions\Database\Result;
use BulkGate\Extensions\Json;
use BulkGate\Extensions\Escape;
use BulkGate\Extensions\IModule;
use BulkGate\Extensions\ISettings;
use BulkGate\Extensions\Strict;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class PrestaSMS extends Strict implements IModule
{
    const PRODUCT = 'ps';

    private $info = array(
        'store' => 'PrestaShop',
        'name' => _BG_PRESTASMS_NAME_,
        'url' => 'http://www.presta-sms.com',
        'developer' => _BG_PRESTASMS_AUTHOR_,
        'developer_url' => 'http://www.topefekt.com/',
        'description' => 'PrestaSMS module extends your PrestaShop store capabilities and creates new opportunities for your business. You can promote your products and sales via personalized bulk SMS. Make your customers happy by notifying them about order status change via SMS notifications. Receive an SMS whenever a new order is placed, a product is out of stock, and much more.',
    );

    /** @var ISettings */
    private $settings;

    /** @var Database */
    private $db;

    /** @var array */
    private $plugin_data = array();

    public $id;

    public function __construct(ISettings $settings, Database $db)
    {
        $this->settings = $settings;
        $this->db = $db;
    }

    public function getUrl($path = '')
    {
        if(defined('BULKGATE_DEBUG'))
        {
            return Escape::url(BULKGATE_DEBUG.$path);
        }
        else
        {
            return Escape::url('https://portal.bulkgate.com'.$path);
        }
    }

    public function statusLoad()
    {
        $actual = array();
        $context = \Context::getContext();

        /** @var Result $result */
        $result = $this->db->execute("SELECT `".$this->db->table('order_state')."`.`id_order_state`, `".$this->db->table('order_state_lang')."`.`name` FROM `".$this->db->table('order_state')."` INNER JOIN `".$this->db->table('order_state_lang')."` ON `".$this->db->table('order_state')."`.`id_order_state` = `".$this->db->table('order_state_lang')."`.`id_order_state` WHERE `".$this->db->table('order_state_lang')."`.`id_lang` = '". (int)$context->language->id."'");

        foreach ($result as $store)
        {
            $actual[$store->id_order_state] = $store->name;
        }

        $status_list = (array) $this->settings->load(':order_status_list', null);


        if($status_list !== $actual)
        {
            $this->settings->set(':order_status_list', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }

    public function languageLoad()
    {
        if((bool) $this->settings->load('main:language_mutation'))
        {
            $languages = (array) $this->settings->load(':languages', null);
            $actual = array();

            foreach(\Language::getLanguages(true) as $language)
            {
                if(isset($language['id_lang']) && isset($language['name']))
                {
                    $actual[$language['id_lang']] = $language['name'];
                }
            }

            if($languages !== $actual)
            {
                $this->settings->set(':languages', Json::encode($actual), array('type' => 'json'));
                return true;
            }
            return false;
        }
        else
        {
            $this->settings->set(':languages', Json::encode(array('default' => 'Default')), array('type' => 'json'));
            return true;
        }
    }

    public function storeLoad()
    {
        $actual = array();

        /** @var Result $stores */
        $result = $this->db->execute("SELECT `id_shop`, `name` FROM `".$this->db->table('shop')."` WHERE `active` = '1' LIMIT 200");

        foreach ($result as $store)
        {
            $actual[$store->id_shop] = $store->name;
        }

        $stores = (array) $this->settings->load(':stores', null);

        if($stores !== $actual)
        {
            $this->settings->set(':stores', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }

    public function product()
    {
        return self::PRODUCT;
    }

    public function url()
    {
        return _PS_BASE_URL_;
    }

    public function info($key = null)
    {
        if(empty($this->plugin_data))
        {
            $this->plugin_data = array_merge(
                array(
                    'store_version' => defined('_BG_PRESTASMS_PS_MIN_VERSION_') ? (_BG_PRESTASMS_PS_MIN_VERSION_.'+') : '1.7',
                    'version' => _BG_PRESTASMS_VERSION_,
                    'application_id' => $this->settings->load('static:application_id', -1),
                    'application_product' => $this->product(),
                    'delete_db' => $this->settings->load('main:delete_db', 0),
                    'language_mutation' => $this->settings->load('main:language_mutation', 0)
                ),
                $this->info
            );
        }
        if($key === null)
        {
            return $this->plugin_data;
        }
        return isset($this->plugin_data[$key]) ? $this->plugin_data[$key] : null;
    }
}
