<?php
if (!defined('ABSPATH')) {
    exit;
}
global $hook_suffix;
add_action('wp_ajax_eh_bep_get_attributes_action', 'eh_bep_get_attributes_action_callback');
add_action('wp_ajax_eh_bep_all_products', 'eh_bep_list_table_all_callback');
add_action('wp_ajax_eh_bep_count_products', 'eh_bep_count_products_callback');
add_action('wp_ajax_eh_bep_clear_products', 'eh_clear_all_callback');
add_action('wp_ajax_eh_bep_update_products', 'eh_bep_update_product_callback');
add_action('wp_ajax_eh_bep_filter_products', 'eh_bep_search_filter_callback');
add_action('wp_ajax_eh_bulk_edit_display_count', 'eh_bulk_edit_display_count_callback');

function eh_bulk_edit_display_count_callback() {
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $value = sanitize_text_field($_POST['row_count']);
    update_option('eh_bulk_edit_table_row', $value);
    die('success');
}

function eh_bep_count_products_callback() {
    $filtered_products = xa_bep_get_selected_products();
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    die(json_encode($filtered_products));
}

function eh_bep_get_attributes_action_callback() {
    $attribute_name = $_POST['attrib'];
    $cat_args = array(
        'hide_empty' => false,
        'order' => 'ASC'
    );
    $attributes = wc_get_attribute_taxonomies();
    foreach ($attributes as $key => $value) {
        if ($attribute_name == $value->attribute_name) {
            $attribute_name = $value->attribute_name;
            $attribute_label = $value->attribute_label;
        }
    }
    $attribute_value = get_terms('pa_' . $attribute_name, $cat_args);
    $return = "<optgroup label='" . $attribute_label . "' id='grp_" . $attribute_name . "'>";
    foreach ($attribute_value as $key => $value) {
        $return .= "<option value=\"'pa_" . $attribute_name . ":" . $value->slug . "'\">" . $value->name . "</option>";
    }
    $return .= "</optgroup>";
    echo $return;
    exit;
}

function eh_bep_in_array_fields_check($key, $array) {
    if (empty($array)) {
        return;
    }
    if (in_array($key, $array)) {
        return true;
    } else {
        return false;
    }
}

//custom rounding

function eh_bep_round_ceiling($number, $significance = 1) {
    return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number / $significance) * $significance) : false;
}

