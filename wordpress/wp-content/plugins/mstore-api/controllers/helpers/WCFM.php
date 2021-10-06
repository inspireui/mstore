<?php

class FlutterWCFMHelper
{
    public function flutter_get_wcfm_stores($request)
    {
        global $WCFM, $WCFMmp, $wpdb, $wcfmmp_radius_lat, $wcfmmp_radius_lng, $wcfmmp_radius_range;

        $search_term = $request->get_param('search') ? sanitize_text_field($request->get_param('search')) : '';
        $search_category = $request->get_param('wcfmmp_store_category') ? sanitize_text_field($request->get_param('wcfmmp_store_category')) : '';
        $paged = $request->get_param('page') ? absint($request->get_param('page')) : 1;
        $per_page = $request->get_param('per_page') ? absint($request->get_param('per_page')) : 10;
        $includes = $request->get_param('includes') ? sanitize_text_field($request->get_param('includes')) : '';
        $excludes = $request->get_param('excludes') ? sanitize_text_field($request->get_param('excludes')) : '';
        $has_product = $request->get_param('has_product') ? sanitize_text_field($request->get_param('has_product')) : '';
        $units = $request->get_param('units') ? sanitize_text_field($request->get_param('units')) : 'metric';

        $search_data = array();

        $length = absint($per_page);
        $offset = ($paged - 1) * $length;

        $search_data['excludes'] = $excludes;

        if ($includes) {
            $includes = explode(",", $includes);
        } else {
            $includes = array();
        }

        $wcfmmp_radius_lat = $request->get_param('wcfmmp_radius_lat');
        $wcfmmp_radius_lng = $request->get_param('wcfmmp_radius_lng');
        $wcfmmp_radius_range = $request->get_param('wcfmmp_radius_range');

        if ($wcfmmp_radius_lat && $wcfmmp_radius_lng && $wcfmmp_radius_range) {
            $search_data['wcfmmp_radius_lat'] = $wcfmmp_radius_lat;
            $search_data['wcfmmp_radius_lng'] = $wcfmmp_radius_lng;
            $search_data['wcfmmp_radius_range'] = $wcfmmp_radius_range;
        }

        $stores = $WCFMmp->wcfmmp_vendor->wcfmmp_search_vendor_list(true, $offset, $length, $search_term, $search_category, $search_data, $has_product, $includes);

        $response = array();
        $index = 0;
        foreach ($stores as $wcfm_vendors_id => $wcfm_vendors_name) {
            $response[$index] = $this->get_formatted_item_data($wcfm_vendors_id, array(), $wcfm_vendors_name, null, null);
            $index++;
        }
        return apply_filters("wcfmapi_rest_prepare_store_vendors_objects", $response, $request);
    }

    public function flutter_get_wcfm_stores_by_id($wcfm_vendors_id)
    {
        $wcfm_vendors_json_arr = array();
        $response = $this->get_formatted_item_data($wcfm_vendors_id, $wcfm_vendors_json_arr, null, null, null);
        return rest_ensure_response($response);
    }

