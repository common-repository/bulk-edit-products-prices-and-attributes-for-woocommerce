<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
$cat_args = array(
    'hide_empty' => false,
    'order' => 'ASC'
);
$categories = get_terms('product_cat', $cat_args);
$attributes = wc_get_attribute_taxonomies();
$attribute_value = get_terms('pa_size', $cat_args);
?>

<div class="loader"></div>

<div class='wrap postbox table-box table-box-main' id="top_filter_tag" style='padding:5px 20px;'>
    <h2>
        <?php _e('Filter the Products<br> <span style="color:green;font-size:16px">This basic version only supports Simple Products.<button id="go_premium_link" style="color:red; background:white; border:white; text-decoration: underline;">Go Premium</button>for Variable products.</span>', 'eh_bulk_edit'); ?>
        <!--<span style="float: right;" id="calculate_percentage_back" ><span class='woocommerce-help-tip tooltip' id='calculate_percentage_back_tooltip' style="padding:0px 15px" data-tooltip='<?php _e('Check your Increase/Decrease Percentage to get back old price.', 'eh_bulk_edit'); ?>'></span><button id='calculate_percentage_back_button' style="margin-bottom: 2%;" class='button button-primary button-large'><span class="calculate-percentage-back-text"><?php _e('Generate Percentage', 'eh_bulk_edit'); ?></span></button></span>-->
        <span style="float: right;" id="remove_undo_update_button_top" ><span class='woocommerce-help-tip tooltip' id='add_undo_button_tooltip' style="padding:0px 15px" data-tooltip='<?php _e('Click to undo the last update you have done', 'eh_bulk_edit'); ?>'></span><button id='undo_display_update_button' style="margin-bottom: 2%;" class='button button-primary button-large' disabled="disabled"><span class="update-text"><?php _e('Undo Last Update', 'eh_bulk_edit'); ?></span></button></span>
    </h2>
    <hr>
    <table class='eh-content-table' id='data_table'>

        <tr>
            <td class='eh-content-table-left'>
                <?php _e('Product Title', 'eh_bulk_edit'); ?>
            </td>
            <td class='eh-content-table-middle'>
                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition from the drop-down and enter a product title', 'eh_bulk_edit'); ?>'></span>
            </td>
            <td class='eh-content-table-input-td'>
                <select id='product_title_select' style="width: 110px;">
                    <option value='all'><?php _e('All', 'eh_bulk_edit'); ?></option>
                    <option value='starts_with'>Starts With</option>
                    <option value='ends_with'>Ends With</option>
                    <option value='contains'>Contains</option>
                    <option value='title_regex'>RegEx Match</option>
                </select>
                <span id='product_title_text'></span>
            </td>
        </tr>


        <tr>
            <td class='eh-content-table-left'>
                <?php _e('Product Types', 'eh_bulk_edit'); ?>
            </td>
            <td class='eh-content-table-middle'>
                <span class='woocommerce-help-tip tooltip' data-tooltip=' <?php _e('Select the product type(s) for which the filter has to be applied', 'eh_bulk_edit'); ?> '></span>
            </td>
            <td>

                <span class='select-eh'><select data-placeholder='<?php _e('Select Product Types', 'eh_bulk_edit'); ?>' id='product_type' multiple class='category-chosen' >
                        <?php {

                            echo "<option value=\"'simple'\">Simple</option>";
//                            echo "<option value=\"'variable'\">Variable (Parent)</option>";
//                            echo "<option value=\"'variation'\">Variable (Variation)</option>";
                        }
                        ?>
                    </select></span>
            </td>
        </tr>
        <tr>
            <td class='eh-content-table-left'>
                <?php _e('Product Categories', 'eh_bulk_edit'); ?>
            </td>
            <td class='eh-content-table-middle'>
                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select the category(s) for which the filter has to be applied. Enable the checkbox to include subcategories', 'eh_bulk_edit'); ?>'></span>
            </td>


            <td>
                <span class='select-eh'><select data-placeholder='<?php _e('Select Product Categories', 'eh_bulk_edit'); ?>' id='category_select' multiple class='category-chosen' >
                        <?php
                        if (count($categories) > 0) {
                            foreach ($categories as $key => $value) {
                                echo "<option value=\"'" . $value->slug . "'\">" . $value->name . "</option>";
                            }
                        } else {
                            echo "<option value='-1' disabled>No categories found</option>";
                        }
                        ?>
                    </select></span>
            </td>
            <td class='eh-content-table-right'>
                <input type="checkbox" id ="subcat_check">Include Subcategories
            </td>
        </tr>
        <tr id='attribute_types'>
            <td class='eh-content-table-left'>
                <?php _e('Product Attributes', 'eh_bulk_edit'); ?>
            </td>
            <td class='eh-content-table-middle'>
                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select the Attribute(s) for which the filter has to be applied', 'eh_bulk_edit'); ?>'></span>
            </td>
            <td>
                <?php
                if (count($attributes) > 0) {
                    foreach ($attributes as $key => $value) {
                        echo "<span id='attrib_name' class='checkbox-eh'><input type='checkbox' name='attrib_name' value='" . $value->attribute_name . "' id='" . $value->attribute_name . "'>" . $value->attribute_label . "</span>";
                    }
                } else {
                    echo "<span id='attrib_name' class='checkbox-eh'>No attributes found.</span>";
                }
                ?>
            </td>
        </tr>
        <tr>
            <td class='eh-content-table-left'>
                <?php _e('Product Regular Price', 'eh_bulk_edit'); ?>
            </td>
            <td class='eh-content-table-middle'>
                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition and specify a price', 'eh_bulk_edit'); ?>'></span>
            </td>
            <td class='eh-content-table-input-td'>
                <select id='regular_price_range_select' style="width: 110px;">
                    <option value='all'><?php _e('All', 'eh_bulk_edit'); ?></option>
                    <option value='>'>>=</option>
                    <option value='<'><=</option>
                    <option value='='>==</option>
                    <option value='|'>|| <?php _e('Between', 'eh_bulk_edit'); ?></option>
                </select>
                <span id='regular_price_range_text'></span>
            </td>
        </tr>

    </table>
    <button id='clear_filter_button' value='clear_products' style='margin:5px 2px 2px 2px; color: white; width:15%; background-color: gray;' class='button button-large'><?php _e('Reset Filter', 'eh_bulk_edit'); ?></button>
    <button id='filter_products_button' value='filter_products' style='margin:5px 2px 2px 2px; float: right; ' class='button button-primary button-large'><?php _e('Preview Filtered Products', 'eh_bulk_edit'); ?></button>        