function eh_bep_update_product_callback() {
    set_time_limit(300);
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $selected_products = $_POST['pid'];
    $product_data = array();
    $title_select = $_POST['title_select'];
    $sku_select = $_POST['sku_select'];
    $catalog_select = $_POST['catalog_select'];
    $shipping_select = $_POST['shipping_select'];
    $sale_select = $_POST['sale_select'];
    $sale_round_select = $_POST['sale_round_select'];
    $regular_select = $_POST['regular_select'];
    $regular_round_select = $_POST['regular_round_select'];
    $stock_manage_select = $_POST['stock_manage_select'];
    $quantity_select = $_POST['quantity_select'];
    $backorder_select = $_POST['backorder_select'];
    $stock_status_select = $_POST['stock_status_select'];
    $attribute_action = $_POST['attribute_action'];

    $length_select = $_POST['length_select'];
    $width_select = $_POST['width_select'];
    $height_select = $_POST['height_select'];
    $weight_select = $_POST['weight_select'];
    $title_text = $_POST['title_text'];
    $replace_title_text = sanitize_text_field($_POST['replace_title_text']);
    $regex_replace_title_text = sanitize_text_field($_POST['regex_replace_title_text']);
    $sku_text = $_POST['sku_text'];
    $sku_replace_text = sanitize_text_field($_POST['sku_replace_text']);
    $regex_sku_replace_text = sanitize_text_field($_POST['regex_sku_replace_text']);
    $sale_text = $_POST['sale_text'];
    $sale_round_text = isset($_POST['sale_round_text']) ? $_POST['sale_round_text'] : '';
    $regular_text = $_POST['regular_text'];
    $regular_round_text = $_POST['regular_round_text'];
    $quantity_text = $_POST['quantity_text'];
    $length_text = $_POST['length_text'];
    $width_text = $_POST['width_text'];
    $height_text = $_POST['height_text'];
    $weight_text = $_POST['weight_text'];
    $hide_price = $_POST['hide_price'];
    $hide_price_role = ($_POST['hide_price_role'] != '') ? $_POST['hide_price_role'] : '';
    $price_adjustment = $_POST['price_adjustment'];
    $shipping_unit = sanitize_text_field($_POST['shipping_unit']);
    $shipping_unit_select = $_POST['shipping_unit_select'];
    $sale_warning = array();
    foreach ($selected_products as $pid => $temp) {
        $pid = $temp;
        apply_filters('http_request_timeout', 30);
        switch ($hide_price) {
            case 'yes':
                eh_bep_update_meta_fn($pid, 'product_adjustment_hide_price_unregistered', 'yes');
                break;
            case 'no':
                eh_bep_update_meta_fn($pid, 'product_adjustment_hide_price_unregistered', 'no');
                break;
        }
        switch ($price_adjustment) {
            case 'yes':
                eh_bep_update_meta_fn($pid, 'product_based_price_adjustment', 'yes');
                break;
            case 'no':
                eh_bep_update_meta_fn($pid, 'product_based_price_adjustment', 'no');
                break;
        }
        if ($hide_price_role != '') {
            eh_bep_update_meta_fn($pid, 'eh_pricing_adjustment_product_price_user_role', $hide_price_role);
        }
        switch ($shipping_unit_select) {
            case "add":
                $unit = get_post_meta($pid, '_wf_shipping_unit', true);
                $unit_val = number_format($unit + $shipping_unit, 6, '.', '');
                eh_bep_update_meta_fn($pid, '_wf_shipping_unit', $unit_val);
                break;
            case "sub":
                $unit = get_post_meta($pid, '_wf_shipping_unit', true);
                $unit_val = number_format($unit - $shipping_unit, 6, '.', '');
                eh_bep_update_meta_fn($pid, '_wf_shipping_unit', $unit_val);
                break;
            case "replace":
                $unit = get_post_meta($pid, '_wf_shipping_unit', true);
                eh_bep_update_meta_fn($pid, '_wf_shipping_unit', $shipping_unit);
                break;
            default:
                break;
        }
        $temp = wc_get_product($pid);
        $parent = $temp;
        $parent_id = $pid;
        
        $temp_type = (WC()->version < '2.7.0') ? $temp->product_type : $temp->get_type();
        $temp_title = (WC()->version < '2.7.0') ? $temp->post->post_title : $temp->get_title();
        if ($temp_type == 'simple') {
            $product_data = array();
            $product_data['type'] = 'simple';
            $product_data['title'] = $temp_title;
            $product_data['sku'] = get_post_meta($pid, '_sku', true);
            $product_data['catalog'] = (WC()->version < '3.0.0') ? get_post_meta($pid, '_visibility', true) : $temp->get_catalog_visibility();
            $ship_args = array('fields' => 'ids');
            $product_data['shipping'] = current(wp_get_object_terms($pid, 'product_shipping_class', $ship_args));
            $product_data['sale'] = (float) get_post_meta($pid, '_sale_price', true);
            $product_data['regular'] = (float) get_post_meta($pid, '_regular_price', true);
            $product_data['stock_manage'] = get_post_meta($pid, '_manage_stock', true);
            $product_data['stock_quantity'] = (float) get_post_meta($pid, '_stock', true);
            $product_data['backorder'] = get_post_meta($pid, '_backorders', true);
            $product_data['stock_status'] = get_post_meta($pid, '_stock_status', true);
            $product_data['length'] = (float) get_post_meta($pid, '_length', true);
            $product_data['width'] = (float) get_post_meta($pid, '_width', true);
            $product_data['height'] = (float) get_post_meta($pid, '_height', true);
            $product_data['weight'] = (float) get_post_meta($pid, '_weight', true);
            switch ($title_select) {
                case 'set_new':
                    $my_post = array(
                        'ID' => $pid,
                        'post_title' => $title_text
                    );
                    wp_update_post($my_post);
                    break;
                case 'append':
                    $my_post = array(
                        'ID' => $pid,
                        'post_title' => $product_data['title'] . $title_text
                    );
                    wp_update_post($my_post);
                    break;
                case 'prepand':
                    $my_post = array(
                        'ID' => $pid,
                        'post_title' => $title_text . $product_data['title']
                    );
                    wp_update_post($my_post);
                    break;
                case 'replace':
                    $my_post = array(
                        'ID' => $pid,
                        'post_title' => str_replace($replace_title_text, $title_text, $product_data['title'])
                    );
                    wp_update_post($my_post);
                    break;
                case 'regex_replace':
                    if(@preg_replace('/'.$regex_replace_title_text.'/',$title_text, $product_data['title']) != false){
                        $my_post = array(
                        'ID' => $pid,
                        'post_title' => preg_replace('/'.$regex_replace_title_text.'/', $title_text, $product_data['title'])
                        );
                        wp_update_post($my_post);
                    }
                    break;
            }
            switch ($sku_select) {
                case 'set_new':
                    eh_bep_update_meta_fn($pid, '_sku', $sku_text);
                    break;
                case 'append':
                    $sku_val = $product_data['sku'] . $sku_text;
                    eh_bep_update_meta_fn($pid, '_sku', $sku_val);
                    break;
                case 'prepand':
                    $sku_val = $sku_text . $product_data['sku'];
                    eh_bep_update_meta_fn($pid, '_sku', $sku_val);
                    break;
                case 'replace':
                    $sku_val = str_replace($sku_replace_text, $sku_text, $product_data['sku']);
                    eh_bep_update_meta_fn($pid, '_sku', $sku_val);
                    break;
                case 'regex_replace':
                    if(@preg_replace('/'.$regex_sku_replace_text.'/',$sku_text, $product_data['sku']) != false){
                        $sku_val = preg_replace('/'.$regex_sku_replace_text.'/', $sku_text, $product_data['sku']);
                        eh_bep_update_meta_fn($pid, '_sku', $sku_val);
                    }
                    break;
            }
            if ($temp_type != 'variation') {
                if (WC()->version < '3.0.0') {
                    eh_bep_update_meta_fn($pid, '_visibility', $catalog_select);
                } else {
                    $options = array_keys(wc_get_product_visibility_options());
                    $catalog_select = wc_clean($catalog_select);
                    if (in_array($catalog_select, $options, true)) {
                        $parent->set_catalog_visibility($catalog_select);
                        $parent->save();
                    }
                }
            }

            if ($shipping_select != '') {
                wp_set_object_terms((int) $pid, (int) $shipping_select, 'product_shipping_class');
            }

            switch ($sale_select) {
                case 'up_percentage':
                    if ($product_data['sale'] !== '') {
                        $per_val = $product_data['sale'] * ($sale_text / 100);
                        $cal_val = $product_data['sale'] + $per_val;
                        if ($sale_round_select != "" && $sale_round_text != "") {
                            $got_sale = $cal_val;
                            switch ($sale_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_sale, $sale_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_sale, -$sale_round_text);
                                    break;
                            }
                        }
                        $sale_val = wc_format_decimal($cal_val, "");
                        //leave sale price blank if sale price increased by -100%
                        if ($sale_val == 0) {
                            $sale_val = '';
                        }
                        $reg_val = get_post_meta($pid, '_regular_price', true);
                        if ($sale_val < $reg_val) {
                            eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Sales');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'down_percentage':
                    if ($product_data['sale'] !== '') {
                        $per_val = $product_data['sale'] * ($sale_text / 100);
                        $cal_val = $product_data['sale'] - $per_val;
                        if ($sale_round_select != "" && $sale_round_text != "") {
                            $got_sale = $cal_val;
                            switch ($sale_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_sale, $sale_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_sale, -$sale_round_text);
                                    break;
                            }
                        }
                        $sale_val = wc_format_decimal($cal_val, "");
                        //leave sale price blank if sale price decreased by 100%
                        if ($sale_val == 0) {
                            $sale_val = '';
                        }
                        $reg_val = get_post_meta($pid, '_regular_price', true);
                        if ($sale_val < $reg_val) {
                            eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Sales');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'up_price':
                    if ($product_data['sale'] !== '') {
                        $cal_val = $product_data['sale'] + $sale_text;
                        if ($sale_round_select != "" && $sale_round_text != "") {
                            $got_sale = $cal_val;
                            switch ($sale_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_sale, $sale_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_sale, -$sale_round_text);
                                    break;
                            }
                        }
                        $sale_val = wc_format_decimal($cal_val, "");
                        $reg_val = get_post_meta($pid, '_regular_price', true);
                        if ($sale_val < $reg_val) {
                            eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Sales');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'down_price':
                    if ($product_data['sale'] !== '') {
                        $cal_val = $product_data['sale'] - $sale_text;
                        if ($sale_round_select != "" && $sale_round_text != "") {
                            $got_sale = $cal_val;
                            switch ($sale_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_sale, $sale_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_sale, -$sale_round_text);
                                    break;
                            }
                        }
                        $sale_val = wc_format_decimal($cal_val, "");
                        $reg_val = get_post_meta($pid, '_regular_price', true);
                        if ($sale_val < $reg_val) {
                            eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Sales');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'flat_all':
                    $sale_val = wc_format_decimal($sale_text, "");
                    $reg_val = get_post_meta($pid, '_regular_price', true);
                    if ($sale_val < $reg_val) {
                        eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                    } else {
                        array_push($sale_warning, $pid, $parent_id);
                        array_push($sale_warning, 'Sales');
                        array_push($sale_warning, $temp_type);
                    }
                    break;
            }
            switch ($regular_select) {
                case 'up_percentage':
                    if ($product_data['regular'] !== '') {
                        $per_val = $product_data['regular'] * ($regular_text / 100);
                        $cal_val = $product_data['regular'] + $per_val;
                        if ($regular_round_select != "" && $regular_round_text != "") {
                            $got_regular = $cal_val;
                            switch ($regular_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_regular, $regular_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_regular, -$regular_round_text);
                                    break;
                            }
                        }
                        $regular_val = wc_format_decimal($cal_val, "");
                        
                        $sal_val = get_post_meta($pid, '_sale_price', true);
                        if ($sal_val < $regular_val) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Regular');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'down_percentage':
                    if ($product_data['regular'] !== '') {
                        $per_val = $product_data['regular'] * ($regular_text / 100);
                        $cal_val = $product_data['regular'] - $per_val;
                        if ($regular_round_select != "" && $regular_round_text != "") {
                            $got_regular = $cal_val;
                            switch ($regular_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_regular, $regular_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_regular, -$regular_round_text);
                                    break;
                            }
                        }
                        $regular_val = wc_format_decimal($cal_val, "");

                        $sal_val = get_post_meta($pid, '_sale_price', true);
                        if ($sal_val < $regular_val) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Regular');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'up_price':
                    if ($product_data['regular'] !== '') {
                        $cal_val = $product_data['regular'] + $regular_text;
                        if ($regular_round_select != "" && $regular_round_text != "") {
                            $got_regular = $cal_val;
                            switch ($regular_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_regular, $regular_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_regular, -$regular_round_text);
                                    break;
                            }
                        }
                        $regular_val = wc_format_decimal($cal_val, "");
                        
                        $sal_val = get_post_meta($pid, '_sale_price', true);
                        if ($sal_val < $regular_val) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Regular');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'down_price':
                    if ($product_data['regular'] !== '') {
                        $cal_val = $product_data['regular'] - $regular_text;
                        if ($regular_round_select != "" && $regular_round_text != "") {
                            $got_regular = $cal_val;
                            switch ($regular_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_regular, $regular_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_regular, -$regular_round_text);
                                    break;
                            }
                        }
                        $regular_val = wc_format_decimal($cal_val, "");
                        $sal_val = get_post_meta($pid, '_sale_price', true);
                        if ($sal_val < $regular_val) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Regular');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'flat_all':
                    $regular_val = wc_format_decimal($regular_text, "");
                    $sal_val = get_post_meta($pid, '_sale_price', true);
                    if ($sal_val < $regular_val) {
                        eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                    } else {
                        array_push($sale_warning, $pid, $parent_id);
                        array_push($sale_warning, 'Regular');
                        array_push($sale_warning, $temp_type);
                    }
                    break;
            }
            if (get_post_meta($pid, '_sale_price', true) !== '' && get_post_meta($pid, '_regular_price', true) !== '') {
                eh_bep_update_meta_fn($pid, '_price', get_post_meta($pid, '_sale_price', true));
            } elseif (get_post_meta($pid, '_sale_price', true) === '' && get_post_meta($pid, '_regular_price', true) !== '') {
                eh_bep_update_meta_fn($pid, '_price', get_post_meta($pid, '_regular_price', true));
            } elseif (get_post_meta($pid, '_sale_price', true) !== '' && get_post_meta($pid, '_regular_price', true) === '') {
                eh_bep_update_meta_fn($pid, '_price', get_post_meta($pid, '_sale_price', true));
            } elseif (get_post_meta($pid, '_sale_price', true) === '' && get_post_meta($pid, '_regular_price', true) === '') {
                eh_bep_update_meta_fn($pid, '_price', '');
            }
            switch ($stock_manage_select) {
                case 'yes':
                    eh_bep_update_meta_fn($pid, '_manage_stock', 'yes');
                    break;
                case 'no':
                    eh_bep_update_meta_fn($pid, '_manage_stock', 'no');
                    break;
            }
            switch ($quantity_select) {
                case 'add':
                    $quantity_val = number_format($product_data['stock_quantity'] + $quantity_text, 6, '.', '');
                    eh_bep_update_meta_fn($pid, '_stock', $quantity_val);
                    break;
                case 'sub':
                    $quantity_val = number_format($product_data['stock_quantity'] - $quantity_text, 6, '.', '');
                    eh_bep_update_meta_fn($pid, '_stock', $quantity_val);
                    break;
                case 'replace':
                    $quantity_val = number_format($quantity_text, 6, '.', '');
                    eh_bep_update_meta_fn($pid, '_stock', $quantity_val);
                    break;
            }
            switch ($backorder_select) {
                case 'no':
                    eh_bep_update_meta_fn($pid, '_backorders', 'no');
                    break;
                case 'notify':
                    eh_bep_update_meta_fn($pid, '_backorders', 'notify');
                    break;
                case 'yes':
                    eh_bep_update_meta_fn($pid, '_backorders', 'yes');
                    break;
            }
            switch ($stock_status_select) {
                case 'instock':
                    eh_bep_update_meta_fn($pid, '_stock_status', 'instock');
                    break;
                case 'outofstock':
                    eh_bep_update_meta_fn($pid, '_stock_status', 'outofstock');
                    break;
            }
            switch ($length_select) {
                case 'add':
                    $length_val = $product_data['length'] + $length_text;
                    eh_bep_update_meta_fn($pid, '_length', $length_val);
                    break;
                case 'sub':
                    $length_val = $product_data['length'] - $length_text;
                    eh_bep_update_meta_fn($pid, '_length', $length_val);
                    break;
                case 'replace':
                    $length_val = $length_text;
                    eh_bep_update_meta_fn($pid, '_length', $length_val);
                    break;
            }
            switch ($width_select) {
                case 'add':
                    $width_val = $product_data['width'] + $width_text;
                    eh_bep_update_meta_fn($pid, '_width', $width_val);
                    break;
                case 'sub':
                    $width_val = $product_data['width'] - $width_text;
                    eh_bep_update_meta_fn($pid, '_width', $width_val);
                    break;
                case 'replace':
                    $width_val = $width_text;
                    eh_bep_update_meta_fn($pid, '_width', $width_val);
                    break;
            }
            switch ($height_select) {
                case 'add':
                    $height_val = $product_data['height'] + $height_text;
                    eh_bep_update_meta_fn($pid, '_height', $height_val);
                    break;
                case 'sub':
                    $height_val = $product_data['height'] - $height_text;
                    eh_bep_update_meta_fn($pid, '_height', $height_val);
                    break;
                case 'replace':
                    $height_val = $height_text;
                    eh_bep_update_meta_fn($pid, '_height', $height_val);
                    break;
            }
            switch ($weight_select) {
                case 'add':
                    $weight_val = $product_data['weight'] + $weight_text;
                    eh_bep_update_meta_fn($pid, '_weight', $weight_val);
                    break;
                case 'sub':
                    $weight_val = $product_data['weight'] - $weight_text;
                    eh_bep_update_meta_fn($pid, '_weight', $weight_val);
                    break;
                case 'replace':
                    $weight_val = $weight_text;
                    eh_bep_update_meta_fn($pid, '_weight', $weight_val);
                    break;
            }
            wc_delete_product_transients($pid);
        }

        // Edit Attributes
        if ($temp_type != 'variation' && !empty($_POST['attribute'])) {
            $i = 0;
            $prev_value = '';
            $_product_attributes = get_post_meta($pid, '_product_attributes', TRUE);
           

            if (!empty($_POST['attribute_value'])) {
                foreach ($_POST['attribute_value'] as $key => $value) {

                    $value = stripslashes($value);
                    $value = preg_replace('/\'/', '', $value);
                    $att_slugs = explode(':', $value);
                    
                    if ($prev_value != $att_slugs[0]) {
                        $i = 0;
                    }
                    $prev_value = $att_slugs[0];
                    if ($_POST['attribute_action'] == 'replace' && $i == 0) {
                        wp_set_object_terms($pid, $att_slugs[1], $att_slugs[0]);
                        $i++;
                    } else {
                        wp_set_object_terms($pid, $att_slugs[1], $att_slugs[0], true);
                    }
                    $thedata = Array($att_slugs[0] => Array(
                            'name' => $att_slugs[0],
                            'value' => $att_slugs[1],
                            'is_visible' => '1',
                            'is_taxonomy' => '1'
                    ));
                    if ($_POST['attribute_action'] == 'add' || $_POST['attribute_action'] == 'replace') {
                        $_product_attr = get_post_meta($pid, '_product_attributes', TRUE);
                        if (!empty($_product_attr)) {
                            update_post_meta($pid, '_product_attributes', array_merge($_product_attr, $thedata));
                        } else {
                            update_post_meta($pid, '_product_attributes', $thedata);
                        }
                    }
                    if ($_POST['attribute_action'] == 'remove') {
                        wp_remove_object_terms($pid, $att_slugs[1], $att_slugs[0]);
                    }
                }
            }
            if (!empty($_POST['new_attribute_values']) || $_POST['new_attribute_values'] != '') {
                $ar1 = explode(',', $_POST['attribute']);
                foreach ($ar1 as $key => $value) {
                    foreach ($_POST['new_attribute_values'] as $key_index => $value_slug) {

                        $att_s = 'pa_' . $value;

                        if ($prev_value != $att_s) {
                            $i = 0;
                        }


                        $prev_value = $att_s;
                        if ($_POST['attribute_action'] == 'replace' && $i == 0) {
                            wp_set_object_terms($pid, $value_slug, $att_s);
                            $i++;
                        } else {
                            wp_set_object_terms($pid, $value_slug, $att_s, true);
                        }
                        $thedata = Array($att_s => Array(
                                'name' => $att_s,
                                'value' => $value_slug,
                                'is_visible' => '1',
                                'is_taxonomy' => '1'
                        ));
                        if ($_POST['attribute_action'] == 'add' || $_POST['attribute_action'] == 'replace') {
                            $_product_attr = get_post_meta($pid, '_product_attributes', TRUE);
                            if (!empty($_product_attr)) {
                                update_post_meta($pid, '_product_attributes', array_merge($_product_attr, $thedata));
                            } else {
                                update_post_meta($pid, '_product_attributes', $thedata);
                            }
                        }
                    }
                }
            }
        }

    }
    
    if ($_POST['index_val'] == $_POST['chunk_length'] - 1) {
        
        array_push($sale_warning, 'done');
        die(json_encode($sale_warning));
    }
    die(json_encode($sale_warning));
}

function eh_bep_update_meta_fn($id, $key, $value) {
    update_post_meta($id, $key, $value);
}

function eh_bep_list_table_all_callback() {
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $obj = new Eh_DataTables();
    $obj->input();
    $obj->ajax_response('1');
}

function eh_clear_all_callback() {
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    update_option('eh_bulk_edit_choosed_product_id', eh_bep_get_first_products());
    $obj = new Eh_DataTables();
    $obj->input();
    $obj->ajax_response();
}

function eh_bep_search_filter_callback() {
    set_time_limit(300);
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $obj_fil = new Eh_DataTables();
    $obj_fil->input();
    $obj_fil->ajax_response('1');
}


function xa_bep_get_selected_products($table_obj = null) {
    $sel_ids = array();
    if (isset($_REQUEST['count_products'])) {
        $sel_ids = get_option('xa_bulk_selected_ids');
        return $sel_ids;
    }
    delete_option('xa_bulk_selected_ids');
    $page_no = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $filter_range = !empty($_REQUEST['range']) ? $_REQUEST['range'] : '';
    $filter_desired_price = (float) sanitize_text_field(!empty($_REQUEST['desired_price']) ? $_REQUEST['desired_price'] : '');
    $filter_minimum_price = sanitize_text_field(!empty($_REQUEST['minimum_price']) ? $_REQUEST['minimum_price'] : '');
    $filter_maximum_price = sanitize_text_field(!empty($_REQUEST['maximum_price']) ? $_REQUEST['maximum_price'] : '');
    $selected_products = array();
    $per_page = (get_option('eh_bulk_edit_table_row')) ? get_option('eh_bulk_edit_table_row') : 20;
    $pid_to_include = xa_bep_filter_products();
    $ids = array();

    if ((WC()->version < '2.7.0')) {
        foreach ($pid_to_include as $pid) {
            $product = wc_get_product($pid);
            $title_valid = true;
            if (isset($_REQUEST['product_title_select']) && $_REQUEST['product_title_select'] != 'all' && $_REQUEST['product_title_text'] != '') {
                $product_title = strtolower($product->post->post_title);
                $product_title_text = strtolower($_REQUEST['product_title_text']);
                $length = strlen($product_title_text);
                $title_valid = true;
                if ((($_REQUEST['product_title_select'] == 'starts_with') && !(substr_compare($product_title, $product_title_text, 0, $length) === 0))) {
                    $title_valid = false;
                } else if ((($_REQUEST['product_title_select'] == 'ends_with') && !(substr_compare($product_title, $product_title_text, -$length) === 0))) {
                    $title_valid = false;
                } else if ((($_REQUEST['product_title_select'] == 'contains') && !(strpos($product_title, $product_title_text) !== false))) {
                    $title_valid = false;
                }  else if ((($_REQUEST['product_title_select'] == 'title_regex'))) {
                        if(@preg_match('/'.$product_title_text.'/', null) === false){
                            update_option('xa_regex_error',true);
                            break;
                        }
                        else if (!(preg_match('/'.$product_title_text.'/', $product_title))) {
                            $title_valid = false;
                        }
                    }
            }

            if ($title_valid) {
                $price_valid = 0;
                apply_filters('http_request_timeout', 30);
                $temp_id = $product->id;
                $temp_type = $product->product_type;
                if ($temp_type == 'simple') {
                    if ($filter_range != 'all' && !empty($filter_range)) {
                        switch ($filter_range) {
                            case '>':
                                if ($proudct->get_regular_price() >= $filter_desired_price) {
                                    $price_valid = 1;
                                }
                                break;
                            case '<':
                                if ($product->get_regular_price() <= $filter_desired_price) {
                                    $price_valid = 1;
                                }
                                break;
                            case '=':
                                if ($product->get_regular_price() == $filter_desired_price) {
                                    $price_valid = 1;
                                }
                                break;
                            case '|':
                                if ($product->get_regular_price() >= $filter_minimum_price && $product->get_regular_price() <= $filter_maximum_price) {
                                    $price_valid = 1;
                                }
                                break;
                        }
                    } else {
                        $price_valid = 1;
                    }

                    if ($price_valid) {
                        array_push($ids, $temp_id);
                        $selected_products[$temp_id] = $product;
                    }
                } 
                if (isset($_REQUEST['page']) && !empty($table_obj)) {
                    break;
                }
            }
        }
    } else {
        $pid_ch = array_chunk($pid_to_include, 500);
        for ($i = 0; $i < count($pid_ch); $i++) {

            $args = array(
                'status' => array('private', 'publish'),
                'include' => $pid_ch[$i],
                'limit' => 500
            );
            $query = wc_get_products($args);
            foreach ($query as $product) {
                $title_valid = true;
                if (isset($_REQUEST['product_title_select']) && $_REQUEST['product_title_select'] != 'all' && $_REQUEST['product_title_text'] != '') {
                    $product_title = strtolower($product->get_name());
                    $product_title_text = strtolower($_REQUEST['product_title_text']);
                    $length = strlen($product_title_text);
                    $title_valid = true;
                    if ((($_REQUEST['product_title_select'] == 'starts_with') && !(substr_compare($product_title, $product_title_text, 0, $length) === 0))) {
                        $title_valid = false;
                    } else if ((($_REQUEST['product_title_select'] == 'ends_with') && !(substr_compare($product_title, $product_title_text, -$length) === 0))) {
                        $title_valid = false;
                    } else if ((($_REQUEST['product_title_select'] == 'contains') && !(strpos($product_title, $product_title_text) !== false))) {
                        $title_valid = false;
                    } else if ((($_REQUEST['product_title_select'] == 'title_regex'))){//
                    // && !(preg_match($product_title_text, $product_title)))) {
                        if(@preg_match('/'.$product_title_text.'/', null) === false){
                            update_option('xa_regex_error',true);
                            break;
                        }
                        else if (!(preg_match('/'.$product_title_text.'/', $product_title))) {
                            $title_valid = false;
                        }
                    }
                }
                if ($title_valid) {
                    $price_valid = 0;
                    apply_filters('http_request_timeout', 30);
                    $temp_id = $product->get_id();
                    $temp_type = $product->get_type();
                    if ($temp_type == 'simple') {
                        if ($filter_range != 'all' && !empty($filter_range)) {
                            switch ($filter_range) {
                                case '>':
                                    if ($product->get_regular_price() >= $filter_desired_price) {
                                        $price_valid = 1;
                                    }
                                    break;
                                case '<':
                                    if ($product->get_regular_price() <= $filter_desired_price) {
                                        $price_valid = 1;
                                    }
                                    break;
                                case '=':
                                    if ($product->get_regular_price() == $filter_desired_price) {
                                        $price_valid = 1;
                                    }
                                    break;
                                case '|':
                                    if ($product->get_regular_price() >= $filter_minimum_price && $product->get_regular_price() <= $filter_maximum_price) {
                                        $price_valid = 1;
                                    }
                                    break;
                            }
                        } else {
                            $price_valid = 1;
                        }

                        if ($price_valid) {
                            array_push($ids, $temp_id);
                            $selected_products[$temp_id] = $product;
                        }
                    }
                }
            }
            if (isset($_REQUEST['page']) && !empty($table_obj)) {
                break;
            }
        }
    }

    update_option('xa_bulk_selected_ids', $ids);
    $selected_chunk = array();
    $selected_chunk = array_chunk($selected_products, $per_page, true);
    $total_pages = count($selected_chunk);
    if (isset($_REQUEST['page']) && !empty($table_obj) && ($total_pages == 1)) {
        $total_pages++;
    }
    $ele_on_page = count($selected_products);
    if (!empty($table_obj)) {
        $table_obj->set_pagination_args(array(
            'total_items' => count($selected_products),
            'per_page' => $ele_on_page,
            'total_pages' => $total_pages
        ));
    }
    //return $selected_products;
    if (!empty($selected_chunk)) {
        return $selected_chunk[$page_no - 1];
    }
}

function xa_bep_filter_products() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $sql = "SELECT 
                    DISTINCT ID 
                FROM {$prefix}posts 
                    LEFT JOIN {$prefix}term_relationships on {$prefix}term_relationships.object_id={$prefix}posts.ID 
                    LEFT JOIN {$prefix}term_taxonomy on {$prefix}term_taxonomy.term_taxonomy_id  = {$prefix}term_relationships.term_taxonomy_id 
                    LEFT JOIN {$prefix}terms on {$prefix}terms.term_id  ={$prefix}term_taxonomy.term_id 
                WHERE  post_type = 'product' AND post_status='publish'";

    $attr_condition = "";
    if (!empty($_REQUEST['attribute_value']) && is_array($_REQUEST['attribute_value'])) {
        $attribute_value = implode(",", $_REQUEST['attribute_value']);
        $attribute_value = stripslashes($attribute_value);
        $attr_condition = " CONCAT(taxonomy,':',slug)  in ({$attribute_value}) ";
    }
    $category_condition = "";
    if (!empty($_REQUEST['category']) && is_array($_REQUEST['category'])) {
        $selected_categories = $_REQUEST['category'];
        $cat_cond = "";
        $t_arr = array();
        if ($_REQUEST['sub_category'] == true) {
            while (!empty($selected_categories)) {
                $slug_name = $selected_categories[0];
                $slug_name = trim($slug_name, "\'");
                if ($cat_cond == "") {
                    $cat_cond = "'" . $slug_name . "'";
                } else {
                    $cat_cond = $cat_cond . ",'" . $slug_name . "'";
                }
                unset($selected_categories[0]);
                $t_arr = xa_subcats_from_parentcat_by_slug($slug_name);
                $selected_categories = array_merge($selected_categories, $t_arr);
            }
        } else {
            $category = implode(",", $_REQUEST['category']);
            $category = stripslashes($category);
            $cat_cond = $category;
        }
        $category_condition = " taxonomy='product_cat' AND slug  in ({$cat_cond}) ";
    }
        $product_type_condition = " taxonomy='product_type'  AND slug  in ('simple') ";

    if (!empty($attr_condition) && !empty($category_condition)) {
        $main_query = $sql . " AND " . $attr_condition . " AND ID IN (" . $sql . " AND " . $category_condition . " AND ID IN (" . $sql . " AND " . $product_type_condition . "))";
    } elseif (!empty($attr_condition) && empty($category_condition)) {
        $main_query = $sql . " AND " . $attr_condition . " AND ID IN (" . $sql . " AND " . $product_type_condition . ")";
    } elseif (!empty($category_condition) && empty($attr_condition)) {
        $main_query = $sql . " AND " . $category_condition . " AND ID IN (" . $sql . " AND " . $product_type_condition . ")";
    } else {
        $main_query = $sql . " AND " . $product_type_condition;
    }
    $result = $wpdb->get_results($main_query, ARRAY_A);
    $ids = wp_list_pluck($result, 'ID');
    if (empty($ids)) {
        return array(0);
    }
    return $ids;
}

//Get Subcategories
function xa_subcats_from_parentcat_by_slug($parent_cat_slug) {
    $ID_by_slug = get_term_by('slug', $parent_cat_slug, 'product_cat');
    $product_cat_ID = $ID_by_slug->term_id;
    $args = array(
        'hierarchical' => 1,
        'show_option_none' => '',
        'hide_empty' => 0,
        'parent' => $product_cat_ID,
        'taxonomy' => 'product_cat'
    );
    $subcats = get_categories($args);
    $temp_arr = array();
    foreach ($subcats as $sc) {
        array_push($temp_arr, $sc->slug);
    }
    return $temp_arr;
}
