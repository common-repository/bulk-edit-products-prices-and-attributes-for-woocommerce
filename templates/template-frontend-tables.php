<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class='wrap table-box table-box-main' id='wrap_table' style="position:relative;display: none;">
    <?php
    eh_bep_list_table();
    ?>
</div>
<?php
eh_bep_process_edit();

function eh_bep_list_table() {
    ?>
    <?php
    $obj = new Eh_DataTables();
    $obj->input();
    $obj->prepare_items();
    $obj->search_box('search', 'search_id');
    ?>
        <!--<button id='process_edit' value='edit_products' style="background-color: green;color: white;" class='button button-large'><span class="update-text"><?php _e('Process Edit', 'eh_bulk_edit'); ?></span><span class="edit"></span></button>-->
    Items per page:
    <input id="display_count_order" style="width:75px" type="number" min="1" max="9999" maxlength="4" value="<?php
    $count = get_option('eh_bulk_edit_table_row');
    if ($count) {
        echo $count;
    }
    ?>">
    <button id='save_dislay_count_order'class='button ' style='background-color:#f7f7f7; '><?php _e('Apply', 'eh-stripe-gateway'); ?></button>
    <form id="products-filter" method="get">
        <input type="hidden" name="action" value="all" />
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
    <?php $obj->display(); ?>
    </form>
    <button id='preview_back' value='edit_products' style="background-color: gray;color: white; width: 10%; " class='button button-large'><span class="update-text"><?php _e('Back', 'eh_bulk_edit'); ?></span></button>
    <button id='preview_cancel' value='edit_products' style="background-color: gray;color: white; width: 10%; " class='button button-large'><span class="update-text"><?php _e('Cancel', 'eh_bulk_edit'); ?></span></button>
    <button id='process_edit' value='edit_products' style="color: white;margin-bottom: 0%; float: right; width: 10%;" class='button button-primary button-large'><span class="update-text"><?php _e('Continue', 'eh_bulk_edit'); ?></span></button>

    <?php
}

