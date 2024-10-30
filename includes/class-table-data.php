<?php

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Eh_DataTables extends WP_List_Table {

    public $main_data;
    public $variation_data;

    function __construct() {
        parent::__construct(array(
            'singular' => 'Product',
            'plural' => 'Products',
            'ajax' => true
        ));
    }

    public function input() {
        global $woocommerce;
        $_products = xa_bep_get_selected_products($this);
        $placeholder = $woocommerce->plugin_url() . '/assets/images/placeholder.png';
        $product_data = array();
        if (!empty($_products)) {
            foreach ($_products as $temp_id => $temp) {
                $temp_type = (WC()->version < '2.7.0') ? $temp->product_type : $temp->get_type();
                $temp_title = (WC()->version < '2.7.0') ? $temp->post->post_title : $temp->get_name();
                $temp_dim = "-";
                if (WC()->version < '2.7.0') {
                    if ($temp->get_dimensions() != NULL) {
                        $temp_dim = $temp->get_dimensions();
                    }
                } else {
                    if ($temp->get_dimensions(FALSE) != "") {
                        $temp_dim = wc_format_dimensions($temp->get_dimensions(FALSE));
                    }
                }
                $get_category = '';
                if ((WC()->version > '2.7.0') && $temp_type == 'variation') {
                    $get_category = $temp->get_parent_id();
                } else {
                    $get_category = $temp_id;
                }
                $parent_id = $temp_id;
                if ($temp_type == 'variation') {
                    $parent_id = (WC()->version < '2.7.0') ? $temp->parent->id : $temp->get_parent_id();
                }

                if ($temp_type == 'simple' || $temp_type == 'variable' || $temp_type == 'variation') {
                    $meta_thumb = get_post_meta($temp_id, '_thumbnail_id', true);
                    $i = $temp_id;
                    $product_data[$i]['product_id'] = $temp_id;
                    $product_data[$i]['parent_id'] = $parent_id;
                    $product_data[$i]['product_title'] = $temp_title;
                    $product_data[$i]['product_date'] = get_the_date('', $temp_id);
                    $product_data[$i]['product_type'] = ucfirst($temp_type);
                    $product_data[$i]['product_type_meta'] = ($temp->is_downloadable() != null) ? 'Downloadable' : (($temp->is_virtual() != null) ? 'Virtual' : 'Item');
                    $product_data[$i]['product_thumb'] = ($meta_thumb != 0) ? wp_get_attachment_thumb_url($meta_thumb) : $placeholder;
                    $product_data[$i]['product_sku'] = ($temp->get_sku() != null) ? $temp->get_sku() : '-';
                    $product_data[$i]['product_category'] = (WC()->version < '2.7.0') ? $temp->get_categories() : wc_get_product_category_list($get_category);
                    $product_data[$i]['product_stock_status'] = ($temp->is_in_stock() != null) ? 'In Stock ' : 'Out of Stock';
                    $product_data[$i]['product_stock_quantity'] = ($temp->get_stock_quantity() != null) ? $temp->get_stock_quantity() : ' - ';
                    $product_data[$i]['product_dimensions'] = $temp_dim;
                    $product_data[$i]['product_weight'] = ($temp->get_weight() != null) ? $temp->get_weight() : '-';
                    $att = $temp->get_attributes();
                    $product_data[$i]['product_attributes'] = '';
                    if (WC()->version < '2.7.0' && $temp_type == 'variation') {
                        $att = $temp->get_variation_attributes();
                    }
                    if ($att != null) {
                        foreach ($att as $key => $value) {
                            if ($temp_type == 'variation') {
                                $attrib_slug = (WC()->version < '2.7.0') ? substr($key, 10) : $key;
                                $product_data[$i]['product_attributes'] = ($product_data[$i]['product_attributes'] == null) ? wc_attribute_label($attrib_slug, $temp) : $product_data[$i]['product_attributes'] . ', ' . wc_attribute_label($attrib_slug, $temp);
                            } else {
                                $attrib_slug = !empty($value['name']) ? $value['name'] : '';
                                $product_data[$i]['product_attributes'] = ($product_data[$i]['product_attributes'] == null) ? wc_attribute_label($attrib_slug, $temp) : $product_data[$i]['product_attributes'] . ' , ' . wc_attribute_label($attrib_slug, $temp);
                            }
                        }
                    } else {
                        $product_data[$i]['product_attributes'] = '-';
                    }
                    if ($temp_type == 'variable') {
                        $product_data[$i]['product_sale'] = ($temp->get_variation_sale_price('min', true) == $temp->get_variation_sale_price('max', true)) ? $temp->get_variation_sale_price('max', true) : $temp->get_variation_sale_price('min', true) . '-' . $temp->get_variation_sale_price('max', true);
                        $product_data[$i]['product_regular'] = ($temp->get_variation_regular_price('min', true) == $temp->get_variation_regular_price('max', true)) ? $temp->get_variation_regular_price('max', true) : $temp->get_variation_regular_price('min', true) . '-' . $temp->get_variation_regular_price('max', true);
                    } else {
                        $product_data[$i]['product_sale'] = $temp->get_sale_price();
                        $product_data[$i]['product_regular'] = $temp->get_regular_price();
                    }
                } else {
                    continue;
                }
            }
        }
        $this->items = $product_data;
    }

    function column_title($item) {
        //Build row actions
        //Return the title contents
        if ($item['product_type'] == 'Variable') {
            $meta = "Parent";
        } else {
            $meta = $item['product_type_meta'];
        }
        $item['product_title'] = preg_replace('/%/', '%%', $item['product_title']);
        $item['product_category'] = preg_replace('/%/', '%%', $item['product_category']);
        $item['product_category'] = preg_replace('/<a/', '<a target="_blank"', $item['product_category']);
        $item['product_id'] = '<a target="_blank" href="' . home_url() . '/wp-admin/post.php?post=' . $item['parent_id'] . '&action=edit" rel="tag">' . $item['product_id'] . '</a>';
        return sprintf($item['product_title'] . '<span style="color:black"> (Id : ' . $item['product_id'] . ') </span> <br> <span id="category" >' . $item['product_category'] . '</span> <br><span id="type" class="table-type-text">Type :</span> ' . $item['product_type'] . ' (' . $meta . ') ');
    }

    function column_thumb($item) {

        return sprintf('<img style="width:52px;" src="' . $item['product_thumb'] . '"/>');
    }

    function column_stock($item) {
        $item['product_sku'] = preg_replace('/%/', '%%', $item['product_sku']);
        return sprintf('<span id="sku" class="table-type-text" >SKU : </span>' . $item['product_sku'] . '<br><span id="stock_status" class="table-type-text">Status :</span> ' . $item['product_stock_status'] . '<br><span id="stock_quantity" class="table-type-text">Quantity : </span>' . $item['product_stock_quantity']);
    }

    function column_price($item) {

        return sprintf('<span id="sale_price" class="table-type-text">Sale :</span> ' . $item['product_sale'] . '<br><span id="regular_price" class="table-type-text">Regular : </span>' . $item['product_regular']);
    }

    function column_properties($item) {
        $item['product_attributes'] = preg_replace('/%/', '%%', $item['product_attributes']);
        return sprintf('<span id="atribute" class="table-type-text">Attributes : </span>' . $item['product_attributes'] . '<br><span id="dimension" class="table-type-text">Dimension :</span> ' . $item['product_dimensions'] . '<br><span id="weight" class="table-type-text">Weight : </span>' . $item['product_weight']);
    }

    function column_published($item) {

        return sprintf('<span id="dimension" class="table-content-td">' . $item['product_date'] . '</span>');
    }

    function get_columns() {

        return $columns = array(
            'thumb' => '<span class="wc-image">Image</span>',
            'title' => 'Title',
            'properties' => 'Properties',
            'stock' => 'Stock',
            'price' => 'Price',
            'published' => 'Published'
        );
    }

    function get_sortable_columns() {

        return $sortable_columns = array();
    }

    function get_bulk_actions() {

        return $actions = array();
    }

    function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
    }

    function prepare_items($page_num = '', $prepare = '', $page_count = '') {
        $per_page = ($page_count == '') ? ((get_option('eh_bulk_edit_table_row')) ? get_option('eh_bulk_edit_table_row') : 20) : $page_count;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );
        $this->process_bulk_action();
        $this->input();
    }

    function display() {
        parent::display();
    }

    function ajax_response($page_num = '') {

        $this->prepare_items($page_num);

        extract($this->_args);
        extract($this->_pagination_args, EXTR_SKIP);

        ob_start();
        if (!empty($_REQUEST['no_placeholder']))
            $this->display_rows();
        else
            $this->display_rows_or_placeholder();
        $rows = ob_get_clean();

        ob_start();
        $this->print_column_headers();
        $headers = ob_get_clean();

        ob_start();
        $this->pagination('top');
        $pagination_top = ob_get_clean();

        ob_start();
        $this->pagination('bottom');
        $pagination_bottom = ob_get_clean();

        $response = array(
            'rows' => $rows
        );
        $response['pagination']['top'] = $pagination_top;
        $response['pagination']['bottom'] = $pagination_bottom;
        $response['column_headers'] = $headers;

        if (isset($total_items))
            $response['total_items_i18n'] = sprintf(_n('1 item', '%s items', $total_items), number_format_i18n($total_items));
        $response['total_items_count'] = $total_items;

        if (isset($total_pages)) {
            $response['total_pages'] = $total_pages;
            $response['total_pages_i18n'] = number_format_i18n($total_pages);
        }
         $is_regex_error = get_option('xa_regex_error');
         if($is_regex_error) {
             $response['regex_error'] = true;
             delete_option('xa_regex_error');
         }
        die(json_encode($response));
    }

}

