<?php

class VendorAdminWooHelper
{

    public function sendError($code, $message, $statusCode)
    {
        return new WP_Error($code, $message, array(
            'status' => $statusCode
        ));
    }

    protected function get_product_item($id)
    {
        if (!wc_get_product($id)) return $this->sendError("invalid_product", "This product does not exist", 404);
        return wc_get_product($id);
    }

    protected function find_image_id($image)
    {
        $image_id = attachment_url_to_postid(stripslashes($image));
        return $image_id;
    }

    protected function http_check($url)
    {
        if ((!(substr($url, 0, 7) == 'http://')) && (!(substr($url, 0, 8) == 'https://'))) {
            return false;
        }
        return true;
    }

    protected function get_attribute_taxonomy_name($slug, $product)
    {
        $attributes = $product->get_attributes();

        if (!isset($attributes[$slug])) {
            return str_replace('pa_', '', $slug);
        }

        $attribute = $attributes[$slug];

        // Taxonomy attribute name.
        if ($attribute->is_taxonomy()) {
            $taxonomy = $attribute->get_taxonomy_object();
            return $taxonomy->attribute_label;
        }

        // Custom product attribute name.
        return $attribute->get_name();
    }

    protected function get_attribute_options($product_id, $attribute)
    {
        if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']) {
            return wc_get_product_terms($product_id, $attribute['name'], array(
                'fields' => 'names',
            ));
        } elseif (isset($attribute['value'])) {
            return array_map('trim', explode('|', $attribute['value']));
        }

