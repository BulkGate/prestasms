<?php
namespace BulkGate\PrestaSms;

use BulkGate\Extensions, PrestaShopBundle;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Helpers extends Extensions\Strict
{
    public static function installModuleTab($class, $name, $parent, $icon = '')
    {
        $tab = new \Tab();

        foreach(\Language::getLanguages() as $id => $language)
        {
            if(isset($language['id_lang']))
            {
                $tab->name[$language['id_lang']] = $name;
            }
        }

        $tab->class_name = $class;
        $tab->module = _BG_PRESTASMS_SLUG_;
        $tab->id_parent = $parent;
        $tab->icon = $icon;

        $tab->save();

        return $tab->id;
    }

    public static function uninstallModuleTab($class)
    {
        $id = \Tab::getIdFromClassName($class);

        if($id !== 0)
        {
            $tab = new \Tab($id);
            $tab->delete();
            return true;
        }
        return false;
    }

    public static function generateTokens()
    {
        $output = array();

        $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_tab`, `class_name`, `module` FROM `'._DB_PREFIX_.'tab` WHERE `module` = \''._BG_PRESTASMS_SLUG_.'\'', true, false);

        if (is_array($result)) {
            foreach ($result as $row) {
                $output[$row['class_name']] = \Tools::getAdminToken($row['class_name'].(int)$row['id_tab'].(int)\Context::getContext()->employee->id);
            }
        }
        return $output;
    }

    public static function installMenu(Extensions\Translator $translator)
    {
        $main = Helpers::installModuleTab('PRESTASMS', $translator->translate('presta_sms', 'PrestaSMS'), 0,  'mail_outline');

        Helpers::installModuleTab('AdminPrestaSms', $translator->translate('dashboard','BulkGate SMS'), $main, 'desktop_windows');
    }

    public static function uninstallMenu()
    {
        Helpers::uninstallModuleTab('PRESTASMS');
        Helpers::uninstallModuleTab('AdminPrestaSms');
    }

    public static function getOrderPhoneNumber($order_id)
    {
        $phone_number = null; $iso = null;

        $order = new \Order($order_id);
        $address_delivery = new \Address($order->id_address_delivery);

        $phone_number = $address_delivery->phone_mobile ?: $address_delivery->phone;

        $country = new \Country($address_delivery->id_country);

        $iso = strtolower($country->iso_code);

        if(strlen(trim($phone_number)) === 0)
        {
            $address_invoice = new \Address($order->id_address_invoice);

            $phone_number = $address_invoice->phone_mobile ?: $address_invoice->phone;

            $country_invoice = new \Country($address_invoice->id_country);

            $iso = strtolower($country_invoice->iso_code);
        }

        return array($phone_number, $iso);
    }
}