function eh_bep_process_edit() {
    global $woocommerce;
    $attributes = wc_get_attribute_taxonomies();
    ?>
    <div class='wrap postbox table-box table-box-main' id="update_logs" style='padding:0px 20px;display: none'>
        <h1> <?php _e('Updating the products. Do not refresh...', 'eh_bulk_edit'); ?></h1>
        <div id='logs_val' ></div>
        <div id='logs_loader' ></div><br><br>

        <button id='finish_cancel' value='edit_products' style="background-color: gray; margin-bottom: 1%; color: white; width: 10%;" class='button button-large'><span class="update-text"><?php _e('Cancel', 'eh_bulk_edit'); ?></span></button>
        <button id='update_finished' value='edit_productss' style=' background-color: #006799; color: white; margin:5px 2px 2px 2px; width: 120px; height: 40px; float: right; ' class='button button-large'><span class="update-text"><?php _e('Continue', 'eh_bulk_edit'); ?></span></button>

    </div>
    
    <div class='wrap postbox table-box table-box-main' id="edit_product" style='padding:0px 20px;display: none'>
        <h2>
    <?php _e('Update the Products', 'eh_bulk_edit'); ?><span style="font-size: 14 !important;float: right;" ><span class='woocommerce-help-tip tooltip' id='add_undo_now_tooltip' style="padding:0px 5px" data-tooltip='<?php _e('Enable this Undo option to revert back your current update. But it will override your previous update.', 'eh_bulk_edit'); ?>'></span><input type="checkbox" id="add_undo_now" disabled="disabled" value="ok"><?php _e('Enable Undo Update <span style="vertical-align: super;color:red;font-size:12px">Premium</span>', 'eh_bulk_edit'); ?></span>
        </h2>
        <hr>
        <table class='eh-edit-table' id='update_general_table'>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Title', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition to edit the title, and enter the relevant text', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='title_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='set_new'><?php _e('Set New', 'eh_bulk_edit'); ?></option>
                        <option value='append'><?php _e('Append', 'eh_bulk_edit'); ?></option>
                        <option value='prepand'><?php _e('Prepend', 'eh_bulk_edit'); ?></option>
                        <option value='replace'><?php _e('Replace', 'eh_bulk_edit'); ?></option>
                        <option value='regex_replace'><?php _e('RegEx Replace', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='title_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('SKU', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition to edit the SKU, and enter the relevant text', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='sku_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='set_new'><?php _e('Set New', 'eh_bulk_edit'); ?></option>
                        <option value='append'><?php _e('Append', 'eh_bulk_edit'); ?></option>
                        <option value='prepand'><?php _e('Prepend', 'eh_bulk_edit'); ?></option>
                        <option value='replace'><?php _e('Replace', 'eh_bulk_edit'); ?></option>
                        <option value='regex_replace'><?php _e('RegEx Replace', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='sku_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Product Visiblity', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose which all shop pages the product will be listed on', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='catalog_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='visible'><?php _e('Shop and Search', 'eh_bulk_edit'); ?></option>
                        <option value='catalog'><?php _e('Shop', 'eh_bulk_edit'); ?></option>
                        <option value='search'><?php _e('Search', 'eh_bulk_edit'); ?></option>
                        <option value='hidden'><?php _e('Hidden', 'eh_bulk_edit'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Shipping Class', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a shipping class that will be added to all the filtered products', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='shipping_class_action' style="width: 210px;">
                        <?php
                        $ship = $woocommerce->shipping->get_shipping_classes();
                        if (count($ship) > 0) {
                            ?>
                            <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                            <?php
                            foreach ($ship as $key => $value) {
                                echo "<option value='" . $value->term_id . "'>" . $value->name . "</option>";
                            }
                        } else {
                            ?>
                            <option value=''><?php _e('< No Shipping Class >', 'eh_bulk_edit'); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <span id='shipping_class_check_text'></span>
                </td>
            </tr>
        </table>
        <h2>
    <?php _e('Price', 'eh_bulk_edit'); ?>
        </h2>
        <hr>
        <table class='eh-edit-table' id="update_price_table">
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Sale Price', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition to adjust the price and enter the value. You can also choose an option to round it to the nearest value', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='sale_price_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='up_percentage'><?php _e('Increase by Percentage ( + %)', 'eh_bulk_edit'); ?></option>
                        <option value='down_percentage'><?php _e('Decrease by Percentage ( - %)', 'eh_bulk_edit'); ?></option>
                        <option value='up_price'><?php _e('Increase by Price ( + $)', 'eh_bulk_edit'); ?></option>
                        <option value='down_price'><?php _e('Decrease by Price ( - $)', 'eh_bulk_edit'); ?></option>
                        <option value='flat_all'><?php _e('Flat Price for All', 'eh_bulk_edit'); ?></option>

                    </select>
                    <span id='sale_price_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Regular Price', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition to adjust the price and enter the value. You can also choose an option to round it to the nearest value', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='regular_price_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='up_percentage'><?php _e('Increase by Percentage ( + %)', 'eh_bulk_edit'); ?></option>
                        <option value='down_percentage'><?php _e('Decrease by Percentage ( - %)', 'eh_bulk_edit'); ?></option>
                        <option value='up_price'><?php _e('Increase by Price ( + $)', 'eh_bulk_edit'); ?></option>
                        <option value='down_price'><?php _e('Decrease by Price ( - $)', 'eh_bulk_edit'); ?></option>
                        <option value='flat_all'><?php _e('Flat Price for All', 'eh_bulk_edit'); ?></option>

                    </select>
                    <span id='regular_price_text'></span>
                </td>
            </tr>
        </table>
        <h2>
    <?php _e('Stock', 'eh_bulk_edit'); ?>
        </h2>
        <hr>
        <table class='eh-edit-table' id='update_stock_table'>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Manage Stock', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Enable or Disable manage stock for products or variations', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='manage_stock_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='yes'><?php _e('Enable', 'eh_bulk_edit'); ?></option>
                        <option value='no'><?php _e('Disable', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='manage_stock_check_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Stock Quantity', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update stock quantity and enter the value', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='stock_quantity_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='add'><?php _e('Increase', 'eh_bulk_edit'); ?></option>
                        <option value='sub'><?php _e('Decrease', 'eh_bulk_edit'); ?></option>
                        <option value='replace'><?php _e('Replace', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='stock_quantity_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Allow Backorders', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose how you want to handle backorders', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='allow_backorder_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='no'><?php _e('Do not Allow', 'eh_bulk_edit'); ?></option>
                        <option value='notify'><?php _e('Allow, but Notify the Customer', 'eh_bulk_edit'); ?></option>
                        <option value='yes'><?php _e('Allow', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='backorder_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Stock Status', 'eh_bulk_edit'); ?>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update  the stock status', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='stock_status_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='instock'><?php _e('In Stock', 'eh_bulk_edit'); ?></option>
                        <option value='outofstock'><?php _e('Out of Stock', 'eh_bulk_edit'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <h2>
    <?php _e('Weight & Dimensions', 'eh_bulk_edit'); ?>
        </h2>
        <hr>
        <table class='eh-edit-table' id='update_properties_table'>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Length', 'eh_bulk_edit'); ?>
                    <span style="float:right;"><?php echo '(' . strtolower(get_option('woocommerce_dimension_unit')) . ')'; ?></span>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update length and enter the value', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='length_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='add'><?php _e('Increase', 'eh_bulk_edit'); ?></option>
                        <option value='sub'><?php _e('Decrease', 'eh_bulk_edit'); ?></option>
                        <option value='replace'><?php _e('Replace', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='length_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Width', 'eh_bulk_edit'); ?>
                    <span style="float:right;"><?php echo '(' . strtolower(get_option('woocommerce_dimension_unit')) . ')'; ?></span>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update width and enter the value', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='width_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='add'><?php _e('Increase', 'eh_bulk_edit'); ?></option>
                        <option value='sub'><?php _e('Decrease', 'eh_bulk_edit'); ?></option>
                        <option value='replace'><?php _e('Replace', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='width_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Height', 'eh_bulk_edit'); ?>
                    <span style="float:right;"><?php echo '(' . strtolower(get_option('woocommerce_dimension_unit')) . ')'; ?></span>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update height and enter the value', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='height_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='add'><?php _e('Increase', 'eh_bulk_edit'); ?></option>
                        <option value='sub'><?php _e('Decrease', 'eh_bulk_edit'); ?></option>
                        <option value='replace'><?php _e('Replace', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='height_text'></span>
                </td>
            </tr>
            <tr>
                <td class='eh-edit-tab-table-left'>
    <?php _e('Weight', 'eh_bulk_edit'); ?>
                    <span style="float:right;"><?php echo '(' . strtolower(get_option('woocommerce_weight_unit')) . ')'; ?></span>
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update weight and enter the value', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='weight_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='add'><?php _e('Increase', 'eh_bulk_edit'); ?></option>
                        <option value='sub'><?php _e('Decrease', 'eh_bulk_edit'); ?></option>
                        <option value='replace'><?php _e('Replace', 'eh_bulk_edit'); ?></option>
                    </select>
                    <span id='weight_text'></span>
                </td>
            </tr>



        </table>


        <h2>
    <?php _e('Attributes', 'eh_bulk_edit'); ?>
        </h2>
        <hr>
        <table class='eh-edit-table' id='update_attribute_table'>

            <tr id="attr_add_edit">
                <td class='eh-edit-tab-table-left'>
    <?php _e('Attribute Actions', 'eh_bulk_edit'); ?>
    <!--                    <span style="float:right;"><?php echo '(' . strtolower(get_option('woocommerce_weight_unit')) . ')'; ?></span>-->
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select an option to make changes to your attribute values', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <select id='attribute_action' style="width: 210px;">
                        <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                        <option value='add'><?php _e('Add New Values', 'eh_bulk_edit'); ?></option>
                        <option value='remove'><?php _e('Remove Existing Values', 'eh_bulk_edit'); ?></option>
                        <option value='replace'><?php _e('Overwrite Existing Values', 'eh_bulk_edit'); ?></option>
                    </select>
                </td>
            </tr>
            <tr id="attr_names" >
                <td class='eh-edit-tab-table-left'>
    <?php _e('Attributes to Update', 'eh_bulk_edit'); ?>
    <!--                    <span style="float:right;"><?php echo '(' . strtolower(get_option('woocommerce_weight_unit')) . ')'; ?></span>-->
                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select the attribute(s) for which you want to change the values', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class= 'eh-edit-tab-table-input-td'>
                    <?php
                    if (count($attributes) > 0) {
                        foreach ($attributes as $key => $value) {
                            echo "<span id='attribu_name' class='checkbox-eh'><input type='checkbox' name='attribu_name' value='" . $value->attribute_name . "' id='" . $value->attribute_name . "'>" . $value->attribute_label . "</span>";
                        }
                    } else {
                        echo "<span id='attribu_name' class='checkbox-eh'>No attributes found.</span>";
                    }
                    ?>
                </td>
    <!--                    <span id='weight_text'></span>-->

            </tr>
            <tr id="new_attr">

            </tr>

            <tr id ="variation_select">

            </tr>





        </table>

        <?php
        if (in_array('pricing-discounts-by-user-role-woocommerce/pricing-discounts-by-user-role-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            ?>
            <h2>
        <?php _e('Role Based Pricing', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id='update_general_table'>
                <tr>
                    <td class='eh-edit-tab-table-left'>
        <?php _e('Hide price', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select option to hide price for unregistered users.', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <select id='visibility_price'>
                            <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                            <option value='no'><?php _e('Show Price', 'eh_bulk_edit'); ?></option>
                            <option value='yes'><?php _e('Hide Price', 'eh_bulk_edit'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-left'>
        <?php _e('Hide product price based on user role', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('For selected user role, hide the product price', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <span class='select-eh'>
                            <select data-placeholder='<?php _e('User Role', 'eh_bulk_edit'); ?>' id='hide_price_role_select' multiple class='hide-price-role-select-chosen' >
                                <?php
                                global $wp_roles;
                                $roles = $wp_roles->role_names;
                                foreach ($roles as $key => $value) {
                                    echo "<option value='" . $key . "'>" . $value . "</option>";
                                }
                                ?>
                            </select>
                        </span>
                    </td>
                </tr>
                <?php
                $enabled_roles = get_option('eh_pricing_discount_product_price_user_role');
                if (is_array($enabled_roles)) {
                    if (!in_array('none', $enabled_roles)) {
                        ?>
                        <tr>
                            <td class='eh-edit-tab-table-left'>
                <?php _e('Enforce product price adjustment', 'eh_bulk_edit'); ?>
                            </td>
                            <td class='eh-edit-tab-table-middle'>
                                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select option to enforce indvidual price adjustment', 'eh_bulk_edit'); ?>'></span>
                            </td>
                            <td class='eh-edit-tab-table-input-td'>
                                <select id='price_adjustment_action'>
                                    <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                                    <option value='yes'><?php _e('Enable', 'eh_bulk_edit'); ?></option>
                                    <option value='no'><?php _e('Disable', 'eh_bulk_edit'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
            <?php
        }
        if (in_array('per-product-addon-for-woocommerce-shipping-pro/woocommerce-per-product-shipping-addon-for-shipping-pro.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            ?>

            <h2>
        <?php _e('Shipping Pro', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id='update_properties_table'>
                <tr>
                    <td class='eh-edit-tab-table-left'>
        <?php _e('Shipping Unit', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Update Shipping Unit', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <select id='shipping_unit_action'>
                            <option value=''><?php _e('< No Change >', 'eh_bulk_edit'); ?></option>
                            <option value='add'><?php _e('Add', 'eh_bulk_edit'); ?></option>
                            <option value='sub'><?php _e('Subtract', 'eh_bulk_edit'); ?></option>
                            <option value='replace'><?php _e('Replace', 'eh_bulk_edit'); ?></option>
                        </select>
                        <span id='shipping_unit_text'></span>
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>
        <button id='edit_back' value='cancel_update_button' style="margin-bottom: 1%; background-color: gray; color: white; width: 10%; " class='button button-large'><span class="update-text"><?php _e('Back', 'eh_bulk_edit'); ?></span></button>
        <button id='edit_cancel' value='cancel_update_button' style="margin-bottom: 1%; background-color: gray; color: white; width: 10%; " class='button button-large'><span class="update-text"><?php _e('Cancel', 'eh_bulk_edit'); ?></span></button>
        <button id='reset_update_button' value='reset_update_button' style="margin-bottom: 1%; background-color: gray; color: white; width: 10%;" class='button button-large'><span class="update-text"><?php _e('Reset Values', 'eh_bulk_edit'); ?></span></button>
        <button id='update_button' value='update_button' style="margin-bottom: 1%; float: right; color: white; width: 12%;" class='button button-primary button-large'><span class="update-text"><?php _e('Update Products', 'eh_bulk_edit'); ?></span></button>
    </div>    
    <?php
}
?>
<?php
add_action('admin_footer', 'eh_bep_variation_pop');

function eh_bep_variation_pop() {
    $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
    if ('eh-bulk-edit-product-attr' != $page)
        return;
    ?>
    <div class="popup" data-popup="popup-1" id='main_var_disp'>
        <div class="popup-inner" >
            <center><h3><?php _e('Product variations', 'eh_bulk_edit'); ?></h3></center>
            <div id='vari_disp' style="overflow-y: auto; height: 80%; position:relative;">
            </div>
            <span class="popup-close " data-popup-close="popup-1" id='pop_close' style="cursor:pointer;">x</span>
        </div>
    </div>
    <?php
}
