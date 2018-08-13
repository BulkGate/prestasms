<?php
namespace BulkGate\PrestaSms;

use BulkGate\Extensions;

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

        Helpers::installModuleTab('AdminPrestaSmsDashboardDefault', $translator->translate('dashboard','Dashboard'), $main, 'desktop_windows');

        $sms = Helpers::installModuleTab('PRESTASMS_SMS', $translator->translate('sms', 'SMS'), $main, 'sms');
        Helpers::installModuleTab('AdminPrestaSmsSmsCampaignNew', $translator->translate('start_campaign','Start Campaign'), $sms);
        Helpers::installModuleTab('AdminPrestaSmsSmsCampaignDefault', $translator->translate('campaigns','Campaigns'), $sms);
        Helpers::installModuleTab('AdminPrestaSmsInboxList', $translator->translate('inbox','Inbox'), $sms);
        Helpers::installModuleTab('AdminPrestaSmsHistoryList', $translator->translate('history','History'), $sms);
        Helpers::installModuleTab('AdminPrestaSmsStatisticsDefault', $translator->translate('statistics','Statistics'), $sms);
        Helpers::installModuleTab('AdminPrestaSmsBlackListDefault', $translator->translate('black_list','Black list'), $sms);
        Helpers::installModuleTab('AdminPrestaSmsSmsPriceList', $translator->translate('price_list', 'Price list'), $sms);

        $payment = Helpers::installModuleTab('PRESTASMS_PAYMENTS', $translator->translate('payments', 'Payments'), $main, 'payment');
        Helpers::installModuleTab('AdminPrestaSmsTopUp', $translator->translate('top_up', 'Top up'), $payment);
        Helpers::installModuleTab('AdminPrestaSmsPaymentList', $translator->translate('invoice_list', 'Invoice list'), $payment);
        Helpers::installModuleTab('AdminPrestaSmsWalletDetail', $translator->translate('billing_informations', 'Billing informations'), $payment);

        $settings = Helpers::installModuleTab('PRESTASMS_SETTINGS',  $translator->translate('settings', 'Settings'), $main, 'settings');
        Helpers::installModuleTab('AdminPrestaSmsUserProfile',  $translator->translate('user_profile', 'User profile'), $settings);
        Helpers::installModuleTab('AdminPrestaSmsModuleNotificationsAdmin',  $translator->translate('admin_sms', 'Admin SMS'), $settings);
        Helpers::installModuleTab('AdminPrestaSmsModuleNotificationsCustomer',  $translator->translate('customer_sms', 'Customer SMS'), $settings);
        Helpers::installModuleTab('AdminPrestaSmsSmsSettingsDefault',  $translator->translate('sender_id_settings', 'Sender ID Settings'), $settings);
        Helpers::installModuleTab('AdminPrestaSmsModuleSettingsDefault', $translator->translate('module_settings', 'Module settings'), $settings);

        Helpers::installModuleTab('AdminPrestaSmsAboutDefault', $translator->translate('about_module','About module'), $settings);
    }

    public static function uninstallMenu()
    {
        $tabs = array();

        $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE `module` = \''._BG_PRESTASMS_SLUG_.'\'', true, false);

        if (is_array($result)) {
            foreach ($result as $row) {
                $tabs[] = (int) $row['id_tab'];
            }
        }

        \Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('DELETE FROM `'._DB_PREFIX_.'tab_lang` WHERE `id_tab` IN ('.implode(',', $tabs).')');
        \Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('DELETE FROM `'._DB_PREFIX_.'tab` WHERE `module` = \''._BG_PRESTASMS_SLUG_.'\'');
    }
}