</div>
<div class="box14 table-box-main">
    <h3>
       <center><a href="https://www.xadapter.com/" target="_blank" style="text-decoration:  none;color:black;" >XAdapter</a></center></h3> 
       <hr>
       <img src="https://cdn.xadapter.com/wp-content/uploads/2016/07/11-Bulk-Edit-Products-Prices-Attributes-for-WooCommerce.jpg">
       <h3>Bulk Edit Products, Prices & Attributes for WooCommerce</h3>
    <br/> <center><a href="https://www.xadapter.com/product/bulk-edit-products-prices-attributes-for-woocommerce/" target="_blank" class="button button-primary">Upgrade to Premium Version</a></center>
    <span>
    <ul style="list-style: disc; margin-left: 20px;">
				<li style='color:red;'><strong>Your Business is precious! Go Premium!</strong></li>
				<li>Support both Simple and Variable Products.</li>
                <li>Option to Undo the Last Bulk Update.</li>
                <li>Timely compatibility updates and bug fixes.</li>
                <li>Premium support!</li>
    </ul>
    </span>
    
    <center> <a href="https://www.xadapter.com/category/documentation/bulk-edit-products-prices-attributes-for-woocommerce/" target="_blank" class="button button-primary">Documentation</a> <a href="http://bulkeditproductsdemo.adaptxy.com/" target="_blank" class="button button-primary">Live Demo</a></center>
</div>
<?php
include_once EH_BEP_TEMPLATE_PATH . "/template-frontend-tables.php";