function eh_bep_ajax_data_callback() {
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $obj = new Eh_DataTables();
    $obj->input();
    $obj->ajax_response();
}

add_action('wp_ajax_eh_bep_ajax_table_data', 'eh_bep_ajax_data_callback');

/**
 * This function adds the jQuery script to the plugin's page footer
 */
function admin_header() {
    $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
    if ('eh-bulk-edit-product-attr' != $page)
        return;
    echo '<style type="text/css">';
    echo '.wp-list-table .column-properties { width: 20%; }';
    echo '.wp-list-table .column-published { width: 8%;}';
    echo '</style>';
}

function eh_bep_ajax_table_script() {
    $screen = get_current_screen();
    if ('woocommerce_page_eh-bulk-edit-product-attr' != $screen->id)
        return false;
    ?>
    <script type="text/javascript">
        (function (jQuery) {

            list = {
                init: function () {

                    // This will have its utility when dealing with the page number input
                    var timer;
                    var delay = 500;

                    // Pagination links, sortable link
                    jQuery('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function (e) {
                        // We don't want to actually follow these links
                        e.preventDefault();
                        // Simple way: use the URL to extract our needed variables
                        var query = this.search.substring(1);

                        var data = {
                            paged: list.__query(query, 'paged') || '1',
                        };
                        list.update(data);
                    });

                    // Page number input
                    jQuery('input[name=paged]').on('keyup', function (e) {
                        if (13 == e.which)
                            e.preventDefault();

                        // This time we fetch the variables in inputs
                        var data = {
                            paged: parseInt(jQuery('input[name=paged]').val()) || '1',
                        };
                        window.clearTimeout(timer);
                        timer = window.setTimeout(function () {
                            list.update(data);
                        }, delay);
                    });
                },
                update: function (data) {
                    jQuery(".loader").css("display", "block");
                    var type_data = '';
                    var category_data = '';
                    var attribute_data = '';
                    var attribute_value_data = '';
                    var range_data = '';
                    var desired_price_data = '';
                    var minimum_price_data = '';
                    var maximum_price_data = '';
                    var sub_cat = '';
                    type_data = jQuery("#product_type").val();
                    category_data = (jQuery("#category_select").chosen().val());
                    attribute_data = getValue_attrib_name();
                    if (jQuery("#subcat_check").attr("checked")) {
                        sub_cat = true;
                    }
                    if (getValue_attrib_name() != '')
                        attribute_value_data = jQuery("#select_input_attributes").chosen().val();
                    else {
                        attribute_value_data = ''
                    }
                    range_data = jQuery("#regular_price_range_select").val();
                    if (jQuery("#regular_price_range_select").val() != 'all')
                    {
                        if (jQuery("#regular_price_range_select").val() != '|')
                            desired_price_data = jQuery("#regular_price_text_val").val();
                        else {
                            minimum_price_data = jQuery("#regular_price_min_text").val();
                            maximum_price_data = jQuery("#regular_price_max_text").val();
                        }
                    }

                    var prod_title_select = jQuery("#product_title_select").val();
                    ;
                    var prod_title_text = '';
                    if (jQuery("#product_title_select").val() != 'all')
                    {
                        prod_title_text = jQuery("#product_title_text_val").val();
                    }

                    jQuery.ajax({
                        type: 'post',
                        url: ajaxurl,
                        data: jQuery.extend({
                            _ajax_eh_bep_nonce: jQuery('#_ajax_eh_bep_nonce').val(),
                            action: 'eh_bep_filter_products',
                            type: type_data,
                            category: category_data,
                            sub_category: sub_cat,
                            attribute: attribute_data,
                            product_title_select: prod_title_select,
                            product_title_text: prod_title_text,
                            attribute_value: attribute_value_data,
                            range: range_data,
                            desired_price: desired_price_data,
                            minimum_price: minimum_price_data,
                            maximum_price: maximum_price_data
                        },
                                data
                                ),
                        // Handle the successful result
                        success: function (response) {
                            jQuery(".loader").css("display", "none");
                            // WP_List_Table::ajax_response() returns json
                            var response = jQuery.parseJSON(response);

                            // Add the requested rows
                            if (response.rows.length)
                                jQuery('#the-list').html(response.rows);
                            // Update column headers for sorting
                            if (response.column_headers.length)
                                jQuery('thead tr, tfoot tr').html(response.column_headers);
                            // Update pagination for navigation
                            if (response.pagination.bottom.length)
                                jQuery('.tablenav.top .tablenav-pages').html(jQuery(response.pagination.top).html());
                            if (response.pagination.top.length)
                                jQuery('.tablenav.bottom .tablenav-pages').html(jQuery(response.pagination.bottom).html());

                            // Init back our event handlers
                            list.init();
                        }
                    });
                },
                __query: function (query, variable) {

                    var vars = query.split("&");
                    for (var i = 0; i < vars.length; i++) {
                        var pair = vars[i].split("=");
                        if (pair[0] == variable)
                            return pair[1];
                    }
                    return false;
                },
            }

            // Show time!
            list.init();

        })(jQuery);
    </script>
    <?php

}