    public function get_formatted_item_data($wcfm_vendors_id, $wcfm_vendors_json_arr, $wcfm_vendors_name, $filter_date_form, $filter_date_to)
    {
        global $WCFM;
        if (is_plugin_active('wcfm-marketplace-rest-api/wcfm-marketplace-rest-api.php')) {
            $store_vendorController = new WCFM_REST_Store_Vendors_Controller();
            $vendorData = $store_vendorController->get_formatted_item_data($wcfm_vendors_id);
            if (!is_wp_error($vendorData) && is_array($vendorData)) {
                $wcfm_vendors_json_arr = $vendorData;
            }
        }

        $admin_fee_mode = apply_filters('wcfm_is_admin_fee_mode', false);
        $price_decimal = get_option('woocommerce_price_num_decimals', 2);
        $report_for = 'month';

        $wcfm_vendors_json_arr['vendor_id'] = $wcfm_vendors_id;
        $wcfm_vendors_json_arr['vendor_display_name'] = $wcfm_vendors_name;
        $wcfm_vendors_json_arr['vendor_shop_name'] = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor($wcfm_vendors_id);

        $store_user = wcfmmp_get_store(absint($wcfm_vendors_id));
        $email = $store_user->get_email();
        $phone = $store_user->get_phone();
        $address = $store_user->get_address_string();

        if ($email) {
            $wcfm_vendors_json_arr['vendor_email'] = $email;
        }

        if ($address) {
            $wcfm_vendors_json_arr['vendor_address'] = $address;
        }

        if ($phone) {
            $wcfm_vendors_json_arr['vendor_phone'] = $phone;
        }

        $wcfm_vendors_json_arr['vendor_shop_name'] = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor($wcfm_vendors_id);
        $disable_vendor = get_user_meta($wcfm_vendors_id, '_disable_vendor', true);
        $is_store_offline = get_user_meta($wcfm_vendors_id, '_wcfm_store_offline', true);
        $wcfm_vendors_json_arr['disable_vendor'] = $disable_vendor == "1";
        $wcfm_vendors_json_arr['is_store_offline'] = $is_store_offline;
        if (apply_filters('wcfm_is_allow_email_verification', true)) {
            $email_verified = false;
            $vendor_user = get_userdata($wcfm_vendors_id);
            $user_email = $vendor_user->user_email;
            $email_verified = get_user_meta($wcfm_vendors_id, '_wcfm_email_verified', true);
            $wcfm_email_verified_for = get_user_meta($wcfm_vendors_id, '_wcfm_email_verified_for', true);
            if ($email_verified && ($user_email != $wcfm_email_verified_for)) $email_verified = false;
            $wcfm_vendors_json_arr['email_verified'] = $email_verified;
        }

        // $wcfm_vendors_json_arr['additional_data'] = apply_filters( 'wcfm_vendors_additonal_data', '&ndash;', $wcfm_vendors_id );
        $vendor_id = $wcfm_vendors_id;
        $vendor_settings = $this->get_vendor_settings_by_id($vendor_id);
        $wcfm_vendors_json_arr["settings"] = $vendor_settings;

        $wcfmvm_registration_custom_fields = get_option('wcfmvm_registration_custom_fields', array());
        $wcfmvm_custom_infos = get_user_meta($vendor_id, 'wcfmvm_custom_infos', true);

        $wcfm_vendors_json_arr['vendor_additional_info'] = array();

        if (!empty($wcfmvm_registration_custom_fields)) {
            foreach ($wcfmvm_registration_custom_fields as $key => $wcfmvm_registration_custom_field) {
                $wcfmvm_registration_custom_field['name'] = sanitize_title($wcfmvm_registration_custom_field['label']);
                if (!empty($wcfmvm_custom_infos)) {
                    if ($wcfmvm_registration_custom_field['type'] == 'checkbox') {
                        $field_value = isset($wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']]) ? $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] : 'no';
                    } elseif ($wcfmvm_registration_custom_field['type'] == 'upload') {
                        $field_name = 'wcfmvm_custom_infos[' . $wcfmvm_registration_custom_field['name'] . ']';
                        $field_id = md5($field_name);
                        $field_value = isset($wcfmvm_custom_infos[$field_id]) ? $wcfmvm_custom_infos[$field_id] : '';
                    } else {
                        $field_value = isset($wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']]) ? $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] : '';
                    }
                }
                if (isset($field_value)) {
                    $wcfm_vendors_json_arr['vendor_additional_info'][$key] = $wcfmvm_registration_custom_field;
                    $wcfm_vendors_json_arr['vendor_additional_info'][$key]['value'] = $field_value;
                }
            }

        } else {
            $wcfm_vendors_json_arr['vendor_additional_info'] = array();
        }

        $wcfm_membership = get_user_meta($wcfm_vendors_id, 'wcfm_membership', true);
        //print_r($wcfm_membership);
        if ($wcfm_membership && function_exists('wcfm_is_valid_membership') && wcfm_is_valid_membership($wcfm_membership)) {
            $wcfm_vendors_json_arr['membership_details']['membership_title'] = get_the_title($wcfm_membership);
            $wcfm_vendors_json_arr['membership_details']['membership_id'] = $wcfm_membership;

            $next_schedule = get_user_meta($wcfm_vendors_id, 'wcfm_membership_next_schedule', true);
            if ($next_schedule) {
                $subscription = (array)get_post_meta($wcfm_membership, 'subscription', true);
                $is_free = isset($subscription['is_free']) ? 'yes' : 'no';
                $subscription_type = isset($subscription['subscription_type']) ? $subscription['subscription_type'] : 'one_time';

                if (($is_free == 'no') && ($subscription_type != 'one_time')) {
                    $wcfm_vendors_json_arr['membership_details']['membership_next_payment'] = date_i18n(wc_date_format(), $next_schedule);
                }

                $member_billing_period = get_user_meta($wcfm_vendors_id, 'wcfm_membership_billing_period', true);
                $member_billing_cycle = get_user_meta($wcfm_vendors_id, 'wcfm_membership_billing_cycle', true);
                if ($member_billing_period && $member_billing_cycle) {
                    $billing_period = isset($subscription['billing_period']) ? $subscription['billing_period'] : '1';
                    $billing_period_count = isset($subscription['billing_period_count']) ? $subscription['billing_period_count'] : '';
                    $billing_period_type = isset($subscription['billing_period_type']) ? $subscription['billing_period_type'] : 'M';
                    $period_options = array('D' => 'days', 'M' => 'months', 'Y' => 'years');

                    if ($billing_period_count) {
                        if ($member_billing_period) $member_billing_period = absint($member_billing_period);
                        else $member_billing_period = absint($billing_period_count);
                        if (!$member_billing_cycle) $member_billing_cycle = 1;
                        $remaining_cycle = ($member_billing_period - $member_billing_cycle);
                        if ($remaining_cycle == 0) {
                            $wcfm_vendors_json_arr['membership_details']['membership_expiry_on'] = date_i18n(wc_date_format(), $next_schedule);
                        } else {
                            $expiry_time = strtotime('+' . $remaining_cycle . ' ' . $period_options[$billing_period_type], $next_schedule);
                            $wcfm_vendors_json_arr['membership_details']['membership_expiry_on'] = date_i18n(wc_date_format(), $expiry_time);
                        }
                    } else {

                        if ($is_free == 'yes') {
                            $wcfm_vendors_json_arr['membership_details']['membership_expiry_on'] = date_i18n(wc_date_format(), $next_schedule);
                        } else {
                            $wcfm_vendors_json_arr['membership_details']['membership_expiry_on'] = __('Never Expire', 'wc-frontend-manager');
                        }
                    }

                } else {
                    $wcfm_vendors_json_arr['membership_details']['membership_expiry_on'] = __('Never Expire', 'wc-frontend-manager');
                }

            }
        }

        $wcfm_vendors_json_arr['shop_url'] = wcfmmp_get_store_url($vendor_id);

        return $wcfm_vendors_json_arr;
    }

    protected function get_vendor_settings_by_id($vendor_id)
    {
        $vendor_settings_data = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);
        if ($vendor_settings_data != "" && isset($vendor_settings_data)) {
            if (isset($vendor_settings_data["gravatar"])) {
                $gravatar_image_url = wp_get_attachment_image_src($vendor_settings_data["gravatar"], 'full');
                if (!empty($gravatar_image_url)) {
                    $vendor_settings_data["gravatar"] = $gravatar_image_url[0];
                }
            }

            if (isset($vendor_settings_data["banner"])) {
                $banner_image_url = wp_get_attachment_image_src($vendor_settings_data["banner"], 'full');
                if (!empty($banner_image_url)) {
                    $vendor_settings_data["banner"] = $banner_image_url[0];
                }
            }

            if (isset($vendor_settings_data["mobile_banner"])) {
                $mobile_banner_image_url = wp_get_attachment_image_src($vendor_settings_data["mobile_banner"], 'full');
                if (!empty($mobile_banner_image_url)) {
                    $vendor_settings_data["mobile_banner"] = $mobile_banner_image_url[0];
                }
            }
        } else {
            $vendor_settings_data = null;
        }

        return $vendor_settings_data;
    }


    public function flutter_get_wcfm_sale_stats($user_id)
    {
        $id = $user_id;
        $price_decimal = get_option('woocommerce_price_num_decimals', 2);

        $sales_stats['gross_sales']['last_month'] = round($this->wcfm_get_gross_sales_by_vendor($id, 'last_month'), $price_decimal);
        $sales_stats['gross_sales']['month'] = round($this->wcfm_get_gross_sales_by_vendor($id, 'month'), $price_decimal);
        $sales_stats['gross_sales']['year'] = round($this->wcfm_get_gross_sales_by_vendor($id, 'year'), $price_decimal);
        $sales_stats['gross_sales']['week_5'] = round($this->wcfm_get_gross_sales_by_vendor($id, '7day'), $price_decimal);
        $sales_stats['gross_sales']['week_4'] = round($this->wcfm_get_gross_sales_by_vendor($id, '14day'), $price_decimal);
        $sales_stats['gross_sales']['week_3'] = round($this->wcfm_get_gross_sales_by_vendor($id, '21day'), $price_decimal);
        $sales_stats['gross_sales']['week_2'] = round($this->wcfm_get_gross_sales_by_vendor($id, '28day'), $price_decimal);
        $sales_stats['gross_sales']['week_1'] = round($this->wcfm_get_gross_sales_by_vendor($id, '35day'), $price_decimal);
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
        $sales_stats['earnings']['week_5'] = round($this->wcfm_get_commission_by_vendor($id, '7day'), $price_decimal);
        $sales_stats['earnings']['week_4'] = round($this->wcfm_get_commission_by_vendor($id, '14day'), $price_decimal);
        $sales_stats['earnings']['week_3'] = round($this->wcfm_get_commission_by_vendor($id, '21day'), $price_decimal);
        $sales_stats['earnings']['week_2'] = round($this->wcfm_get_commission_by_vendor($id, '28day'), $price_decimal);
        $sales_stats['earnings']['week_1'] = round($this->wcfm_get_commission_by_vendor($id, '35day'), $price_decimal);
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

    public function flutter_update_wcfm_order_status($request, $user_id)
    {
        global $WCFM;

        $order_id = absint($request['order_id']);
        $order_status = $request['order_status'];

        $order = wc_get_order($order_id);
        $order->update_status($order_status, '', true);
        $shop_name = get_user_by('ID', $user_id)->display_name;
        if (wcfm_is_vendor()) {
            $shop_name = wcfm_get_vendor_store(absint($user_id));
        }
        $wcfm_messages = sprintf(__('Order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager'), wc_get_order_status_name(str_replace('wc-', '', $order_status)), $shop_name);
        $is_customer_note = apply_filters('wcfm_is_allow_order_update_note_for_customer', '1');

        if (wcfm_is_vendor($user_id)) add_filter('woocommerce_new_order_note_data', array($WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor'), 10, 2);
        $comment_id = $order->add_order_note($wcfm_messages, $is_customer_note);
        if (wcfm_is_vendor($user_id)) {
            add_comment_meta($comment_id, '_vendor_id', $user_id);
        }
        if (wcfm_is_vendor($user_id)) remove_filter('woocommerce_new_order_note_data', array($WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor'), 10, 2);

        $wcfm_messages = sprintf(__('<b>%s</b> order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager'), '#<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>', wc_get_order_status_name(str_replace('wc-', '', $order_status)), $shop_name);
        $WCFM->wcfm_notification->wcfm_send_direct_message(-2, 0, 1, 0, $wcfm_messages, 'status-update');

        do_action('woocommerce_order_edit_status', $order_id, $order_status);
        do_action('wcfm_order_status_updated', $order_id, $order_status);

        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $order->get_data()
        ), 200);
    }

    public function flutter_get_wcfm_reviews($request, $user_id)
    {
        global $WCFM, $wpdb, $WCFMmp;

        $vendor_id = $user_id;

        $length = sanitize_text_field($request['per_page']);
        $offset = sanitize_text_field($request['page']);

        $the_orderby = !empty($request['orderby']) ? sanitize_text_field($request['orderby']) : 'ID';
        $the_order = (!empty($request['order']) && 'asc' === $request['order']) ? 'ASC' : 'DESC';

        $status_filter = '';
        if (isset($request['status_type']) && ($request['status_type'] != '')) {
            $status_filter = sanitize_text_field($request['status_type']);
            if ($status_filter == 'approved') {
                $status_filter = ' AND `approved` = 1';
            } elseif ($status_filter == 'pending') {
                $status_filter = ' AND `approved` = 0';
            }
        }

        $reviews_vendor_filter = " AND `vendor_id` = " . $vendor_id;
        $sql = "SELECT COUNT(ID) from {$wpdb->prefix}wcfm_marketplace_reviews";
        $sql .= " WHERE 1=1";
        $sql .= $reviews_vendor_filter;
        $sql .= $status_filter;

        $wcfm_review_items = $wpdb->get_var($sql);
        if (!$wcfm_review_items) $wcfm_review_items = 0;

        $sql = "SELECT * from {$wpdb->prefix}wcfm_marketplace_reviews";
        $sql .= " WHERE 1=1";
        $sql .= $reviews_vendor_filter;
        $sql .= $status_filter;
        $sql .= " ORDER BY `{$the_orderby}` {$the_order}";
        $sql .= " LIMIT {$length}";
        $sql .= " OFFSET {$offset}";

        $wcfm_reviews_array = $wpdb->get_results($sql);
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $wcfm_reviews_array
        ), 200);
    }


    // Update review status
    function flutter_update_wcfm_review($request)
    {
        global $WCFM, $WCFMmp, $wpdb;

        $reviewid = absint($request['id']);
        $status = absint($request['status']);

        $wcfm_review_categories = get_wcfm_marketplace_active_review_categories();

        if ($reviewid) {
            $review_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wcfm_marketplace_reviews WHERE `ID`= " . $reviewid);
            $review_meta = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta WHERE `type` = 'rating_category' AND `review_id`= " . $reviewid . " ORDER BY ID ASC");
            if ($review_data && !empty($review_data) && is_object($review_data)) {
                if ($status) { // On Approve
                    $total_review_count = get_user_meta($review_data->vendor_id, '_wcfmmp_total_review_count', true);
                    if (!$total_review_count) $total_review_count = 0;
                    else $total_review_count = absint($total_review_count);
                    $total_review_count++;
                    update_user_meta($review_data->vendor_id, '_wcfmmp_total_review_count', $total_review_count);

                    $total_review_rating = get_user_meta($review_data->vendor_id, '_wcfmmp_total_review_rating', true);
                    if (!$total_review_rating) $total_review_rating = 0;
                    else $total_review_rating = (float)$total_review_rating;
                    $total_review_rating += (float)$review_data->review_rating;
                    update_user_meta($review_data->vendor_id, '_wcfmmp_total_review_rating', $total_review_rating);

                    $avg_review_rating = $total_review_rating / $total_review_count;
                    update_user_meta($review_data->vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating);

                    $wcfm_store_review_categories = array();
                    if (!empty($review_meta)) {
                        foreach ($review_meta as $review_meta_cat) {
                            $wcfm_store_review_categories[] = $review_meta_cat->value;
                        }
                    }

                    $category_review_rating = get_user_meta($review_data->vendor_id, '_wcfmmp_category_review_rating', true);
                    if (!$category_review_rating) $category_review_rating = array();
                    foreach ($wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category) {
                        if (isset($wcfm_store_review_categories[$wcfm_review_cat_key])) {
                            $total_category_review_rating = 0;
                            $avg_category_review_rating = 0;
                            if ($category_review_rating && !empty($category_review_rating) && isset($category_review_rating[$wcfm_review_cat_key])) {
                                $total_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['total'];
                                $avg_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['avg'];
                            }
                            $total_category_review_rating += (float)$wcfm_store_review_categories[$wcfm_review_cat_key];
                            $avg_category_review_rating = $total_category_review_rating / $total_review_count;
                            $category_review_rating[$wcfm_review_cat_key]['total'] = $total_category_review_rating;
                            $category_review_rating[$wcfm_review_cat_key]['avg'] = $avg_category_review_rating;
                        } else {
                            $category_review_rating[$wcfm_review_cat_key]['total'] = 0;
                            $category_review_rating[$wcfm_review_cat_key]['avg'] = 0;
                        }
                    }
                    $category_review_rating = update_user_meta($review_data->vendor_id, '_wcfmmp_category_review_rating', $category_review_rating);

                    update_user_meta($review_data->vendor_id, '_wcfmmp_last_author_id', $review_data->author_id);
                    update_user_meta($review_data->vendor_id, '_wcfmmp_last_author_name', $review_data->author_name);

                    $wpdb->update("{$wpdb->prefix}wcfm_marketplace_reviews", array('approved' => 1), array('ID' => $reviewid), array('%d'), array('%d'));
                } else { // On UnApprove
                    $total_review_count = get_user_meta($review_data->vendor_id, '_wcfmmp_total_review_count', true);
                    if (!$total_review_count) $total_review_count = 0;
                    else $total_review_count = absint($total_review_count);
                    if ($total_review_count) $total_review_count--;
                    update_user_meta($review_data->vendor_id, '_wcfmmp_total_review_count', $total_review_count);

                    $total_review_rating = get_user_meta($review_data->vendor_id, '_wcfmmp_total_review_rating', true);
                    if (!$total_review_rating) $total_review_rating = 0;
                    else $total_review_rating = (float)$total_review_rating;
                    if ($total_review_rating) $total_review_rating -= (float)$review_data->review_rating;
                    update_user_meta($review_data->vendor_id, '_wcfmmp_total_review_rating', $total_review_rating);

                    $avg_review_rating = 0;
                    if ($total_review_rating && $total_review_count) $avg_review_rating = $total_review_rating / $total_review_count;
                    update_user_meta($review_data->vendor_id, '_wcfmmp_avg_review_rating', $avg_review_rating);

                    $wcfm_store_review_categories = array();
                    if (!empty($review_meta)) {
                        foreach ($review_meta as $review_meta_cat) {
                            $wcfm_store_review_categories[] = $review_meta_cat->value;
                        }
                    }

                    $category_review_rating = get_user_meta($review_data->vendor_id, '_wcfmmp_category_review_rating', true);
                    if (!$category_review_rating) $category_review_rating = array();
                    foreach ($wcfm_review_categories as $wcfm_review_cat_key => $wcfm_review_category) {
                        if (isset($wcfm_store_review_categories[$wcfm_review_cat_key])) {
                            $total_category_review_rating = 0;
                            $avg_category_review_rating = 0;
                            if ($category_review_rating && !empty($category_review_rating) && isset($category_review_rating[$wcfm_review_cat_key])) {
                                $total_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['total'];
                                $avg_category_review_rating = $category_review_rating[$wcfm_review_cat_key]['avg'];
                            }
                            if ($total_category_review_rating) $total_category_review_rating -= (float)$wcfm_store_review_categories[$wcfm_review_cat_key];
                            if ($total_category_review_rating && $total_review_count) $avg_category_review_rating = $total_category_review_rating / $total_review_count;
                            $category_review_rating[$wcfm_review_cat_key]['total'] = $total_category_review_rating;
                            $category_review_rating[$wcfm_review_cat_key]['avg'] = $avg_category_review_rating;
                        } else {
                            $category_review_rating[$wcfm_review_cat_key]['total'] = 0;
                            $category_review_rating[$wcfm_review_cat_key]['avg'] = 0;
                        }
                    }
                    $category_review_rating = update_user_meta($review_data->vendor_id, '_wcfmmp_category_review_rating', $category_review_rating);

                    $wpdb->update("{$wpdb->prefix}wcfm_marketplace_reviews", array('approved' => 0), array('ID' => $reviewid), array('%d'), array('%d'));
                }
            }
        }
    }

    /* GET WCFM SALE STATS FUNCTIONS */
    function wcfm_query_time_range_filter($sql, $time, $interval = '7day', $start_date = '', $end_date = '', $table_handler = 'commission')
    {
        switch ($interval) {
            case 'year' :
                $sql .= " AND YEAR( {$table_handler}.{$time} ) = YEAR( CURDATE() )";
                break;
            case 'last_month' :
                $sql .= " AND MONTH( {$table_handler}.{$time} ) = MONTH( NOW() ) - 1";
                break;
            case 'month' :
                $sql .= " AND MONTH( {$table_handler}.{$time} ) = MONTH( NOW() )";
                break;
            case 'custom' :
                $start_date = !empty($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : $start_date;
                $end_date = !empty($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : $end_date;
                if ($start_date) $start_date = wcfm_standard_date($start_date);
                if ($end_date) $end_date = wcfm_standard_date($end_date);
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
                break;
            case 'all' :
                break;
            case '7day' :
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 7 DAY ) AND NOW()";
                break;
            case '14day':
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 14 DAY ) AND DATE_SUB( NOW(), INTERVAL 7 DAY )";
                break;
            case '21day':
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 21 DAY ) AND DATE_SUB( NOW(), INTERVAL 14 DAY )";
                break;
            case '28day':
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 28 DAY ) AND DATE_SUB( NOW(), INTERVAL 21 DAY )";
                break;
            case '35day':
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 35 DAY ) AND DATE_SUB( NOW(), INTERVAL 28 DAY )";
                break;
            case 'default' :
        }

