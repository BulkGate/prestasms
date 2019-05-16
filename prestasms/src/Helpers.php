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
        $main = Helpers::installModuleTab('PRESTASMS', $translator->translate('prestasms', 'PrestaSMS'), 0,  'mail_outline');
        
        $dashboard = Helpers::installModuleTab('AdminPrestaSmsDashboardDefault', $translator->translate('dashboard','Dashboard'), $main, 'desktop_windows');
        Helpers::installModuleTab('AdminPrestaSmsSmsCampaignNew', $translator->translate('start_campaign','Start Campaign'), $main);
        Helpers::installModuleTab('AdminPrestaSmsSmsCampaignDefault', $translator->translate('campaigns','Campaigns'), $main);
        Helpers::installModuleTab('AdminPrestaSmsInboxList', $translator->translate('inbox','Inbox'), $main);
        Helpers::installModuleTab('AdminPrestaSmsHistoryList', $translator->translate('history','History'), $main);
        Helpers::installModuleTab('AdminPrestaSmsStatisticsDefault', $translator->translate('statistics','Statistics'), $main);
        Helpers::installModuleTab('AdminPrestaSmsBlackListDefault', $translator->translate('black_list','Black list'), $main);
        Helpers::installModuleTab('AdminPrestaSmsSmsPriceList', $translator->translate('price_list', 'Price list'), $main);
        Helpers::installModuleTab('AdminPrestaSmsTopUp', $translator->translate('buy_credit', 'Buy credit'), $main);
        Helpers::installModuleTab('AdminPrestaSmsPaymentList', $translator->translate('invoices', 'Invoices'), $main);
        Helpers::installModuleTab('AdminPrestaSmsWalletDetail', $translator->translate('payments_data', 'Payments data'), $main);
        Helpers::installModuleTab('AdminPrestaSmsUserProfile',  $translator->translate('user_profile', 'User profile'), $main);
        Helpers::installModuleTab('AdminPrestaSmsModuleNotificationsAdmin',  $translator->translate('admin_sms', 'Admin SMS'), $main);
        Helpers::installModuleTab('AdminPrestaSmsModuleNotificationsCustomer',  $translator->translate('customer_sms', 'Customer SMS'), $main);
        Helpers::installModuleTab('AdminPrestaSmsSmsSettingsDefault',  $translator->translate('sender_id_profiles', 'Sender ID Profiles'), $main);
        Helpers::installModuleTab('AdminPrestaSmsModuleSettingsDefault', $translator->translate('module_settings', 'Module settings'), $main);
        Helpers::installModuleTab('AdminPrestaSmsAboutDefault', $translator->translate('about_module','About module'), $main);
        Helpers::installModuleTab('AdminPrestaSmsSignIn', 'SignIn', $dashboard);
        Helpers::installModuleTab('AdminPrestaSmsSignUp', 'SignUp', $dashboard);
        Helpers::installModuleTab('AdminPrestaSmsSmsCampaignCampaign', 'SmsCampaignCampaign', $dashboard);
        Helpers::installModuleTab('AdminPrestaSmsSmsCampaignActive', 'SmsCampaignActive', $dashboard);
        Helpers::installModuleTab('AdminPrestaSmsBlackListImport', 'BlackListImport', $dashboard);
    }

    public static function uninstallMenu()
    {
        Helpers::uninstallModuleTab('PRESTASMS');
        Helpers::uninstallModuleTab('AdminPrestaSmsDashboardDefault');
        Helpers::uninstallModuleTab('AdminPrestaSmsSmsCampaignNew');
        Helpers::uninstallModuleTab('AdminPrestaSmsSmsCampaignDefault');
        Helpers::uninstallModuleTab('AdminPrestaSmsInboxList');
        Helpers::uninstallModuleTab('AdminPrestaSmsHistoryList');
        Helpers::uninstallModuleTab('AdminPrestaSmsStatisticsDefault');
        Helpers::uninstallModuleTab('AdminPrestaSmsBlackListDefault');
        Helpers::uninstallModuleTab('AdminPrestaSmsSmsPriceList');
        Helpers::uninstallModuleTab('AdminPrestaSmsTopUp');
        Helpers::uninstallModuleTab('AdminPrestaSmsPaymentList');
        Helpers::uninstallModuleTab('AdminPrestaSmsWalletDetail');
        Helpers::uninstallModuleTab('AdminPrestaSmsUserProfile');
        Helpers::uninstallModuleTab('AdminPrestaSmsModuleNotificationsAdmin');
        Helpers::uninstallModuleTab('AdminPrestaSmsModuleNotificationsCustomer');
        Helpers::uninstallModuleTab('AdminPrestaSmsSmsSettingsDefault');
        Helpers::uninstallModuleTab('AdminPrestaSmsModuleSettingsDefault');
        Helpers::uninstallModuleTab('AdminPrestaSmsAboutDefault');
        Helpers::uninstallModuleTab('AdminPrestaSmsSignIn');
        Helpers::uninstallModuleTab('AdminPrestaSmsSignUp');
        Helpers::uninstallModuleTab('AdminPrestaSmsSmsCampaignCampaign');
        Helpers::uninstallModuleTab('AdminPrestaSmsSmsCampaignActive');
        Helpers::uninstallModuleTab('AdminPrestaSmsBlackListImport');
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
