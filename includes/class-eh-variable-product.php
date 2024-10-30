<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 *  plugin init
 */
class Eh_Bulk_Edit_Init extends Eh_Bulk_Edit_Main {

    function __construct() {
        add_action('admin_menu', array(
            $this,
            'eh_bep_menu_add'
        ));
        add_action('admin_init', array(
            $this,
            'eh_bep_register_plugin_styles_scripts'
        ));
    }

    /**
     * Sub menu add in woocommerce menu
     */
    public function eh_bep_menu_add() {

        add_submenu_page('woocommerce', 'Bulk Edit Products', 'Bulk Edit Products', 'manage_woocommerce', 'eh-bulk-edit-product-attr', array(
            $this,
            'eh_bep_template_display'
        ));
    }

    /**
     * Register and enqueue style sheet.
     */
    public function eh_bep_register_plugin_styles_scripts() {
        include_once "ajax-apifunctions.php";
        include_once "class-table-data.php";
        $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
        if ('eh-bulk-edit-product-attr' != $page)
            return;
        wp_nonce_field('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
        global $woocommerce;
        $woocommerce_version = function_exists('WC') ? WC()->version : $woocommerce->version;
        wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css', array(), $woocommerce_version);
        wp_register_style('eh-plugin-style', plugins_url('/assets/css/bootstrap.css', dirname(__FILE__)));
        wp_enqueue_style('eh-plugin-style');
        wp_register_style('eh-alert-style', plugins_url('/assets/css/sweetalert2.css', dirname(__FILE__)));
        wp_enqueue_style('eh-alert-style');
        wp_register_script('eh-alert-jquery', plugins_url('/assets/js/sweetalert2.min.js', dirname(__FILE__)));
        wp_enqueue_script('eh-alert-jquery');
        wp_register_script('eh-multibox-jquery', plugins_url('/assets/js/chosen.jquery.js', dirname(__FILE__)));
        wp_enqueue_script('eh-multibox-jquery');
        wp_register_script('eh-tooltip-jquery', plugins_url('/assets/js/tooltip.js', dirname(__FILE__)));
        wp_enqueue_script('eh-tooltip-jquery');
        wp_register_script('eh-custom', plugins_url('/assets/js/eh-custom.js', dirname(__FILE__)));
        $js_var = array(
            'filter_attribute_value_title' => __('Attribute Values (Existing)', 'eh_bulk_edit'),
            'filter_attribute_value_tooltip' => __('Select the Attribute value(s) for which the filter has to be applied', 'eh_bulk_edit'),
            'filter_attribute_value_placeholder' => __('Select Attributes Values', 'eh_bulk_edit'),
            'filter_price_range_desired_placeholder' => __('Desired Price', 'eh_bulk_edit'),
            'filter_price_range_min_placeholder' => __('Minimum Price', 'eh_bulk_edit'),
            'filter_price_range_max_placeholder' => __('Maximum Price', 'eh_bulk_edit'),
            'process_edit_alert_title' => __('Do you want to Proceed?', 'eh_bulk_edit'),
            'process_edit_alert_confirm_button' => __('Yes, Proceed', 'eh_bulk_edit'),
            'process_edit_alert_cancel_button' => __('No, Wait', 'eh_bulk_edit'),
            'process_update_alert_title' => __('Proceed with Update?', 'eh_bulk_edit'),
            'process_update_alert_confirm_button' => __('Yes, Update', 'eh_bulk_edit'),
            'process_update_alert_cancel_button' => __('No, Wait', 'eh_bulk_edit'),
            'undo_alert_title' => __('Proceed with Undo Update?', 'eh_bulk_edit'),
            'undo_alert_subtitle' => __('This operation can not be reversed ', 'eh_bulk_edit'),
            'undo_display_alert_title' => __('Undo the Previous Update?', 'eh_bulk_edit'),
            'undo_display_alert_subtitle' => __('Click Proceed to see the Update Details.', 'eh_bulk_edit'),
            'undo_alert_confirm_button' => __('Yes, Proceed', 'eh_bulk_edit'),
            'undo_alert_cancel_button' => __('No, Wait', 'eh_bulk_edit'),
            'clear_product_alert_title' => __('Are you Sure ?', 'eh_bulk_edit'),
            'clear_product_alert_subtitle' => __('Do you want to Reset?', 'eh_bulk_edit'),
            'clear_product_alert_confirm_button' => __('Yes, Reset', 'eh_bulk_edit'),
            'clear_product_alert_cancel_button' => __('No, Wait', 'eh_bulk_edit'),
            'edit_title_new_placeholder' => __('New Title', 'eh_bulk_edit'),
            'edit_title_append_placeholder' => __('Append Title', 'eh_bulk_edit'),
            'edit_title_prepand_placeholder' => __('Prepend Title', 'eh_bulk_edit'),
            'edit_title_replaceable_placeholder' => __('Text to be Replaced', 'eh_bulk_edit'),
            'edit_title_replace_placeholder' => __('Replace Text', 'eh_bulk_edit'),
            'edit_sku_new_placeholder' => __('New SKU', 'eh_bulk_edit'),
            'edit_sku_append_placeholder' => __('Append SKU', 'eh_bulk_edit'),
            'edit_sku_prepand_placeholder' => __('Prepend SKU', 'eh_bulk_edit'),
            'edit_sku_replaceable_placeholder' => __('Text to be Replaced', 'eh_bulk_edit'),
            'edit_sku_replace_placeholder' => __('Replace Text', 'eh_bulk_edit'),
            'parent_text' => __('Product', 'eh_bulk_edit'),
            'child_text' => __('Variations', 'eh_bulk_edit'),
            'edit_price_up_per_placeholder' => __('Increase Percentage', 'eh_bulk_edit'),
            'edit_price_round_off' => __('Nearest Value', 'eh_bulk_edit'),
            'edit_price_down_per_placeholder' => __('Decrease Percentage', 'eh_bulk_edit'),
            'edit_price_up_pri_placeholder' => __('Increase Price', 'eh_bulk_edit'),
            'edit_price_flat_pri_placeholder' => __('Set Flat Price for All', 'eh_bulk_edit'),
            'edit_price_down_pri_placeholder' => __('Decrease Price', 'eh_bulk_edit'),
            'edit_add_placeholder' => __('Value to Add', 'eh_bulk_edit'),
            'edit_sub_placeholder' => __('Value to Subtract', 'eh_bulk_edit'),
            'edit_rep_placeholder' => __('Value to Replace', 'eh_bulk_edit'),
            'edit_shipping_unit_add_placeholder' => __('Shipping Unit Will be Added', 'eh_bulk_edit'),
            'edit_shipping_unit_sub_placeholder' => __('Shipping Unit Will be Subtracted', 'eh_bulk_edit'),
            'edit_shipping_unit_rep_placeholder' => __('Shipping Unit Will be Replaced', 'eh_bulk_edit'),
            'edit_success_alert_title' => __('Update Successful', 'eh_bulk_edit'),
            'undo_success_alert_title' => __('Undo Update Successful', 'eh_bulk_edit'),
            'edit_success_alert_button' => __('OK', 'eh_bulk_edit')
        );
        wp_localize_script('eh-custom', 'js_obj', $js_var);
        wp_enqueue_script('eh-custom');
    }

    public function eh_bep_template_display() {
        $current_tab = 'bulk_edit';
        echo '
                    <script>
                    jQuery(function($){
                    show_selected_tab($(".tab_bulk_edit"),"bulk_edit");
                    $(".tab_bulk_edit").on("click",function() {
                        return show_selected_tab($(this),"bulk_edit");
                    });
                    
                    $(".tab_licence").on("click",function() {
                        return show_selected_tab($(this),"licence");
                    });
                     $("#go_premium_link").on("click",function() {
                        return show_selected_tab($(this),"licence");
                    });
                   
                    function show_selected_tab($element,$tab) {
                        $(".nav-tab").removeClass("nav-tab-active");
                        $element.addClass("nav-tab-active");
                        $(".bulk_edit_tab_field").closest("tr,h3").hide();
                        $(".bulk_edit_tab_field").next("p").hide();
                                         
                        $(".settings_tab_field").closest("tr,h3").hide();
                        $(".settings_tab_field").next("p").hide();

                        $(".licence_tab_field").closest("tr,h3").hide();
                        $(".licence_tab_field").next("p").hide();
                        $("."+$tab+"_tab_field").closest("tr,h3").show();
                        $("."+$tab+"_tab_field").next("p").show();
                        
                         if($tab=="licence") {
                        	$(".table-box-main").show();
                        }
                        else {
                        	$(".table-box-main").hide();
                        }
                        
                        if($tab == "bulk_edit") {
                        $(".all-step").show();
                            if(document.getElementById("top_filter_tag").hidden == true) {
                                    if(document.getElementById("wrap_table").hidden == true) {
                                    $("#wrap_table").hide();
                                    
                                        if(document.getElementById("edit_product").hidden == true) {
                                        if(document.getElementById("update_logs").hidden == true) {
                                            $("#update_logs").hide();
                                             $("#undo_update_html").show();
                                        }
                                        else {
                                            $("#update_logs").show();
                                        }
                                        
                                        $("#edit_product").hide();
                                    }
                                    else {
                                    if(document.getElementById("update_logs").hidden != true) {
                                        $("#edit_product").show();
                                        }
                                    }
                                }
                                else {
                                    $("#wrap_table").show();
                                }

                                    $("#top_filter_tag").hide();
                                }
                            else {
                                $("#top_filter_tag").show();
                            }
                            
                        }
                        else {
                        $(".all-step").hide();
                            $("#undo_update_logs").hide();
                            $("#top_filter_tag").hide();
                            $("#undo_update_html").hide();
                            $("#wrap_table").hide();
                            $("#edit_product").hide();
                            $("#update_logs").hide();
                        }
                       
                        return false;
                    }   

                    });
                    </script>
                    <style>
                   
                    a.nav-tab{
                                cursor: default;
                    }
                    </style>
                    <hr class = "wp-header-end">';
        $tabs = array(
            'bulk_edit' => __("Bulk Edit ", 'eh_bulk_edit'),
            'licence' => __("<span style='color:red;font-size:16px'>Go Premium!</span>", 'eh_bulk_edit')
        );
        $html = '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $stab => $name) {
            $class = ($stab == $current_tab) ? 'nav-tab-active' : '';
            $style = ($stab == $current_tab) ? 'border-bottom: 1px solid transparent !important;' : '';

            $html .= '<a style="text-decoration:none !important;' . $style . '" class="nav-tab ' . $class . " tab_" . $stab . '" >' . $name . '</a>';
        }
        $html .= '</h2>';
        echo $html;
        ?>
        <div class="all-step">
            <div id ="step1" class="steps active">
                FILTER
            </div>
            <div id ="step2" class="steps">
                PREVIEW
            </div>
            <div id ="step3" class="steps ">
                EDIT PRODUCTS
            </div>
            <div id ="step4" class="steps">
                FINISH
            </div>
        </div>

        <?php
        include_once EH_BEP_TEMPLATE_PATH . "/template-frontend-filters.php";
    }

}

new Eh_Bulk_Edit_Init();