function eh_bep_get_first_products() {
    set_time_limit(300);
    $args = array(
        'post_type' => 'product',
        'fields' => 'ids',
        'numberposts' => 11
    );
    $variations_id = array();
    $product_all_id = get_posts($args);
    $product_id = array();
    for ($i = 0; $i < count($product_all_id); $i++) {
        apply_filters('http_request_timeout', 30);
        $temp = wc_get_product($product_all_id[$i]);
        $temp_type = (WC()->version < '2.7.0') ? $temp->product_type : $temp->get_type();
        $temp_id = (WC()->version < '2.7.0') ? $temp->id : $temp->get_id();
        if ($temp_type == 'simple') {
            array_push($product_id, $product_all_id[$i]);
        }
        if ($temp_type == 'variable') {
            array_push($product_id, $product_all_id[$i]);
            $variations_temp_id = array();
            $vari = $temp->get_available_variations();
            foreach ($vari as $key => $value) {
                array_push($variations_temp_id, (string) $value['variation_id']);
            }
            $variations_id[$temp_id] = $variations_temp_id;
        }
    }

    update_option('eh_bulk_edit_choosed_variation_id', $variations_id);
    return $product_id;
}
?>
<?php

add_action('admin_head', 'admin_header');
add_action('admin_footer', 'eh_bep_ajax_table_script');