        return array();
    }


    protected function get_attribute_slugs($product_id, $attribute)
    {
        if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']) {
            return wc_get_product_terms($product_id, $attribute['name'], array(
                'fields' => 'slugs',
            ));
        } elseif (isset($attribute['value'])) {
            return array_map('trim', explode('|', $attribute['value']));
        }

        return array();
    }

    /// GET FUNCTIONS
    public function flutter_get_products($request, $user_id)
    {
        $page = isset($request["page"]) ? sanitize_text_field($request["page"])  : 1;
        $limit = isset($request["per_page"]) ? sanitize_text_field($request["per_page"]) : 10;
        if(!is_numeric($page)){
            $page = 1;
        }
        if(!is_numeric($limit)){
            $limit = 10;
        }
        $terms = array(
            'post_type' => 'product',
            'posts_per_page' => $limit,
            'paged' => $page,
            'post_status' => 'any'
        );

        // Added search product feature
        if (isset($request['search'])) {
            $terms['s'] = sanitize_text_field($request['search']);
        }

        $loop = new WP_Query($terms);
        if ($loop->have_posts()) {
            while ($loop->have_posts()):
                $loop->the_post();
                $product = wc_get_product(get_the_ID());
                $p = $product->get_data();
                $image_arr = array();
                foreach (array_filter($p['gallery_image_ids']) as $img) {
                    $image = wp_get_attachment_image_src($img, 'full');
                    if (!is_null($image[0])) {
                        $image_arr[] = $image[0];
                    }
                }

                $image = wp_get_attachment_image_src($p['image_id'], 'full');
                if (!is_null($image[0])) {
                    $p['featured_image'] = $image[0];
                }

                $p['images'] = $image_arr;
                $p['category_ids'] = array();
                $category_ids = wp_get_post_terms($p['id'], 'product_cat');
                foreach ($category_ids as $cat) {
                    if ($cat->slug != 'uncategorized') {
                        $p['category_ids'][] = $cat->term_id;
                    }
                }

                $p['type'] = $product->get_type();
                $p['on_sale'] = $product->is_on_sale();
                $p['tags'] = wp_get_post_terms($product->get_id(), 'product_tag');

                $attributes = array();
                $is_variable = false;
                foreach ($product->get_attributes() as $attribute) {
                    $attributes[] = array(
                        'id' => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name($attribute['name']) : 0,
                        'name' => 0 === strpos($attribute['name'], 'pa_') ? get_taxonomy($attribute['name'])
                            ->labels->singular_name : $attribute['name'],
                        'position' => (int)$attribute['position'],
                        'visible' => (bool)$attribute['is_visible'],
                        'variation' => (bool)$attribute['is_variation'],
                        'options' => $this->get_attribute_options($product->get_id(), $attribute),
                        'slugs' => $this->get_attribute_slugs($product->get_id(), $attribute),
                        'default' => 0 === strpos($attribute['name'], 'pa_'),
                        'slug' => $attribute['name']
                    );

                    if ((bool)$attribute['is_variation'] && !$is_variable) {
                        $is_variable = true;
                    }
                }
                $p['attributesData'] = $attributes;

                if ($is_variable) {
                    $p['type'] = 'variable';
                    $result = array();
                    $query = ['post_parent' => $product->get_id(), 'post_status' => ['publish', 'private'], 'post_type' => ['product_variation'], 'posts_per_page' => -1,];

                    $wc_query = new WP_Query($query);
                    while ($wc_query->have_posts()) {
                        $wc_query->next_post();
                        $result[] = $wc_query->post;
                    }
                    foreach ($result as $variation) {
                        $p_varation = new WC_Product_Variation($variation->ID);
                        $dataVariation = array();
                        $dataVariation['variation_id'] = $p_varation->get_id();
                        $dataVariation['max_qty'] = $p_varation->get_stock_quantity();
                        $dataVariation['variation_is_active'] = $p_varation->get_status() == 'publish';
                        $dataVariation['display_price'] = $p_varation->get_sale_price();
                        $dataVariation['display_regular_price'] = $p_varation->get_regular_price();
                        $dataVariation['slugs'] = $p_varation->get_attributes();
                        $dataVariation['manage_stock'] = $p_varation->get_manage_stock();
                        $attributes = $p_varation->get_attributes();
                        $dataVariation['attributes'] = array();
                        foreach ($dataVariation['slugs'] as $key => $value) {
                            foreach ($p['attributesData'] as $item) {
                                if ($item['slug'] === $key) {
                                    for ($i = 0; $i < count($item['slugs']); $i++) {
                                        if ($value === $item['slugs'][$i]) {
                                            $dataVariation['attributes'][$key] = $item['options'][$i];
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        $p['variable_products'][] = $dataVariation;
                    }

                    if (empty($p['variable_products'])) {
                        $p['type'] = 'simple';
                    }

                }
                $products_arr[] = $p;
            endwhile;
        }
        return apply_filters("flutter_get_products", $products_arr, $request, $user_id);
    }

    public function flutter_get_orders($request, $user_id)
    {
        global $wpdb;
        $api = new WC_REST_Orders_V1_Controller();
        $results = [];
        $page = 1;
        $per_page = 10;

        if (isset($request['page'])) {
            $page = sanitize_text_field($request['page']);
            if(!is_numeric($page)){
                $page = 1;
            }
        }
        if (isset($request['per_page'])) {
            $per_page = sanitize_text_field($request['per_page']);
            if(!is_numeric($per_page)){
                $per_page = 10;
            }
        }
        $page = ($page - 1) * $per_page;

        $table_name = $wpdb->prefix . "posts";
        $sql = "SELECT * FROM " . $table_name . " WHERE post_type LIKE 'shop_order'";

        if (isset($request['status'])) {
            $sql .= " AND post_status = %s";
        }
        if (isset($request['search'])) {
            $sql .= " AND ID LIKE %s";
        }
        $sql .= " GROUP BY $table_name.`ID` ORDER BY $table_name.`ID` DESC LIMIT %d OFFSET %d";
 
        $args = array();
        if (isset($request['status'])) {
            $args[] = 'wc-'.sanitize_text_field($request['status']);
        }
        if (isset($request['search'])) {
            $args[] = '%'.sanitize_text_field($request['search']).'%';
        }
        $args[] = $per_page;
        $args[] = $page;
        $sql = $wpdb->prepare($sql, $args);
        $query = $wpdb->get_results($sql);
        // Loop through each order post object
        foreach ($query as $item) {
            $order = wc_get_order($item->ID);
            if (is_bool($order)) {
                continue;
            }
            $response = $api->prepare_item_for_response($order, $request);
            $order = $response->get_data();
            $count = count($order['line_items']);
            $order['product_count'] = $count;
            $order = getCommissionOrderResponse($order, $user_id);

            for ($i = 0; $i < $count; $i++) {
                $product_id = absint($order['line_items'][$i]['product_id']);
                $image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id));
                if ($image && count($image) > 0 && !is_null($image[0])) {
                    $order['line_items'][$i]['featured_image'] = $image[0];
                }
            }
            $results[] = $order;
        }

        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $results,
        ), 200);
    }

    public function flutter_get_sale_stats($user_id)
    {
        $id = $user_id;
        $price_decimal = get_option('woocommerce_price_num_decimals', 2);
        $sales_stats['gross_sales']['last_month'] = $this->wcfm_get_gross_sales_by_vendor($id, 'last_month');
        $sales_stats['gross_sales']['month'] = $this->wcfm_get_gross_sales_by_vendor($id, 'month');
        $sales_stats['gross_sales']['year'] = $this->wcfm_get_gross_sales_by_vendor($id, 'year');
        $sales_stats['gross_sales']['week_1'] = $this->wcfm_get_gross_sales_by_vendor($id, '7day');
        $sales_stats['gross_sales']['week_2'] = round($this->wcfm_get_gross_sales_by_vendor($id, '14day'), $price_decimal);
        $sales_stats['gross_sales']['week_3'] = round($this->wcfm_get_gross_sales_by_vendor($id, '21day'), $price_decimal);
        $sales_stats['gross_sales']['week_4'] = round($this->wcfm_get_gross_sales_by_vendor($id, '28day'), $price_decimal);
        $sales_stats['gross_sales']['week_5'] = round($this->wcfm_get_gross_sales_by_vendor($id, '35day'), $price_decimal);
        $sales_stats['gross_sales']['all'] = round($this->wcfm_get_gross_sales_by_vendor($id, 'all'), $price_decimal);
        if ($sales_stats['gross_sales']['last_month'] != 0) {
            $profit_percentage = round($sales_stats['gross_sales']['month'] - $sales_stats['gross_sales']['last_month'], 2);
            $profit_percentage = round($profit_percentage / $sales_stats['gross_sales']['last_month'] * 100 / 100, 2);
        } else {
            $profit_percentage = round($sales_stats['gross_sales']['month'] - $sales_stats['gross_sales']['last_month'], 2);
            $profit_percentage = round($profit_percentage / 1 * 100 / 100, 2);
        }
        $sales_stats['gross_sales']['profit_percentage'] = $profit_percentage;
        $sales_stats['earnings']['last_month'] = round($this->wcfm_get_commission_by_vendor($id, 'last_month'), $price_decimal);
        $sales_stats['earnings']['month'] = round($this->wcfm_get_commission_by_vendor($id, 'month'), $price_decimal);
        $sales_stats['earnings']['year'] = round($this->wcfm_get_commission_by_vendor($id, 'year'), $price_decimal);
        $sales_stats['earnings']['week_1'] = round($this->wcfm_get_commission_by_vendor($id, '7day'), $price_decimal);
        $sales_stats['earnings']['week_2'] = round($this->wcfm_get_commission_by_vendor($id, '14day'), $price_decimal);
        $sales_stats['earnings']['week_3'] = round($this->wcfm_get_commission_by_vendor($id, '21day'), $price_decimal);
        $sales_stats['earnings']['week_4'] = round($this->wcfm_get_commission_by_vendor($id, '28day'), $price_decimal);
        $sales_stats['earnings']['week_5'] = round($this->wcfm_get_commission_by_vendor($id, '35day'), $price_decimal);
        $sales_stats['earnings']['all'] = round($this->wcfm_get_commission_by_vendor($id, 'all'), $price_decimal);
        if ($sales_stats['earnings']['last_month'] != 0) {
            $profit_percentage = round($sales_stats['earnings']['month'] - $sales_stats['earnings']['last_month'], 2);
            $profit_percentage = round($profit_percentage / $sales_stats['earnings']['last_month'] * 100 / 100, 2);
        } else {
            $profit_percentage = round($sales_stats['earnings']['month'] - $sales_stats['earnings']['last_month'], 2);
            $profit_percentage = round($profit_percentage / 1 * 100 / 100, 2);
        }
        $sales_stats['earnings']['profit_percentage'] = $profit_percentage;

        $sales_stats['currency'] = get_woocommerce_currency();

        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $sales_stats
        ), 200);
    }

    public function flutter_update_order_status($request, $user_id)
    {
        $order_id = sanitize_text_field($request['order_id']);
        $order_status = sanitize_text_field($request['order_status']);
        if (!is_numeric($order_id)) {
            return new WP_REST_Response(array(
                'status' => 'success',
                'response' => []
            ), 200);
        }

        $order = wc_get_order($order_id);
        $order->update_status($order_status, '', true);
        
        $note = sanitize_text_field($request['customer_note']);
        if (!empty($note)) {
            $order->add_order_note($note, true, true);
        }
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $order->get_data()
        ), 200);
    }

    public function flutter_get_reviews($request, $user_id)
    {
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => []
        ), 200);
    }

    // Update review status
    function flutter_update_review($request)
    {

    }

    function wcfm_get_gross_sales_by_vendor($vendor_id = '', $interval = '7day', $is_paid = false, $order_id = 0, $filter_date_form = '', $filter_date_to = '')
    {

        global $woocommerce, $wpdb, $product;
        include_once($woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php');

        // WooCommerce Admin Report
        $wc_report = new WC_Admin_Report();

        // Set date parameters for the current month
        switch ($interval) {
            case 'year':
                $start_date = strtotime(date('Y-m', current_time('timestamp')) . '-01 midnight');
                $end_date = strtotime('-1year', $start_date) - 86400;
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case 'last_month':
                $start_date = strtotime("first day of last month-01 midnight");
                $end_date = strtotime("last day of last month-01 midnight");
                $wc_report->start_date = $start_date;
                $wc_report->end_date = $end_date;
                break;
            case 'month':
                $start_date = strtotime("first day of this month-01 midnight");
                $end_date = strtotime("now-01 midnight");
                $wc_report->start_date = $start_date;
                $wc_report->end_date = $end_date;
                break;
            case 'custom':
                break;
            case 'all':
                $start_date = strtotime(date('Y-m', current_time('timestamp')) . '-01 midnight');
                $end_date = strtotime('-10year', $start_date) - 86400;
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '7day':
                $start_date = strtotime("now-01 midnight");
                $end_date = strtotime("now-7days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '14day':
                $start_date = strtotime("now-7days-01 midnight");
                $end_date = strtotime("now-14days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '21day':
                $start_date = strtotime("now-14days-01 midnight");
                $end_date = strtotime("now-21days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '28day':
                $start_date = strtotime("now-21days-01 midnight");
                $end_date = strtotime("now-28days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '35day':
                $start_date = strtotime("now-28days-01 midnight");
                $end_date = strtotime("now-35days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case 'default':
        }

        // Avoid max join size error
        $wpdb->query('SET SQL_BIG_SELECTS=1');

        // Get data for current month sold products
        $sold_products = $wc_report->get_order_report_data(array(
            'data' => array(
                '_product_id' => array(
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => '',
                    'name' => 'product_id'
                ),
                '_qty' => array(
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => 'SUM',
                    'name' => 'quantity'
                ),
                '_line_subtotal' => array(
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => 'SUM',
                    'name' => 'gross'
                ),
                '_line_total' => array(
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => 'SUM',
                    'name' => 'gross_after_discount'
                )
            ),
            'query_type' => 'get_results',
            'group_by' => 'product_id',
            'where_meta' => '',
            'order_by' => 'quantity DESC',
            'order_types' => wc_get_order_types('order_count'),
            'filter_range' => true,
            'order_status' => array(
                'completed'
            ),
        ));
        $data = 0;

        foreach ($sold_products as $product) {
            $data += $product->gross;
        }
        return $data;
    }

    /**
     * Total commission paid by Admin
     */
    function wcfm_get_commission_by_vendor($vendor_id = '', $interval = '7day', $is_paid = false, $order_id = 0, $filter_date_form = '', $filter_date_to = '')
    {
        global $woocommerce, $wpdb, $product;
        include_once($woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php');

        // WooCommerce Admin Report
        $wc_report = new WC_Admin_Report();

        // Set date parameters for the current month
        $start_date = strtotime(date('Y-m', current_time('timestamp')) . '-01 midnight');
        $end_date = strtotime('-1month', $start_date) - 86400;
        $wc_report->start_date = $end_date;
        $wc_report->end_date = $start_date;

        switch ($interval) {
            case 'year':
                $start_date = strtotime(date('Y-m', current_time('timestamp')) . '-01 midnight');
                $end_date = strtotime('-1year', $start_date) - 86400;
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case 'last_month':
                $start_date = strtotime("first day of last month-01 midnight");
                $end_date = strtotime("last day of last month-01 midnight");
                $wc_report->start_date = $start_date;
                $wc_report->end_date = $end_date;
                break;
            case 'month':
                $start_date = strtotime("first day of this month-01 midnight");
                $end_date = strtotime("now-01 midnight");
                $wc_report->start_date = $start_date;
                $wc_report->end_date = $end_date;
                break;
            case 'custom':
                break;
            case 'all':
                $start_date = strtotime(date('Y-m', current_time('timestamp')) . '-01 midnight');
                $end_date = strtotime('-10year', $start_date) - 86400;
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '7day':
                $start_date = strtotime("now-01 midnight");
                $end_date = strtotime("now-7days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '14day':
                $start_date = strtotime("now-7days-01 midnight");
                $end_date = strtotime("now-14days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '21day':
                $start_date = strtotime("now-14days-01 midnight");
                $end_date = strtotime("now-21days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '28day':
                $start_date = strtotime("now-21days-01 midnight");
                $end_date = strtotime("now-28days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case '35day':
                $start_date = strtotime("now-28days-01 midnight");
                $end_date = strtotime("now-35days-01 midnight");
                $wc_report->start_date = $end_date;
                $wc_report->end_date = $start_date;
                break;
            case 'default':
        }

        // Avoid max join size error
        $wpdb->query('SET SQL_BIG_SELECTS=1');

        // Get data for current month sold products
        $sold_products = $wc_report->get_order_report_data(array(
            'data' => array(
                '_product_id' => array(
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => '',
                    'name' => 'product_id'
                ),
                '_qty' => array(
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => 'SUM',
                    'name' => 'quantity'
                ),
                '_line_subtotal' => array(
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => 'SUM',
                    'name' => 'gross'
                ),
                '_line_total' => array(
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => 'SUM',
                    'name' => 'gross_after_discount'
                )
            ),
            'query_type' => 'get_results',
            'group_by' => 'product_id',
            'where_meta' => '',
            'order_by' => 'quantity DESC',
            'order_types' => wc_get_order_types('order_count'),
            'filter_range' => true,
            'order_status' => array(
                'completed'
            ),
        ));
        $data = 0;

        foreach ($sold_products as $product) {
            $data += $product->gross_after_discount;
        }
        return $data;
    }

    /* GET WCFM SALE STATS FUNCTIONS. CUSTOM BY TOAN 04/11/2020 */

    /* GET NOTIFICATIONS */
    function get_notification_by_vendor($request, $user_id)
    {

        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => [],
        ), 200);
    }

/// CREATE ///
    public function vendor_admin_create_product($request, $user_id)
    {
        $user = get_userdata($user_id);
        $isSeller = in_array("editor", $user->roles) || in_array("administrator", $user->roles);
        
        $requestStatus = "draft";
        if ($request["status"] != null) {
            $requestStatus = sanitize_text_field($request["status"]);
        }

        $name = sanitize_text_field($request["name"]);
        $description = sanitize_text_field($request["description"]);
        $short_description = sanitize_text_field($request["short_description"]);
        $featured_image = sanitize_text_field($request['featuredImage']);
        $product_images = sanitize_text_field($request['images']);
        $type = sanitize_text_field($request['type']);
        $tags = sanitize_text_field($request['tags']);
        $featured = sanitize_text_field($request['featured']);
        $regular_price = sanitize_text_field($request['regular_price']);
        $sale_price = sanitize_text_field($request['sale_price']);
        $date_on_sale_from = sanitize_text_field($request['date_on_sale_from']);
        $date_on_sale_from_gmt = sanitize_text_field($request['date_on_sale_from_gmt']);
        $date_on_sale_to = sanitize_text_field($request['date_on_sale_to']);
        $date_on_sale_to_gmt = sanitize_text_field($request['date_on_sale_to_gmt']);
        $in_stock = sanitize_text_field($request['in_stock']);
        $stock_quantity = sanitize_text_field($request['stock_quantity']);
        $manage_stock  = sanitize_text_field($request['manage_stock']);
        $backorders = sanitize_text_field($request['backorders']);
        $categories = sanitize_text_field($request['categories']);
        $productAttributes = sanitize_text_field($request['productAttributes']);
        $variations = sanitize_text_field($request['variations']);      
        $inventory_delta = sanitize_text_field($request['inventory_delta']);      

        $count = 1;


        if ($isSeller) {
            $args = [
                "post_author" => $user_id,
                "post_content" => $description,
                "post_status" => $requestStatus, // (Draft | Pending | Publish)
                "post_title" => $name,
                "post_parent" => "",
                "post_type" => "product",
            ];
            // Create a simple WooCommerce product
            $post_id = wp_insert_post($args);
            $product = wc_get_product($post_id);

            if ($product->get_type() != $type) {
                // Get the correct product classname from the new product type
                $product_classname = WC_Product_Factory::get_product_classname($product->get_id(), $type);

                // Get the new product object from the correct classname
                $product = new $product_classname($product->get_id());
                $product->save();

            }

            if (isset($featured_image)) {
                if (!empty($featured_image)) {
                    if ($this->http_check($featured_image)) {
                        $featured_image_id = $this->find_image_id($featured_image);
                        $product->set_image_id($featured_image_id);
                    } else {
                        $featured_image_id = upload_image_from_mobile($featured_image, $count, $user_id);
                        $product->set_image_id($featured_image_id);
                        $count = $count + 1;
                    }
                } else {
                    $product->set_image_id('');
                }

            }

            if (isset($product_images)) {
                $product_images_array = array_filter(explode(',', $product_images));
                $img_array = array();

                foreach ($product_images_array as $p_img) {
                    if (!empty($p_img)) {
                        if ($this->http_check($p_img)) {
                            $img_id = $this->find_image_id($p_img);
                            array_push($img_array, $img_id);
                        } else {
                            $img_id = upload_image_from_mobile($p_img, $count, $user_id);
                            array_push($img_array, $img_id);
                            $count = $count + 1;
                        }
                    }
                }
                $product->set_gallery_image_ids($img_array);
            }

            if (isset($tags)) {
                $tags = array_filter(explode(',', $tags));
                wp_set_object_terms($post_id, $tags, 'product_tag');
            }

            /// Set attributes to product
            if (isset($product) && !is_wp_error($product)) {
                if (isset($name)) {
                    $product->set_name(wp_filter_post_kses($name));
                }
                // Featured Product.
                if (isset($featured)) {
                    $product->set_featured($featured);
                }
                // SKU.
                if (isset($request['sku'])) {
                    $product->set_sku(wc_clean($request['sku']));
                }

                // Catalog Visibility.
                //   if ( isset( $request['catalog_visibility'] ) ) {
                // 	$product->set_catalog_visibility( $request['catalog_visibility'] );
                //   }
                // Check for featured/gallery images, upload it and set it.
                //   if ( isset( $request['images'] ) ) {
                // 	$product = $this->set_product_images( $product, $request['images'] );
                //   }
                // Sales and prices.
                if (in_array($request['type'], array(
                    'variable',
                    'grouped'
                ), true)) {
                    $product->set_regular_price('');
                    $product->set_sale_price('');
                    $product->set_date_on_sale_to('');
                    $product->set_date_on_sale_from('');
                    $product->set_price('');
                } else {
                    // Regular Price.
                    if (isset($regular_price)) {
                        $product->set_regular_price($regular_price);
                    }
                    // Sale Price.
                    if (isset($sale_price) && !empty($sale_price)) {
                        $product->set_sale_price($sale_price);
                    }
                    if (isset($date_on_sale_from)) {
                        $product->set_date_on_sale_from($date_on_sale_from);
                    }
                    if (isset($date_on_sale_from_gmt)) {
                        $product->set_date_on_sale_from($date_on_sale_from_gmt ? strtotime($date_on_sale_from_gmt) : null);
                    }

                    if (isset($date_on_sale_to)) {
                        $product->set_date_on_sale_to($date_on_sale_to);
                    }

                    if (isset($date_on_sale_to_gmt)) {
                        $product->set_date_on_sale_to($date_on_sale_to_gmt ? strtotime($date_on_sale_to_gmt) : null);
                    }

                }

                // Description
                if (isset($description)) {
                    $product->set_description($description);
                }
                if (isset($short_description)) {
                    $product->set_description($short_description);
                }

                // Stock status.
                if (isset($in_stock) && is_bool($in_stock)) {
                    $stock_status = true === $in_stock ? 'instock' : 'outofstock';
                } else {
                    $stock_status = $product->get_stock_status();
                }

                // Stock data.
                if ('yes' === get_option('woocommerce_manage_stock')) {
                    // Manage stock.
                    if (isset($manage_stock)) {
                        $product->set_manage_stock($manage_stock);
                    }

                    // Backorders.
                    if (isset($backorders)) {
                        $product->set_backorders($backorders);
                    }

                    if ($request['type'] == 'grouped') {
                        $product->set_manage_stock('no');
                        $product->set_backorders('no');
                        $product->set_stock_quantity('');
                        $product->set_stock_status($stock_status);
                    } elseif ($request['type'] == 'external') {
                        $product->set_manage_stock('no');
                        $product->set_backorders('no');
                        $product->set_stock_quantity('');
                        $product->set_stock_status('instock');
                    } elseif ($product->get_manage_stock()) {
                        // Stock status is always determined by children so sync later.
                        if (!$product->is_type('variable')) {
                            $product->set_stock_status($stock_status);
                        }

                        // Stock quantity.
                        if (isset($stock_quantity)) {
                            $product->set_stock_quantity(wc_stock_amount($stock_quantity));
                        } elseif (isset($inventory_delta)) {
                            $stock_quantity = wc_stock_amount($product->get_stock_quantity());
                            $stock_quantity += wc_stock_amount($inventory_delta);
                            $product->set_stock_quantity(wc_stock_amount($stock_quantity));
                        }
                    } else {
                        // Don't manage stock.
                        $product->set_manage_stock('no');
                        $product->set_stock_quantity('');
                        $product->set_stock_status($stock_status);
                    }
                } elseif ($request['type'] != 'variable') {
                    $product->set_stock_status($stock_status);
                }

                //Assign categories
                if (isset($request['categories'])) {
                    $categories = array_filter(explode(',', $categories));
                    if (!empty($categories)) {
                        $categoryArray = array();
                        foreach ($categories as $index) {
                            $categoryArray[] = absint($index);
                        }
                        $product->set_category_ids($categoryArray);
                    }
                }

                //Description
                $product->set_short_description($short_description);
                $product->set_description($description);
                $attribute_json = json_decode($productAttributes, true);
                $pro_attributes = array();
                $is_variable = false;
                foreach ($attribute_json as $key => $value) {
                    if ($value['isActive']) {
                        $attribute_name = strtolower($value['slug']);
                        if ($value['default']) {
                            $attribute_name = strtolower('pa_' . $value['slug']);
                        }
                        $attribute_id = wc_attribute_taxonomy_id_by_name($attribute_name);
                        $attribute = new WC_Product_Attribute();
                        $attribute->set_id($attribute_id);
                        $attribute->set_name(wc_clean($attribute_name));
                        $options = $value['options'];
                        $attribute->set_options($options);
                        $attribute->set_visible($value['visible']);
                        $attribute->set_variation($value['variation']);
                        if ($value['variation'] && !$is_variable) {
                            $is_variable = true;
                        }
                        $pro_attributes[] = $attribute;
                    }
                }

                $product->set_props(array(
                    'attributes' => $pro_attributes
                ));
                if (is_wp_error($product)) {
                    return $this->sendError("request_failed", "Bad data", 400);
                }

                $product->save();
                $variable_ids = array();
                if ($is_variable) {

                    $variations_arr = json_decode($variations, true);
                    foreach ($variations_arr as $variation) {
                        if ($variation['variation_id'] != -1) {
                            foreach ($variation['attributes'] as $key => $value) {
                                $variationAttrArr[$key] = strtolower(strval($value));
                            }
                            $variationProduct = new WC_Product_Variation($variation['variation_id']);
                            $variationProduct->set_regular_price($variation['display_regular_price']);
                            $variationProduct->set_sale_price($variation['display_price']);
                            $variationProduct->set_stock_quantity($variation['max_qty']);
                            $variationProduct->set_attributes($variationAttrArr);
                            $variationProduct->set_manage_stock(boolval($variation['manage_stock']));
                            $variationProduct->set_status($variation['variation_is_active'] ? 'publish' : 'private');
                            $variationProduct->save();
                        } else {
                            // Creating the product variation
                            $variation_post = array(
                                'post_title' => $product->get_title(),
                                'post_name' => 'product-' . $product->get_id() . '-variation',
                                'post_status' => 'publish',
                                'post_parent' => $product->get_id(),
                                'post_type' => 'product_variation',
                                'guid' => $product->get_permalink()
                            );
                            $variation_id = wp_insert_post($variation_post);

                            foreach ($variation['attributes'] as $key => $value) {
                                $variationAttrArr[$key] = strtolower(strval($value));
                            }
                            $variationProduct = new WC_Product_Variation($variation_id);
                            $variationProduct->set_regular_price($variation['display_regular_price']);
                            $variationProduct->set_sale_price($variation['display_price']);
                            $variationProduct->set_stock_quantity($variation['max_qty']);
                            $variationProduct->set_attributes($variationAttrArr);
                            $variationProduct->set_manage_stock(boolval($variation['manage_stock']));
                            $variationProduct->set_status($variation['variation_is_active'] ? 'publish' : 'private');
                            $variationProduct->save();
                        }
                        $variable_ids[] = $variationProduct->get_id();
                    }
                }

                wp_update_post(array(
                    'ID' => $product->get_id(),
                    'post_author' => $user_id
                ));
                //print_r($product);
                $image_arr = array();
                $p = $product->get_data();
                foreach (array_filter($p['gallery_image_ids']) as $img) {
                    $image = wp_get_attachment_image_src($img, 'full');

                    if (!is_null($image[0])) {
                        $image_arr[] = $image[0];
                    }
                }
                $p['description'] = strip_tags($p['description']);
                $p['short_description'] = strip_tags($p['short_description']);
                $p['images'] = $image_arr;
                $p['variable_id'] = $variable_ids;
                $image = wp_get_attachment_image_src($p['image_id'], 'full');
                if (!is_null($image[0])) {
                    $p['featured_image'] = $image[0];
                }
                $p['type'] = $request['type'];
                $p['on_sale'] = $product->is_on_sale();
                if ($request['type'] == 'variable') {
                    $query = ['post_parent' => $product->get_id(), 'post_status' => ['publish', 'private'], 'post_type' => ['product_variation'], 'posts_per_page' => -1,];

                    $wc_query = new WP_Query($query);
                    while ($wc_query->have_posts()) {
                        $wc_query->next_post();
                        $result[] = $wc_query->post;
                    }

                    foreach ($result as $variation) {
                        $p_varation = new WC_Product_Variation($variation->ID);
                        $dataVariation = array();
                        $dataVariation['variation_id'] = $p_varation->get_id();
                        $dataVariation['max_qty'] = $p_varation->get_stock_quantity();
                        $dataVariation['variation_is_active'] = $p_varation->get_status() == 'publish';
                        $dataVariation['display_price'] = $p_varation->get_sale_price();
                        $dataVariation['display_regular_price'] = $p_varation->get_regular_price();
                        $dataVariation['attributes'] = $p_varation->get_attributes();
                        $dataVariation['manage_stock'] = $p_varation->get_manage_stock();
                        $p['variable_products'][] = $dataVariation;
                    }
                }
                return new WP_REST_Response(array(
                    'status' => 'success',
                    'response' => $p,
                ), 200);
            }
        } else {
            return $this->sendError("invalid_role", "You must be seller to create product", 401);
        }
    }

/// UPDATE ///
    public function vendor_admin_update_product($request, $user_id)
    {
        $id = isset($request['id']) ? $request['id'] : 0;
        if (isset($id) && is_numeric($id)) {
            $product = $this->get_product_item($id);
        } else {
            return $this->sendError("request_failed", "Invalid data", 400);
        }

        $user = get_userdata($user_id);
        $isSeller = in_array("editor", $user->roles) || in_array("administrator", $user->roles);

        if (!$isSeller) {
            return $this->sendError("invalid_role", "You must be seller to update product", 401);
        }

        $name = sanitize_text_field($request["name"]);
        $description = sanitize_text_field($request["description"]);
        $short_description = sanitize_text_field($request["short_description"]);
        $featured_image = sanitize_text_field($request['featuredImage']);
        $product_images = sanitize_text_field($request['images']);
        $type = sanitize_text_field($request['type']);
        $tags = sanitize_text_field($request['tags']);
        $featured = sanitize_text_field($request['featured']);
        $regular_price = sanitize_text_field($request['regular_price']);
        $sale_price = sanitize_text_field($request['sale_price']);
        $date_on_sale_from = sanitize_text_field($request['date_on_sale_from']);
        $date_on_sale_from_gmt = sanitize_text_field($request['date_on_sale_from_gmt']);
        $date_on_sale_to = sanitize_text_field($request['date_on_sale_to']);
        $date_on_sale_to_gmt = sanitize_text_field($request['date_on_sale_to_gmt']);
        $in_stock = sanitize_text_field($request['in_stock']);
        $stock_quantity = sanitize_text_field($request['stock_quantity']);
        $manage_stock  = sanitize_text_field($request['manage_stock']);
        $backorders = sanitize_text_field($request['backorders']);
        $categories = sanitize_text_field($request['categories']);
        $productAttributes = sanitize_text_field($request['productAttributes']);
        $variations = sanitize_text_field($request['variations']);      
        $inventory_delta = sanitize_text_field($request['inventory_delta']);     
        $status = sanitize_text_field($request['status']);     
        $count = 1;


        if ($product->get_type() != $type) {
            // Get the correct product classname from the new product type
            $product_classname = WC_Product_Factory::get_product_classname($product->get_id(), $type);

            // Get the new product object from the correct classname
            $product = new $product_classname($product->get_id());
            $product->save();
        }

        if (isset($tags)) {
            $tags = array_filter(explode(',', $tags));
            wp_set_object_terms($product->get_id(), $tags, 'product_tag');
        }

        if (isset($featured_image)) {
            if (!empty($featured_image)) {
                if ($this->http_check($featured_image)) {
                    $featured_image_id = $this->find_image_id($featured_image);
                    $product->set_image_id($featured_image_id);
                } else {
                    $featured_image_id = upload_image_from_mobile($featured_image, $count, $user_id);
                    $product->set_image_id($featured_image_id);
                    $count = $count + 1;
                }
            } else {
                $product->set_image_id('');
            }

        }

        if (isset($product_images)) {
            $product_images_array = array_filter(explode(',', $product_images));
            $img_array = array();

            foreach ($product_images_array as $p_img) {
                if (!empty($p_img)) {
                    if ($this->http_check($p_img)) {
                        $img_id = $this->find_image_id($p_img);
                        array_push($img_array, $img_id);
                    } else {
                        $img_id = upload_image_from_mobile($p_img, $count, $user_id);
                        array_push($img_array, $img_id);
                        $count = $count + 1;
                    }
                }
            }
            $product->set_gallery_image_ids($img_array);
        }

        /// Set attributes to product
        if (isset($product) && !is_wp_error($product)) {
            if (isset($name)) {
                $product->set_name(wp_filter_post_kses($name));
            }
            // Featured Product.
            if (isset($featured)) {
                $product->set_featured($featured);
            }
            // SKU.
            if (isset($request['sku'])) {
                $product->set_sku(wc_clean($request['sku']));
            }

            // Sales and prices.
            $product->set_status($status);

            if (in_array($product->get_type(), array(
                'variable',
                'grouped'
            ), true)) {
                $product->set_regular_price('');
                $product->set_sale_price('');
                $product->set_date_on_sale_to('');
                $product->set_date_on_sale_from('');
                $product->set_price('');
            } else {
                // Regular Price.
                if (isset($regular_price)) {
                    $product->set_regular_price($regular_price);
                }
                // Sale Price.
                if (isset($sale_price) && !empty($sale_price)) {
                    $product->set_sale_price($sale_price);
                }
                if (isset($date_on_sale_from)) {
                    $product->set_date_on_sale_from($date_on_sale_from);
                }
                if (isset($date_on_sale_from_gmt)) {
                    $product->set_date_on_sale_from($date_on_sale_from_gmt ? strtotime($date_on_sale_from_gmt) : null);
                }

                if (isset($date_on_sale_to)) {
                    $product->set_date_on_sale_to($date_on_sale_to);
                }

                if (isset($date_on_sale_to_gmt)) {
                    $product->set_date_on_sale_to($date_on_sale_to_gmt ? strtotime($date_on_sale_to_gmt) : null);
                }

            }

            // Description
            if (isset($description)) {

                $product->set_description(strip_tags($description));
            }
            if (isset($short_description)) {
                $product->set_short_description(strip_tags($short_description));
            }

            // Stock status.
            if (isset($in_stock)) {
                $stock_status = true === $in_stock ? 'instock' : 'outofstock';
            } else {
                $stock_status = $product->get_stock_status();
            }

            // Stock data.
            if ('yes' === get_option('woocommerce_manage_stock')) {
                // Manage stock.
                if (isset($manage_stock)) {
                    $product->set_manage_stock($manage_stock);
                }

                // Backorders.
                if (isset($backorders)) {
                    $product->set_backorders($backorders);
                }


                if ($product->is_type('grouped')) {
                    $product->set_manage_stock('no');
                    $product->set_backorders('no');
                    $product->set_stock_quantity('');
                    $product->set_stock_status($stock_status);
                } elseif ($product->is_type('external')) {
                    $product->set_manage_stock('no');
                    $product->set_backorders('no');
                    $product->set_stock_quantity('');
                    $product->set_stock_status('instock');
                } elseif ($product->get_manage_stock()) {
                    // Stock status is always determined by children so sync later.
                    if (!$product->is_type('variable')) {
                        $product->set_stock_status($stock_status);
                    }

                    // Stock quantity.
                    if (isset($stock_quantity)) {
                        $product->set_stock_quantity(wc_stock_amount($stock_quantity));
                    } elseif (isset($request['inventory_delta'])) {
                        $stock_quantity = wc_stock_amount($product->get_stock_quantity());
                        $stock_quantity += wc_stock_amount($inventory_delta);
                        $product->set_stock_quantity(wc_stock_amount($stock_quantity));
                    }
                } else {
                    // Don't manage stock.
                    $product->set_manage_stock('no');
                    $product->set_stock_quantity('');
                    $product->set_stock_status($stock_status);
                }
            } elseif (!$product->is_type('variable')) {
                $product->set_stock_status($stock_status);
            }

            //Assign categories
            if (isset($categories)) {
                $categories = array_filter(explode(',', $categories));
                if (!empty($categories)) {
                    $categoryArray = array();
                    foreach ($categories as $index) {
                        $categoryArray[] = absint($index);
                    }
                    $product->set_category_ids($categoryArray);
                } else {
                    $product->set_category_ids(array());
                }
            }

            //Description
            $product->set_short_description($short_description);
            $product->set_description($description);
            if (is_wp_error($product)) {
                return $this->sendError("request_failed", "Bad data", 400);
            }

            $attribute_json = json_decode($productAttributes, true);
            $pro_attributes = array();
            foreach ($attribute_json as $key => $value) {
                if ($value['isActive']) {
                    $attribute_name = strtolower($value['slug']);
                    if ($value['default']) {
                        $attribute_name = strtolower('pa_' . $value['slug']);
                    }
                    $attribute_id = wc_attribute_taxonomy_id_by_name($attribute_name);
                    $attribute = new WC_Product_Attribute();
                    $attribute->set_id($attribute_id);
                    $attribute->set_name(wc_clean($attribute_name));
                    $options = $value['options'];
                    $attribute->set_options($options);
                    $attribute->set_visible($value['visible']);
                    $attribute->set_variation($value['variation']);
                    $pro_attributes[] = $attribute;
                }
            }

            $product->set_props(array(
                'attributes' => $pro_attributes
            ));
            $product->save();

            if ($product->is_type('variable')) {

                $variations_arr = json_decode($variations, true);
                foreach ($variations_arr as $variation) {
                    if ($variation['variation_id'] != -1) {
                        foreach ($variation['attributes'] as $key => $value) {
                            $variationAttrArr[$key] = strtolower(strval($value));
                        }
                        $variationProduct = new WC_Product_Variation($variation['variation_id']);
                        $variationProduct->set_regular_price($variation['display_regular_price']);
                        $variationProduct->set_sale_price($variation['display_price']);
                        $variationProduct->set_stock_quantity($variation['max_qty']);
                        $variationProduct->set_attributes($variationAttrArr);
                        $variationProduct->set_manage_stock(boolval($variation['manage_stock']));
                        $variationProduct->set_status($variation['variation_is_active'] ? 'publish' : 'private');
                        $variationProduct->save();
                    } else {
                        // Creating the product variation
                        $variation_post = array(
                            'post_title' => $product->get_title(),
                            'post_name' => 'product-' . $product->get_id() . '-variation',
                            'post_status' => 'publish',
                            'post_parent' => $product->get_id(),
                            'post_type' => 'product_variation',
                            'guid' => $product->get_permalink()
                        );
                        $variation_id = wp_insert_post($variation_post);

                        foreach ($variation['attributes'] as $key => $value) {
                            $variationAttrArr[$key] = strtolower(strval($value));
                        }
                        $variationProduct = new WC_Product_Variation($variation_id);
                        $variationProduct->set_regular_price($variation['display_regular_price']);
                        $variationProduct->set_sale_price($variation['display_price']);
                        $variationProduct->set_stock_quantity($variation['max_qty']);
                        $variationProduct->set_attributes($variationAttrArr);
                        $variationProduct->set_manage_stock(boolval($variation['manage_stock']));
                        $variationProduct->set_status($variation['variation_is_active'] ? 'publish' : 'private');
                        $variationProduct->save();
                    }
                }
            }

            wp_update_post(array(
                'ID' => $product->get_id(),
                'post_author' => $user_id
            ));
            //print_r($product);
            $image_arr = array();
            $p = $product->get_data();

            foreach (array_filter($p['gallery_image_ids']) as $img) {
                $image = wp_get_attachment_image_src($img, 'full');

                if (!is_null($image[0])) {
                    $image_arr[] = $image[0];
                }
            }
            $p['description'] = strip_tags($p['description']);
            $p['short_description'] = strip_tags($p['short_description']);
            $p['images'] = $image_arr;
            $image = wp_get_attachment_image_src($p['image_id'], 'full');
            if (!is_null($image[0])) {
                $p['featured_image'] = $image[0];
            }
            $p['type'] = $product->get_type();
            $p['on_sale'] = $product->is_on_sale();
            $attributes = array();
            foreach ($product->get_attributes() as $attribute) {
                $attributes[] = array(
                    'id' => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name($attribute['name']) : 0,
                    'name' => $this->get_attribute_taxonomy_name($attribute['name'], $product),
                    'position' => (int)$attribute['position'],
                    'visible' => (bool)$attribute['is_visible'],
                    'variation' => (bool)$attribute['is_variation'],
                    'options' => $this->get_attribute_options($product->get_id(), $attribute),
                    'default' => 0 === strpos($attribute['name'], 'pa_'),
                );
            }

            $p['attributesData'] = $attributes;
            if ($product->is_type('variable')) {
                $query = ['post_parent' => $product->get_id(), 'post_status' => ['publish', 'private'], 'post_type' => ['product_variation'], 'posts_per_page' => -1,];

                $wc_query = new WP_Query($query);
                while ($wc_query->have_posts()) {
                    $wc_query->next_post();
                    $result[] = $wc_query->post;
                }

                foreach ($result as $variation) {
                    $p_varation = new WC_Product_Variation($variation->ID);
                    $dataVariation = array();
                    $dataVariation['variation_id'] = $p_varation->get_id();
                    $dataVariation['max_qty'] = $p_varation->get_stock_quantity();
                    $dataVariation['variation_is_active'] = $p_varation->get_status() == 'publish';
                    $dataVariation['display_price'] = $p_varation->get_sale_price();
                    $dataVariation['display_regular_price'] = $p_varation->get_regular_price();
                    $dataVariation['attributes'] = $p_varation->get_attributes();
                    $dataVariation['manage_stock'] = $p_varation->get_manage_stock();
                    $p['variable_products'][] = $dataVariation;
                }
            }
            return new WP_REST_Response(array(
                'status' => 'success',
                'response' => $p,
            ), 200);
        }
    }

/// DELETE ///
    public function vendor_admin_delete_product($request, $user_id)
    {
        $request = sanitize_text_field($request);

        $user = get_userdata($user_id);
        $isSeller = in_array("editor", $user->roles) || in_array("administrator", $user->roles);
        if (!$isSeller) {
            return $this->sendError("invalid_role", "You must be seller to delete product", 401);
        }
        /// Validate product ID
        $id = isset($request['id']) ? $request['id'] : 0;
        if (isset($request['id']) && is_numeric($id)) {
            $product = $this->get_product_item($id);
        } else {
            return $this->sendError("request_failed", "Invalid data", 400);
        }
        wp_delete_post($product->get_id());
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => '',
        ), 200);
    }
}

