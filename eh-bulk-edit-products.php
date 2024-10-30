<?php
/*
Plugin Name: Bulk Edit Products, Prices & Attributes for Woocommerce (Basic)
Plugin URI: http://www.xadapter.com/product/bulk-edit-products-prices-attributes-for-woocommerce
Description: Bulk Edit Products, Prices & Attributes for Woocommerce allows you to edit products prices and attributes as Bulk.
Version: 3.0.2
WC requires at least: 2.6.0
WC tested up to: 3.4
Author: AdaptXY
Author URI: https://adaptxy.com/
*/
if (!defined('ABSPATH')) {
    exit;
}
if (!defined('EH_BEP_DIR')) {
    define('EH_BEP_DIR', plugin_dir_path(__FILE__));
}

if (!defined('EH_BEP_TEMPLATE_PATH')) {
    define('EH_BEP_TEMPLATE_PATH', EH_BEP_DIR . 'templates');
}
require_once(ABSPATH."wp-admin/includes/plugin.php");
// Change the Pack IF BASIC  mention switch('BASIC') ELSE mention switch('PREMIUM')
switch('BASIC')
{
    case 'PREMIUM':
        $conflict   = 'basic';
        $base       = 'premium';
        break;
    case 'BASIC':
        $conflict   = 'premium';
        $base       = 'basic';
        break;
}
// Enter your plugin unique option name below $option_name variable
$option_name='eh_bulk_edit_pack';
register_deactivation_hook(__FILE__, 'eh_bulk_edit_basic_deactivate_work');
// Enter your plugin unique option name below update_option function
function eh_bulk_edit_basic_deactivate_work()
{
    update_option('eh_bulk_edit_pack', '');
}
if(get_option($option_name)==$conflict)
{
    add_action('admin_notices','eh_wc_admin_notices', 99);
    deactivate_plugins(plugin_basename(__FILE__));
    function eh_wc_admin_notices()
    {
        is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
        {
            $old = array(
                "Plugin <strong>activated</strong>.",
                "Selected plugins <strong>activated</strong>."
            );
            $error_text='';
            // Change the Pack IF BASIC  mention switch('BASIC') ELSE mention switch('PREMIUM')
            switch('BASIC')
            {
                case 'PREMIUM':
                    $error_text="BASIC Version of this Plugin Installed. Please uninstall the BASIC Version before activating PREMIUM.";
                    break;
                case 'BASIC':
                    $error_text="PREMIUM Version of this Plugin Installed. Please uninstall the PREMIUM Version before activating BASIC.";
                    break;
            }
            $new = "<span style='color:red'>".$error_text."</span>";
            if (in_array($untranslated_text, $old, true)) {
                $translated_text = $new;
            }
            return $translated_text;
        }, 99, 3);
    }
    return;
}
else
{
    update_option($option_name, $base);	

    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        /**
         *  Bulk Product Edit class
         */
        class Eh_Bulk_Edit_Main
        {
            function __construct()
            {
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(
                    $this,
                    'eh_bep_action_link'
                )); //to add settings, doc, etc options to plugins base
                $this->eh_bep_include_lib();
            }
            public function eh_bep_include_lib()
            {
                include_once('includes/class-eh-variable-product.php');
            }
            public function eh_bep_action_link($links)
            {
                $plugin_links = array(
                    '<a href="' . admin_url('admin.php?page=eh-bulk-edit-product-attr') . '">' . __('Bulk Edit Products', 'eh_bulk_edit') . '</a>',
                    '<a href="http://www.xadapter.com/product/bulk-edit-products-prices-attributes-for-woocommerce" target="_blank">' . __( 'Premium Upgrade', 'eh-woocommerce-pricing-discount' ) . '</a>',
                    '<a href="https://wordpress.org/support/plugin/bulk-edit-products-prices-and-attributes-for-woocommerce" target="_blank">' . __( 'Support', 'eh-woocommerce-pricing-discount' ) . '</a>'
                );
                return array_merge($plugin_links, $links);
            }
        }
        new Eh_Bulk_Edit_Main();
    }
}