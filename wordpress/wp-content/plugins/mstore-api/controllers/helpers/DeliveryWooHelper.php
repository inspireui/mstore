<?php

class DeliveryWooHelper
{
    public function sendError($code, $message, $statusCode)
    {
        return new WP_Error($code, $message, array(
            'status' => $statusCode
        ));
    }

    protected function upload_image_from_mobile($image, $count, $user_id)
    {
        require_once(ABSPATH . '/wp-load.php');
        require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
        require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
        $imgdata = $image;
        $imgdata = trim($imgdata);
        $imgdata = str_replace('data:image/png;base64,', '', $imgdata);
        $imgdata = str_replace('data:image/jpg;base64,', '', $imgdata);
        $imgdata = str_replace('data:image/jpeg;base64,', '', $imgdata);
        $imgdata = str_replace('data:image/gif;base64,', '', $imgdata);
        $imgdata = str_replace(' ', '+', $imgdata);
        $imgdata = base64_decode($imgdata);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
        $type_file = explode('/', $mime_type);
        $avatar = time() . '_' . $count . '.' . $type_file[1];

        $uploaddir = wp_upload_dir();
        $myDirPath = $uploaddir["path"];
        $myDirUrl = $uploaddir["url"];

        file_put_contents($uploaddir["path"] . '/' . $avatar, $imgdata);

        $filename = $myDirUrl . '/' . basename($avatar);
        $wp_filetype = wp_check_filetype(basename($filename), null);
        $uploadfile = $uploaddir["path"] . '/' . basename($filename);

        $attachment = array(
            "post_mime_type" => $wp_filetype["type"],
            "post_title" => preg_replace("/\.[^.]+$/", "", basename($filename)),
            "post_content" => "",
            "post_author" => $user_id,
            "post_status" => "inherit",
            'guid' => $myDirUrl . '/' . basename($filename),
        );

        $attachment_id = wp_insert_attachment($attachment, $uploadfile);
        $attach_data = apply_filters('wp_generate_attachment_metadata', $attachment, $attachment_id, 'create');
        // $attach_data = wp_generate_attachment_metadata($attachment_id, $uploadfile);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        return $attachment_id;
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


    /// GET FUNCTIONS
    public function get_delivery_profile($user_id)
    {
        $data['first_name'] = get_user_meta($user_id, 'billing_first_name', true);
        $data['last_name'] = get_user_meta($user_id, 'billing_last_name', true);
        $data['phone'] = get_user_meta($user_id, 'billing_phone', true);


        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $data,
        ), 200);
    }

    public function update_vendor_profile($request, $user_id)
    {
        $data = json_decode($request, true);
        $vendor_data = get_user_meta($user_id, 'wcfmmp_profile_settings', true);
        if (is_string($vendor_data)) {
            $vendor_data = array();
        }
    }


    public function get_delivery_stat($user_id)
    {
        $results = array();

        if (is_plugin_active('delivery-drivers-for-woocommerce/delivery-drivers-for-woocommerce.php')) {
            global $wpdb;
            $table_1 = "{$wpdb->prefix}posts";
            $table_2 = "{$wpdb->prefix}postmeta";
            $sql = "SELECT ID FROM {$table_1} INNER JOIN {$table_2} ON {$table_1}.ID = {$table_2}.post_id";
            $sql .= " WHERE `{$table_2}`.`meta_key` = 'ddwc_driver_id' AND `{$table_2}`.`meta_value` = {$user_id}";
            $total = count($wpdb->get_results($sql));
            $pending_sql = $sql . " AND (`{$table_1}`.`post_status` = 'wc-driver-assigned' OR `{$table_1}`.`post_status` = 'wc-out-for-delivery')";
            $delivered_sql = $sql . " AND `{$table_1}`.`post_status` = 'wc-completed'";

            $pending_count = count($wpdb->get_results($pending_sql));
            $delivered_count = count($wpdb->get_results($delivered_sql));

            $results = array(
                'delivered' => $delivered_count,
                'pending' => $pending_count,
                'total' => $total,
            );

        }

        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $results,
        ), 200);
    }

    public function get_delivery_order($user_id, $request)
    {
        $api = new WC_REST_Orders_V1_Controller();

        $order_id = $request['id'];
        if (is_plugin_active('delivery-drivers-for-woocommerce/delivery-drivers-for-woocommerce.php')) {
            $order = wc_get_order($order_id);
        }
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $order,
        ), 200);
    }


    public function get_delivery_stores($user_id, $request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "wcfm_delivery_orders";
        $sql = "SELECT $table_name.`vendor_id` FROM `{$table_name}`";
        $sql .= " WHERE 1=1";
        $sql .= " AND delivery_boy = {$user_id}";
        $sql .= " AND is_trashed = 0";
        $sql .= " AND delivery_status = 'pending'";
        $sql .= " GROUP BY $table_name.`vendor_id`";
        $items = $wpdb->get_results($sql);

        $vendor = new FlutterWCFMHelper();
        $stores = array();
        foreach ($items as $item) {
            $vendor_data = $vendor->flutter_get_wcfm_stores_by_id($item->vendor_id);
            $stores[] = $vendor_data->data;
        }
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $stores,
        ), 200);

    }

    public function get_delivery_orders($user_id, $request)
    {
        $api = new WC_REST_Orders_V1_Controller();
        $results = [];
        if (is_plugin_active('delivery-drivers-for-woocommerce/delivery-drivers-for-woocommerce.php')) {
            $page = 1;
            $per_page = 10;
            if (isset($request['page'])) {
                $page = $request['page'];
            }
            if (isset($request['per_page'])) {
                $per_page = $request['per_page'];
            }
            $page = ($page - 1) * $per_page;
            global $wpdb;

            $table_1 = "{$wpdb->prefix}posts";
            $table_2 = "{$wpdb->prefix}postmeta";
            $sql = "SELECT ID FROM {$table_1} INNER JOIN {$table_2} ON {$table_1}.ID = {$table_2}.post_id";
            $sql .= " WHERE `{$table_2}`.`meta_key` = 'ddwc_driver_id' AND `{$table_2}`.`meta_value` = {$user_id}";
            if (isset($request['status']) && !empty($request['status'])) {
                $status = $request['status'];
                if ($status == 'pending') {
                    $sql .= " AND (`{$table_1}`.`post_status` = 'wc-driver-assigned' OR `{$table_1}`.`post_status` = 'wc-out-for-delivery')";
                }
                if ($status == 'delivered') {
                    $sql .= " AND `{$table_1}`.`post_status` = 'wc-completed'";
                }
            } else {
                $sql .= " AND (`{$table_1}`.`post_status` = 'wc-driver-assigned' OR `{$table_1}`.`post_status` = 'wc-out-for-delivery' OR `{$table_1}`.`post_status` = 'wc-completed')";
            }
            if (isset($request['search'])) {
                $order_search = $request['search'];
                $sql .= " AND $table_1.`ID` LIKE '%{$order_search}%'";
            }
            $sql .= " GROUP BY $table_1.`ID` ORDER BY $table_1.`ID` DESC LIMIT $per_page OFFSET $page";

            $items = $wpdb->get_results($sql);
            foreach ($items as $item) {
                $order = wc_get_order($item);
                if (is_bool($order)) {
                    continue;
                }
                $response = $api->prepare_item_for_response($order, $request);
                $order = $response->get_data();
                $count = count($order['line_items']);
                $order['product_count'] = $count;
                for ($i = 0; $i < $count; $i++) {
                    $product_id = absint($order['line_items'][$i]['product_id']);
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id));
                    if (!is_null($image[0])) {
                        $order['line_items'][$i]['featured_image'] = $image[0];
                    }
                }
                $order['delivery_status'] = 'delivered';
                if ($order['status'] != 'completed') {
                    $order['delivery_status'] = 'pending';
                }
                $results[] = $order;
            }
        }
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $results,
        ), 200);
    }


    function get_notification($request, $user_id)
    {
        global $WCFM, $wpdb;
        $table_name = $wpdb->prefix . 'delivery_woo_notification';
        $sql = "CREATE TABLE " . $table_name . "(
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                message text NOT NULL,
                order_id text NOT NULL,
                delivery_boy text NOT NULL,
                created datetime NOT NULL,
                UNIQUE KEY id (id)
                );";
        maybe_create_table($table_name, $sql);
        $messages = array();
        if (isset($request['per_page']) && $request['per_page']) {
            $limit = absint($request['per_page']);
            $offset = absint($request['page']);
            $offset = ($offset - 1) * $limit;
            $sql = "SELECT * FROM $table_name WHERE `{$table_name}`.`delivery_boy` = $user_id";
            $sql .= " ORDER BY `{$table_name}`.`id` DESC";
            $sql .= " LIMIT $limit";
            $sql .= " OFFSET $offset";
            $messages = $wpdb->get_results($sql);
        }
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $messages,
        ), 200);
    }


    function update_delivery_profile($request, $user_id)
    {
        $is_pw_correct = true;
        $pass = $request['password'];
        $new_pass = $request['new_password'];
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $phone = $request['phone'];
        $data = array('ID' => $user_id);
        if (isset($params->display_name)) {
            $user_update['first_name'] = $params->first_name;
        }
        if (isset($params->display_name)) {
            $user_update['last_name'] = $params->last_name;
        }

        if (isset($first_name)) {
            $data['first_name'] = $first_name;
            update_user_meta($user_id, 'billing_first_name', $first_name, '');
            wp_update_user(array('ID' => $user_id, 'first_name' => $first_name));
        }
        if (isset($last_name)) {
            $data['last_name'] = $last_name;
            update_user_meta($user_id, 'billing_last_name', $last_name, '');
            wp_update_user(array('ID' => $user_id, 'last_name' => $last_name));
        }
        if (isset($phone)) {
            update_user_meta($user_id, 'billing_phone', $phone, '');
        }
        if (!empty($data)) {
            wp_update_user($data, $user_id);
        }
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => 1,
        ), 200);
    }


    function update_delivery_order($order_id)
    {
        $order = wc_update_order(array("order_id" => $order_id, "status" => "wc-completed"));
        if (is_wp_error($order)) {
            return new WP_REST_Response(array(
                'status' => 'success',
                'response' => -1,
                'message' => $order,
            ), 200);
        }

        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => 1,
        ), 200);


    }
}
    