        return $sql;
    }

    function wcfm_get_gross_sales_by_vendor($vendor_id = '', $interval = '7day', $is_paid = false, $order_id = 0, $filter_date_form = '', $filter_date_to = '')
    {
        global $WCFM, $wpdb, $WCMp, $WCFMmp;

        if ($vendor_id) $vendor_id = absint($vendor_id);

        $gross_sales = 0;

        $marketplece = wcfm_is_marketplace();
        if ($marketplece == 'wcvendors') {
            $sql = "SELECT order_id, GROUP_CONCAT(product_id) product_ids, SUM( commission.total_shipping ) AS total_shipping FROM {$wpdb->prefix}pv_commission AS commission";
            $sql .= " WHERE 1=1";
            if ($vendor_id) $sql .= " AND `vendor_id` = {$vendor_id}";
            if ($order_id) {
                $sql .= " AND `order_id` = {$order_id}";
            } else {
                if ($is_paid) {
                    $sql .= " AND commission.status = 'paid'";
                }
                $sql = $this->wcfm_query_time_range_filter($sql, 'time', $interval, $filter_date_form, $filter_date_to);
            }
            $sql .= " GROUP BY commission.order_id";

            $gross_sales_whole_week = $wpdb->get_results($sql);
            if (!empty($gross_sales_whole_week)) {
                foreach ($gross_sales_whole_week as $net_sale_whole_week) {
                    if ($net_sale_whole_week->order_id) {
                        $order_post_title = get_the_title($net_sale_whole_week->order_id);
                        if (!$order_post_title) continue;
                        try {
                            $order = wc_get_order($net_sale_whole_week->order_id);
                            $line_items = $order->get_items('line_item');
                            $valid_items = (array)$order_item_ids = explode(",", $net_sale_whole_week->product_ids);

                            foreach ($line_items as $key => $line_item) {
                                if ($line_item->get_product_id() == 0) {
                                    $_product_id = wc_get_order_item_meta($key, '_product_id', true);
                                    $_variation_id = wc_get_order_item_meta($key, '_variation_id', true);
                                    if (in_array($_product_id, $valid_items) || in_array($_variation_id, $valid_items)) {
                                        $gross_sales += (float)sanitize_text_field($line_item->get_total());
                                        if (version_compare(WCV_VERSION, '2.0.0', '<')) {
                                            if (WC_Vendors::$pv_options->get_option('give_tax')) {
                                                $gross_sales += (float)sanitize_text_field($line_item->get_total_tax());
                                            }
                                        } else {
                                            if (get_option('wcvendors_vendor_give_taxes')) {
                                                $gross_sales += (float)sanitize_text_field($line_item->get_total_tax());
                                            }
                                        }
                                    }
                                } elseif (in_array($line_item->get_variation_id(), $valid_items) || in_array($line_item->get_product_id(), $valid_items)) {
                                    $gross_sales += (float)sanitize_text_field($line_item->get_total());
                                    if (version_compare(WCV_VERSION, '2.0.0', '<')) {
                                        if (WC_Vendors::$pv_options->get_option('give_tax')) {
                                            $gross_sales += (float)sanitize_text_field($line_item->get_total_tax());
                                        }
                                    } else {
                                        if (get_option('wcvendors_vendor_give_taxes')) {
                                            $gross_sales += (float)sanitize_text_field($line_item->get_total_tax());
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                    if (version_compare(WCV_VERSION, '2.0.0', '<')) {
                        if (WC_Vendors::$pv_options->get_option('give_shipping')) {
                            $gross_sales += (float)$net_sale_whole_week->total_shipping;
                        }
                    } else {
                        if (get_option('wcvendors_vendor_give_shipping')) {
                            $gross_sales += (float)$net_sale_whole_week->total_shipping;
                        }
                    }
                }
            }
        } elseif ($marketplece == 'wcmarketplace') {
            $sql = "SELECT order_item_id, shipping, shipping_tax_amount FROM {$wpdb->prefix}wcmp_vendor_orders AS commission";
            $sql .= " WHERE 1=1";
            if ($vendor_id) $sql .= " AND `vendor_id` = {$vendor_id}";
            if ($order_id) {
                $sql .= " AND `order_id` = {$order_id}";
            } else {
                $sql .= " AND `line_item_type` = 'product' AND `commission_id` != 0 AND `commission_id` != '' AND `is_trashed` != 1";
                if ($is_paid) {
                    $sql .= " AND commission.commission_status = 'paid'";
                    $sql = $this->wcfm_query_time_range_filter($sql, 'commission_paid_date', $interval, $filter_date_form, $filter_date_to);
                } else {
                    $sql = $this->wcfm_query_time_range_filter($sql, 'created', $interval, $filter_date_form, $filter_date_to);
                }
            }

            $gross_sales_whole_week = $wpdb->get_results($sql);
            if (!empty($gross_sales_whole_week)) {
                foreach ($gross_sales_whole_week as $net_sale_whole_week) {
                    if ($net_sale_whole_week->order_item_id) {
                        try {
                            $line_item = new WC_Order_Item_Product($net_sale_whole_week->order_item_id);
                            $gross_sales += (float)sanitize_text_field($line_item->get_total());
                            if ($WCMp->vendor_caps->vendor_payment_settings('give_tax')) {
                                $gross_sales += (float)sanitize_text_field($line_item->get_total_tax());
                                $gross_sales += (float)$net_sale_whole_week->shipping_tax_amount;
                            }
                            if ($WCMp->vendor_caps->vendor_payment_settings('give_shipping')) {
                                $gross_sales += (float)$net_sale_whole_week->shipping;
                            }
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                }
            }
        } elseif ($marketplece == 'wcpvendors') {
            $sql = "SELECT SUM( commission.product_amount ) AS total_product_amount, SUM( commission.product_shipping_amount ) AS product_shipping_amount, SUM( commission.product_shipping_tax_amount ) AS product_shipping_tax_amount, SUM( commission.product_tax_amount ) AS product_tax_amount FROM " . WC_PRODUCT_VENDORS_COMMISSION_TABLE . " AS commission";
            $sql .= " WHERE 1=1";
            if ($vendor_id) $sql .= " AND commission.vendor_id = {$vendor_id}";
            if ($order_id) {
                $sql .= " AND `order_id` = {$order_id}";
            } else {
                if ($is_paid) {
                    $sql .= " AND commission.commission_status = 'paid'";
                    $sql = $this->wcfm_query_time_range_filter($sql, 'paid_date', $interval, $filter_date_form, $filter_date_to);
                } else {
                    $sql = $this->wcfm_query_time_range_filter($sql, 'order_date', $interval, $filter_date_form, $filter_date_to);
                }
            }

            $total_sales = $wpdb->get_results($sql);
            if (!empty($total_sales)) {
                foreach ($total_sales as $total_sale) {
                    $gross_sales = $total_sale->total_product_amount + $total_sale->product_shipping_amount + $total_sale->product_shipping_tax_amount + $total_sale->product_tax_amount;
                }
            }
        } elseif ($marketplece == 'dokan') {
            $sql = "SELECT SUM( commission.order_total ) AS total_order_amount FROM {$wpdb->prefix}dokan_orders AS commission LEFT JOIN {$wpdb->posts} p ON commission.order_id = p.ID";
            $sql .= " WHERE 1=1";
            if ($vendor_id) $sql .= " AND commission.seller_id = {$vendor_id}";
            if ($order_id) {
                $sql .= " AND `commission.order_id` = {$order_id}";
            } else {
                $status = dokan_withdraw_get_active_order_status_in_comma();
                $sql .= " AND commission.order_status IN ({$status})";
                $sql = $this->wcfm_query_time_range_filter($sql, 'post_date', $interval, '', '', 'p');
            }

            $total_sales = $wpdb->get_results($sql);
            if (!empty($total_sales)) {
                foreach ($total_sales as $total_sale) {
                    $gross_sales = $total_sale->total_order_amount;
                }
            }
        } elseif ($marketplece == 'wcfmmarketplace') {
            $sql = "SELECT ID, order_id, item_id, item_total, item_sub_total, refunded_amount, shipping, tax, shipping_tax_amount FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
            $sql .= " WHERE 1=1";
            if ($vendor_id) $sql .= " AND `vendor_id` = {$vendor_id}";
            if ($order_id) {
                $sql .= " AND `order_id` = {$order_id}";
                //$sql .= " AND `is_refunded` != 1";
            } else {
                $sql .= apply_filters('wcfm_order_status_condition', '', 'commission');
                $sql .= " AND `is_trashed` = 0";
                if ($is_paid) {
                    $sql .= " AND commission.withdraw_status = 'completed'";
                    $sql = $this->wcfm_query_time_range_filter($sql, 'commission_paid_date', $interval, $filter_date_form, $filter_date_to);
                } else {
                    $sql = $this->wcfm_query_time_range_filter($sql, 'created', $interval, $filter_date_form, $filter_date_to);
                }
            }

            $gross_sales_whole_week = $wpdb->get_results($sql);
            $gross_commission_ids = array();
            $gross_total_refund_amount = 0;
            if (!empty($gross_sales_whole_week)) {
                foreach ($gross_sales_whole_week as $net_sale_whole_week) {
                    $gross_commission_ids[] = $net_sale_whole_week->ID;
                    $gross_total_refund_amount += (float)sanitize_text_field($net_sale_whole_week->refunded_amount);
                }

                if (!empty($gross_commission_ids)) {
                    try {
                        if (apply_filters('wcfmmmp_gross_sales_respect_setting', true)) {
                            $gross_sales = (float)$WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta_sum($gross_commission_ids, 'gross_total');
                        } else {
                            $gross_sales = (float)$WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta_sum($gross_commission_ids, 'gross_sales_total');
                        }

                        // Deduct Refunded Amount
                        $gross_sales -= (float)$gross_total_refund_amount;
                    } catch (Exception $e) {
                        //continue;
                    }
                }
            }
        }

        if (!$gross_sales) $gross_sales = 0;

        return $gross_sales;
    }

    /**
     * Total commission paid by Admin
     */
    function wcfm_get_commission_by_vendor($vendor_id = '', $interval = '7day', $is_paid = false, $order_id = 0, $filter_date_form = '', $filter_date_to = '')
    {
        global $WCFM, $wpdb, $WCMp;

        if ($vendor_id) $vendor_id = absint($vendor_id);

        $commission = 0;

        $marketplece = wcfm_is_marketplace();
        if ($marketplece == 'wcvendors') {
            $commission_table = 'pv_commission';
            $total_due = 'total_due';
            $total_shipping = 'total_shipping';
            $tax = 'tax';
            $shipping_tax = 'tax';
            $status = 'status';
            $time = 'time';
            $vendor_handler = 'vendor_id';
            $table_handler = 'commission';
        } elseif ($marketplece == 'wcmarketplace') {
            $commission_table = 'wcmp_vendor_orders';
            $total_due = 'commission_amount';
            $total_shipping = 'shipping';
            $tax = 'tax';
            $shipping_tax = 'shipping_tax_amount';
            $status = 'commission_status';
            $vendor_handler = 'vendor_id';
            $table_handler = 'commission';
            if ($is_paid)
                $time = 'commission_paid_date';
            else
                $time = 'created';
        } elseif ($marketplece == 'wcpvendors') {
            $commission_table = 'wcpv_commissions';
            $total_due = 'total_commission_amount';
            $total_shipping = 'product_shipping_amount';
            $tax = 'product_tax_amount';
            $shipping_tax = 'product_shipping_tax_amount';
            $status = 'commission_status';
            $vendor_handler = 'vendor_id';
            $table_handler = 'commission';
            if ($is_paid)
                $time = 'paid_date';
            else
                $time = 'order_date';
        } elseif ($marketplece == 'dokan') {
            $order_status = apply_filters('wcfm_dokan_allowed_order_status', array('completed', 'processing', 'on-hold'));
            $commission_table = 'dokan_orders';
            $total_due = 'net_amount';
            $time = 'post_date';
            $vendor_handler = 'seller_id';
            $table_handler = 'p';
            if ($is_paid) {
                $sql = "SELECT SUM( withdraw.amount ) AS amount FROM {$wpdb->prefix}dokan_withdraw AS withdraw";
                $sql .= " WHERE 1=1";
                if ($vendor_id) $sql .= " AND withdraw.user_id = {$vendor_id}";
                $sql .= " AND withdraw.status = 1";
                $sql = $this->wcfm_query_time_range_filter($sql, 'date', $interval, $filter_date_form, $filter_date_to, 'withdraw');
                $total_commissions = $wpdb->get_results($sql);
                $commission = 0;
                if (!empty($total_commissions)) {
                    foreach ($total_commissions as $total_commission) {
                        $commission += $total_commission->amount;
                    }
                }
                if (!$commission) $commission = 0;
                return $commission;
            }
        } elseif ($marketplece == 'wcfmmarketplace') {
            $commission_table = 'wcfm_marketplace_orders';
            $total_due = 'total_commission';
            $total_shipping = 'shipping';
            $tax = 'tax';
            $shipping_tax = 'shipping_tax_amount';
            $status = 'withdraw_status';
            $vendor_handler = 'vendor_id';
            $table_handler = 'commission';
            if ($is_paid)
                $time = 'commission_paid_date';
            else
                $time = 'created';
        }

        if ($marketplece == 'dokan') {
            $order_status = apply_filters('wcfm_dokan_allowed_order_status', array('completed', 'processing', 'on-hold'));
            $sql = "SELECT SUM( commission.{$total_due} ) AS total_due FROM {$wpdb->prefix}{$commission_table} AS commission LEFT JOIN {$wpdb->posts} p ON commission.order_id = p.ID";
        } else {
            $sql = "SELECT SUM( commission.{$total_due} ) AS total_due, SUM( commission.{$total_shipping} ) AS total_shipping, SUM( commission.{$tax} ) AS tax, SUM( commission.{$shipping_tax} ) AS shipping_tax FROM {$wpdb->prefix}{$commission_table} AS commission";
        }

        $sql .= " WHERE 1=1";
        if ($vendor_id) $sql .= " AND commission.{$vendor_handler} = {$vendor_id}";
        if ($is_paid) $sql .= " AND (commission.{$status} = 'paid' OR commission.{$status} = 'completed')";
        if ($marketplece == 'wcmarketplace') {
            $sql .= " AND commission.commission_id != 0 AND commission.commission_id != '' AND `is_trashed` != 1";
        }
        if ($marketplece == 'dokan') {
            $status = dokan_withdraw_get_active_order_status_in_comma();
            $sql .= " AND commission.order_status IN ({$status})";
        }
        if ($marketplece == 'wcfmmarketplace') {
            if ($order_id) {
                $sql .= " AND `order_id` = {$order_id}";
            } else {
                $sql .= apply_filters('wcfm_order_status_condition', '', 'commission');
                $sql .= " AND `is_refunded` = 0 AND `is_trashed` = 0";
            }
        }
        if (!$order_id)
            $sql = $this->wcfm_query_time_range_filter($sql, $time, $interval, $filter_date_form, $filter_date_to, $table_handler);

        $total_commissions = $wpdb->get_results($sql);
        $commission = 0;
        if (!empty($total_commissions)) {
            foreach ($total_commissions as $total_commission) {
                $commission += $total_commission->total_due;
                if ($marketplece == 'wcvendors') {
                    if (version_compare(WCV_VERSION, '2.0.0', '<')) {
                        if (WC_Vendors::$pv_options->get_option('give_tax')) {
                            $commission += $total_commission->total_shipping;
                        }
                        if (WC_Vendors::$pv_options->get_option('give_shipping')) {
                            $commission += $total_commission->tax;
                        }
                    } else {
                        if (get_option('wcvendors_vendor_give_taxes')) {
                            $commission += $total_commission->total_shipping;
                        }
                        if (get_option('wcvendors_vendor_give_shipping')) {
                            $commission += $total_commission->tax;
                        }
                    }
                } elseif ($marketplece == 'wcmarketplace') {
                    if ($WCMp->vendor_caps->vendor_payment_settings('give_shipping')) {
                        $commission += ($total_commission->total_shipping == 'NAN') ? 0 : $total_commission->total_shipping;
                    }
                    if ($WCMp->vendor_caps->vendor_payment_settings('give_tax')) {
                        $commission += ($total_commission->tax == 'NAN') ? 0 : $total_commission->tax;
                        $commission += ($total_commission->shipping_tax == 'NAN') ? 0 : $total_commission->shipping_tax;
                    }
                }
            }
        }
        if (!$commission) $commission = 0;

        return $commission;
    }

    /* GET WCFM SALE STATS FUNCTIONS. CUSTOM BY TOAN 04/11/2020 */

    /* GET NOTIFICATIONS */
    function wcfm_get_wcfm_notification_by_vendor($request, $user_id)
    {
        global $WCFM, $wpdb;

        if (isset($request['per_page']) && $request['per_page']) {
            $limit = absint($request['per_page']);
            $offset = absint($request['page']);
            $offset = ($offset - 1) * $limit;
            $message_to = apply_filters('wcfm_message_author', $user_id);

            $sql = 'SELECT wcfm_messages.* FROM ' . $wpdb->prefix . 'wcfm_messages AS wcfm_messages';
            $vendor_filter = " WHERE ( `author_id` = {$message_to} OR `message_to` = -1 OR `message_to` = {$message_to} )";
            $sql .= $vendor_filter;
            $message_status_filter = " AND NOT EXISTS (SELECT * FROM {$wpdb->prefix}wcfm_messages_modifier as wcfm_messages_modifier_2 WHERE wcfm_messages.ID = wcfm_messages_modifier_2.message AND wcfm_messages_modifier_2.read_by={$message_to})";
            $sql .= $message_status_filter;
            $sql .= " ORDER BY wcfm_messages.`ID` DESC";
            $sql .= " LIMIT $limit";
            $sql .= " OFFSET $offset";
            $wcfm_messages = $wpdb->get_results($sql);

            foreach ($wcfm_messages as $wcfm_message) {
                unset(
                    $wcfm_message->author_id,
                    $wcfm_message->reply_to,
                    $wcfm_message->author_is_admin,
                    $wcfm_message->author_is_vendor,
                    $wcfm_message->author_is_customer,
                    $wcfm_message->is_notice,
                    $wcfm_message->is_direct_message,
                    $wcfm_message->is_pined,
                    $wcfm_message->message_to
                );
                $wcfm_message->message = strip_tags($wcfm_message->message);
            }
            return $wcfm_messages;
        }

    }

    function get_nearest_vendors($request)
    {
        global $WCFM, $WCFMmp, $wpdb;

        $wcfmmp_radius_lat = $request['lat'];
        $wcfmmp_radius_lng = $request['long'];
        $wcfmmp_radius = .0;
        if (isset($request['radius'])) {
            $wcfmmp_radius = $request['radius'];
        }
        $args = array(
            'role' => 'wcfm_vendor',
            'fields' => array('ID'),
        );
        $users = get_users($args);

        $list_nearby_users = array();
        foreach ($users as $user) {
            $vendor_lat = get_user_meta($user->ID, '_wcfm_store_lat')[0];
            $vendor_lng = get_user_meta($user->ID, '_wcfm_store_lng')[0];
            if (isset($vendor_lat) && isset($vendor_lng)) {
                if ($vendor_lat != '' && $vendor_lng != '') {
                    $distance = round($this->distance($wcfmmp_radius_lat, $wcfmmp_radius_lng, $vendor_lat, $vendor_lat));
                    if ($distance <= $wcfmmp_radius) {
                        $list_nearby_users[] = $user->ID;
                    }
                }
            }
        }

        $result = array();
        foreach ($list_nearby_users as $item):
            $result[] = $this->get_formatted_item_data($item, array(), null, null, null);
        endforeach;
        return $result;


    }

    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $pi80 = M_PI / 180;
        $lat1 *= $pi80;
        $lon1 *= $pi80;
        $lat2 *= $pi80;
        $lon2 *= $pi80;
        $r = 6372.797; // mean radius of Earth in km
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $km = $r * $c;
        //echo ' '.$km;
        return $km;
    }

    function get_product_categories($request)
    {
        $store_id = $request['id'];
        $page = isset($request["page"]) ? $request["page"] : 1;
        $limit = isset($request["limit"]) ? $request["limit"] : 10;

        global $woocommerce, $wpdb;
        $table_name = $wpdb->prefix . "posts";
        $sql = "SELECT * FROM `$table_name` ";
        $sql .= "WHERE `$table_name`.`post_type` = 'product' AND `$table_name`.`post_status` = 'publish' ";
        if (isset($store_id)) {
            $sql .= "AND `$table_name`.`post_author` = $store_id";
        }

        $products = $wpdb->get_results($sql);

        $categoryIds = array();
        foreach ($products as $object) {
            $terms = get_the_terms($object->ID, 'product_cat');
            foreach ((array)$terms as $term) {
                $cat_id = $term->term_id;
                if (!in_array($cat_id, $categoryIds)) {
                    $categoryIds[] = $cat_id;
                }
            }
        }
        if (empty($categoryIds)) {
            return [];
        }

        $controller = new WC_REST_Product_Categories_Controller();
        $req = new WP_REST_Request('GET');
        $params = array('include' => $categoryIds, 'page' => $page, 'per_page' => $limit, 'orderby' => 'name', 'order' => 'asc');
        if (isset($request['lang'])) {
            $params['lang'] = $request['lang'];
        }
        if (isset($request['hide_empty'])) {
            $params['hide_empty'] = $request['hide_empty'];
        }
        $req->set_query_params($params);

        $response = $controller->get_items($req);
        return $response->get_data();
    }

    function generate_vendor_delivery_time_checkout_field($vendor_id)
    {
        global $WCFM, $WCFMmp, $wcfmd;

        $wcfm_vendor_delivery_time = get_user_meta($vendor_id, 'wcfm_vendor_delivery_time', true);
        if (!$wcfm_vendor_delivery_time) $wcfm_vendor_delivery_time = array();

        $wcfm_delivery_time_enable = isset($wcfm_vendor_delivery_time['enable']) ? 'yes' : 'no';

        if ($wcfm_delivery_time_enable == 'yes') {

            $wcfm_delivery_time_off_days = isset($wcfm_vendor_delivery_time['off_days']) ? $wcfm_vendor_delivery_time['off_days'] : array();
            $wcfm_delivery_time_start_from = isset($wcfm_vendor_delivery_time['start_from']) ? $wcfm_vendor_delivery_time['start_from'] : 0;
            $wcfm_delivery_time_end_at = isset($wcfm_vendor_delivery_time['end_at']) ? $wcfm_vendor_delivery_time['end_at'] : 0;
            $wcfm_delivery_time_slots_duration = isset($wcfm_vendor_delivery_time['slots_duration']) ? $wcfm_vendor_delivery_time['slots_duration'] : 0;
            $wcfm_delivery_time_display_format = isset($wcfm_vendor_delivery_time['display_format']) ? $wcfm_vendor_delivery_time['display_format'] : 'date_time';

            $wcfm_delivery_time_day_times = isset($wcfm_vendor_delivery_time['day_times']) ? $wcfm_vendor_delivery_time['day_times'] : array();
            $wcfm_delivery_time_start_from_options = get_wcfm_start_from_delivery_times_raw();
            $wcfm_delivery_time_end_at_options = get_wcfm_end_at_delivery_times_raw();
            $wcfm_delivery_time_slots_duration_options = get_wcfm_slots_duration_delivery_times_raw();

            $time_format = wc_date_format() . ' ' . wc_time_format();
            if ($wcfm_delivery_time_display_format == 'date') {
                $time_format = wc_date_format();
            } else if ($wcfm_delivery_time_display_format == 'time') {
                $time_format = wc_time_format();
            }

            $current_time = current_time('timestamp');

            $start_time = strtotime('+' . $wcfm_delivery_time_start_from_options[$wcfm_delivery_time_start_from], $current_time);
            $end_time = strtotime('+' . $wcfm_delivery_time_end_at_options[$wcfm_delivery_time_end_at], $start_time);

            $time_slots = array();
            $next_time_slot = $start_time;


            while ($end_time > $next_time_slot) {
                $week_date = date('Y-m-d', $next_time_slot);
                $weekday = date('N', $next_time_slot);
                $weekday -= 1;
                if (!empty($wcfm_delivery_time_off_days)) {
                    if (in_array($weekday, $wcfm_delivery_time_off_days)) {
                        $next_time_slot = strtotime('+24 hours', $next_time_slot);
                        $end_time = strtotime('+24 hours', $end_time);
                        continue;
                    }
                }

                $time_slots[$next_time_slot] = date_i18n($time_format, $next_time_slot);
                $next_time_slot = strtotime('+' . $wcfm_delivery_time_slots_duration_options[$wcfm_delivery_time_slots_duration], $next_time_slot);
            }

            if (count($time_slots) > 1) {
                $time_slots[$end_time] = date_i18n($time_format, $end_time);
            }
        }
        $time_arr = array();
        foreach ($time_slots as $k => $v) {
            $time_arr[] = array($k => $v);
        }
        $data = array();
        for ($i = 0; $i < count($time_arr); $i++) {
            if ($i == (count($time_arr) - 1)) {
                break;
            }
            $key = key($time_arr[$i]);
            $value = current($time_arr[$i]);
            $key2 = key($time_arr[$i + 1]);
            $value2 = current($time_arr[$i + 1]);
            if ($value == $value2) {
                $d = array();
                $d['timestamp'] = $key;
                $d['delivery_date'] = $value;
                $data[] = $d;
                break;
            }
            $d = array();
            $d['timestamp'] = $key . '|' . $key2;
            if ($wcfm_delivery_time_display_format == 'date_time') {
                $d['delivery_date'] = $value . '-' . date_i18n(wc_time_format(), $key2);
            } else {
                $d['delivery_date'] = $value . '-' . $value2;
            }

            $data[] = $d;

        }

        return $data;
    }
}