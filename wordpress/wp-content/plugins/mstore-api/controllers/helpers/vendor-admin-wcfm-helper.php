<?php

class VendorAdminWCFMHelper
{
    public function sendError($code, $message, $statusCode)
    {
        return new WP_Error($code, $message, [
            "status" => $statusCode,
        ]);
    }

    protected function get_product_item($id)
    {
        if (!wc_get_product($id)) {
            return $this->sendError(
                "invalid_product",
                "This product does not exist",
                404
            );
        }
        return wc_get_product($id);
    }

    protected function find_image_id($image)
    {
        $image_id = attachment_url_to_postid(stripslashes($image));
        return $image_id;
    }

    protected function http_check($url)
    {
        if (
            !(substr($url, 0, 7) == "http://") &&
            !(substr($url, 0, 8) == "https://")
        ) {
            return false;
        }
        return true;
    }

    protected function get_attribute_taxonomy_name($slug, $product)
    {
        $attributes = $product->get_attributes();

        if (!isset($attributes[$slug])) {
            return str_replace("pa_", "", $slug);
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
        if (isset($attribute["is_taxonomy"]) && $attribute["is_taxonomy"]) {
            return wc_get_product_terms($product_id, $attribute["name"], [
                "fields" => "names",
            ]);
        } elseif (isset($attribute["value"])) {
            return array_map("trim", explode("|", $attribute["value"]));
        }

        return [];
    }

    protected function get_attribute_slugs($product_id, $attribute)
    {
        if (isset($attribute["is_taxonomy"]) && $attribute["is_taxonomy"]) {
            return wc_get_product_terms($product_id, $attribute["name"], [
                "fields" => "slugs",
            ]);
        } elseif (isset($attribute["value"])) {
			$arr = explode("|", $attribute["value"]);
			$data = array();
			foreach($arr as $item){
				$data[] = str_replace('-',' ',trim($item)) ;
			}
            return $data;
        }

        return [];
    }

    /// GET FUNCTIONS
    public function get_vendor_profile($user_id)
    {
        $vendor_data = get_user_meta($user_id, "wcfmmp_profile_settings", true);
        if (is_string($vendor_data)) {
            $vendor_data = [];
        }
        $vendor_data["logo"] = wp_get_attachment_image_src(
            $vendor_data["gravatar"]
        )[0];
        $vendor_data["banner"] = wp_get_attachment_image_src(
            $vendor_data["banner"]
        )[0];
        $vendor_data["mobile_banner"] = wp_get_attachment_image_src(
            $vendor_data["mobile_banner"]
        )[0];
        $vendor_data["list_banner"] = wp_get_attachment_image_src(
            $vendor_data["list_banner"]
        )[0];
        $data = [];
        foreach ($vendor_data["banner_slider"] as $item) {
            $image = wp_get_attachment_image_src($item["image"])[0];
            $link = $item["link"];
            $data[] = [
                "image" => $image,
                "link" => $link,
            ];
        }
        $vendor_data["banner_slider"] = $data;
        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => $vendor_data,
            ],
            200
        );
    }

    public function update_vendor_profile($request, $user_id)
    {
        $data = json_decode( $request, true);
        $vendor_data = get_user_meta($user_id, "wcfmmp_profile_settings", true);
        if (is_string($vendor_data)) {
            $vendor_data = [];
        }
        $vendor_data["store_name"] =  sanitize_text_field($data["store_name"]);
        $vendor_data["store_slug"] =  sanitize_text_field($data["store_slug"]);
        wp_update_user(array(
            'ID' => $user_id,
            'user_nicename' =>  sanitize_text_field($data["store_slug"])
        ));
        $vendor_data["wcfmmp_store_name"] =  sanitize_text_field($data["store_name"]);
        update_user_meta($user_id, 'store_name',  sanitize_text_field($data["store_name"]));
        update_user_meta($user_id, 'wcfmmp_store_name',  sanitize_text_field($data["store_name"]));


        $vendor_data["store_email"] =  sanitize_text_field($data["store_email"]);
        $vendor_data["phone"] =  sanitize_text_field($data["phone"]);

        $count = 0;

        if (isset($data["logo"])) {
            $img_id = upload_image_from_mobile(
                sanitize_text_field($data["logo"]),
                $count,
                $user_id
            );
            $count = $count + 1;
            $vendor_data["gravatar"] = $img_id;
        }

        if (isset($data["mobile_banner"])) {
            $img_id = upload_image_from_mobile(
                sanitize_text_field($data["mobile_banner"]),
                $count,
                $user_id
            );
            $count++;
            $vendor_data["mobile_banner"] = $img_id;
        }

        if (isset($data["banner"])) {
            $img_id = upload_image_from_mobile(
                sanitize_text_field($data["banner"]),
                $count,
                $user_id
            );
            $count++;
            $vendor_data["banner"] = $img_id;
        }
        if ( isset($data["banner_type"])) {
            $vendor_data["banner_type"] = $data["banner_type"];
        }

        if (isset($data["banner_slider"]) && isset($data["banner_type"])) {
            $vendor_data["banner_slider"] = [];
            foreach ($data["banner_slider"] as $item) {
                if ($item["type"] == "asset") {
                    $img_id = upload_image_from_mobile(
                        sanitize_text_field($item["image"]),
                        $count,
                        $user_id
                    );
                }
                if ($item["type"] == "url") {
                    $img_id = attachment_url_to_postid( sanitize_text_field($item["image"]));
                }
                $vendor_data["banner_slider"][] = [
                    "image" => $img_id,
                    "link" =>  sanitize_text_field($item["link"]),
                ];
            }
            $vendor_data["banner_type"] =  sanitize_text_field($data["banner_type"]);
        }

        if (isset($data["banner_video"]) && isset($data["banner_type"])) {
            $vendor_data["banner_video"] =  sanitize_text_field($data["banner_video"]);
            $vendor_data["banner_type"] =  sanitize_text_field($data["banner_type"]);
        }

        if (isset($data["list_banner"])) {
            $img_id = upload_image_from_mobile(
                sanitize_text_field($data["list_banner"]),
                $count,
                $user_id
            );
            $count++;
            $vendor_data["list_banner"] = $img_id;
        }
        if (isset($data["list_banner_type"])) {
            $vendor_data["list_banner_type"] =  sanitize_text_field($data["list_banner_type"]);
        }
        if (
            isset($data["list_banner_video"])
        ){
            $vendor_data["list_banner_video"] =  sanitize_text_field($data["list_banner_video"]);
        }

        $vendor_data["shop_description"] =  sanitize_text_field($data["shop_description"]);
        $vendor_data["_store_description"] =  sanitize_text_field($data["shop_description"]);
        update_user_meta($user_id, '_store_description',  sanitize_text_field($data["shop_description"]));


        $vendor_data["address"] =  $data["address"];

        $vendor_data["geolocation"]["store_location"] =  sanitize_text_field($data["store_location"]);
        $vendor_data["geolocation"]["store_lat"] =  sanitize_text_field($data["store_lat"]);
        $vendor_data["geolocation"]["store_lng"] =  sanitize_text_field($data["store_lng"]);
        $vendor_data["store_location"] =  sanitize_text_field($data["store_location"]);
        $vendor_data["store_lat"] =  sanitize_text_field($data["store_lat"]);
        $vendor_data["store_lng"] =  sanitize_text_field($data["store_lng"]);

        $vendor_data["store_hide_email"] =  sanitize_text_field($data["store_hide_email"]);
        $vendor_data["store_hide_phone"] =  sanitize_text_field($data["store_hide_phone"]);
        $vendor_data["store_hide_address"] =  sanitize_text_field($data["store_hide_address"]);
        $vendor_data["store_hide_map"] =  sanitize_text_field($data["store_hide_map"]);
        $vendor_data["store_hide_description"] = sanitize_text_field($data["store_hide_description"]);
        $vendor_data["store_hide_policy"] =  sanitize_text_field($data["store_hide_policy"]);

        update_user_meta($user_id, "wcfmmp_profile_settings", $vendor_data);

        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => 1,
            ],
            200
        );
    }

    public function flutter_get_products($request, $user_id)
    {
        global $wpdb;
        $page = isset($request["page"]) ? sanitize_text_field($request["page"])  : 1;
        $limit = isset($request["per_page"]) ? sanitize_text_field($request["per_page"]) : 10;
        if(!is_numeric($page)){
            $page = 1;
        }
        if(!is_numeric($limit)){
            $limit = 10;
        }
        if ($page >= 1) {
            $page = ($page - 1) * $limit;
        }

        if ($user_id) {
            $vendor_id = absint($user_id);
        }

        $table_name = $wpdb->prefix . "posts";
        $sql = "SELECT * FROM `$table_name` WHERE `$table_name`.`post_author` = %s AND `$table_name`.`post_type` = 'product' AND `$table_name`.`post_status` != 'trash'";

        if (isset($request["search"])) {
            $search =  sanitize_text_field($request["search"]);
            $search = "%$search%";
            $sql .= " AND (`$table_name`.`post_content` LIKE %s OR `$table_name`.`post_title` LIKE %s OR `$table_name`.`post_excerpt` LIKE %s)";
        }
        $sql .= " ORDER BY `ID` DESC LIMIT %d OFFSET %d";

        if (isset($search)) {
            $sql = $wpdb->prepare($sql, $vendor_id, $search, $search, $search, $limit, $page);
        } else {
            $sql = $wpdb->prepare($sql, $vendor_id, $limit, $page);
        }
        
        $item = $wpdb->get_results($sql);

        $products_arr = [];
        foreach ($item as $pro) {
            $product = wc_get_product($pro->ID);
            $p = $product->get_data();
            $image_arr = [];
            foreach (array_filter($p["gallery_image_ids"]) as $img) {
                $image = wp_get_attachment_image_src($img, "full");
                if (!is_null($image[0])) {
                    $image_arr[] = $image[0];
                }
            }
			
			

            $image = wp_get_attachment_image_src($p["image_id"], "full");
            if (!is_null($image[0])) {
                $p["featured_image"] = $image[0];
            }

            $p["images"] = $image_arr;
            $p["category_ids"] = [];
			$p['categories'] = [];
            $category_ids = wp_get_post_terms($p["id"], "product_cat");
            foreach ($category_ids as $cat) {
                if ($cat->slug != "uncategorized") {
                    $p["category_ids"][] = $cat->term_id;
					$cat_data = $cat;
					$cat_data->has_children = !empty(get_term_children($cat->term_id, 'product_cat'));
					$p['categories'][] = $cat_data;
                }
            }
            $p["type"] = $product->get_type();
            $p["on_sale"] = $product->is_on_sale();
            $p["tags"] = wp_get_post_terms($product->get_id(), "product_tag");

            $attributes = [];
            foreach ($product->get_attributes() as $attribute) {
                $attributes[] = [
                    "id" => $attribute["is_taxonomy"]
                        ? wc_attribute_taxonomy_id_by_name($attribute["name"])
                        : 0,
                    "name" =>
                        0 === strpos($attribute["name"], "pa_")
                            ? get_taxonomy($attribute["name"])->labels
                            ->singular_name
                            : $attribute["name"],
                    "position" => (int)$attribute["position"],
                    "visible" => (bool)$attribute["is_visible"],
                    "variation" => (bool)$attribute["is_variation"],
                    "options" => $this->get_attribute_options(
                        $product->get_id(),
                        $attribute
                    ),
                    "slugs" => $this->get_attribute_slugs(
                        $product->get_id(),
                        $attribute
                    ),
                    "default" => 0 === strpos($attribute["name"], "pa_"),
                    "slug" => str_replace(' ','-',$attribute["name"]),
                ];
            }
            $p["attributesData"] = $attributes;
            if ($product->get_type() == "variable") {
                $result = [];
                $p['min_price'] = $product->get_variation_price();
                $p['max_price'] = $product->get_variation_price('max');
                if(!$p['min_price']){
                    $p['min_price'] = '0';
                }
                if(!$p['max_price']){
                    $p['max_price'] = '0';
                }
                $query = [
                    "post_parent" => $product->get_id(),
                    "post_status" => ["publish", "private"],
                    "post_type" => ["product_variation"],
                    "posts_per_page" => -1,
                ];

                $wc_query = new WP_Query($query);
                while ($wc_query->have_posts()):
                    $wc_query->next_post();
                    $result[] = $wc_query->post;
                endwhile;

                foreach ($result as $variation) {
                    $p_varation = new WC_Product_Variation($variation->ID);
                    $dataVariation = array();
                    $dataVariation["variation_id"] = $p_varation->get_id();
                    $dataVariation["max_qty"] = $p_varation->get_stock_quantity();
                    $dataVariation["variation_is_active"] =
                        $p_varation->get_status() == "publish";
                    $dataVariation["display_price"] = $p_varation->get_sale_price();
                    $dataVariation["display_regular_price"] = $p_varation->get_regular_price();
                    $dataVariation["slugs"] = $p_varation->get_attributes();
                    $dataVariation["manage_stock"] = $p_varation->get_manage_stock();
                    $attributes = $p_varation->get_attributes();
                    $dataVariation["attributes"] = [];
                    foreach ($dataVariation["slugs"] as $key => $value) {
                        foreach ($p["attributesData"] as $item) {
                            if ($item["slug"] === $key) {
                                for ($i = 0; $i < count($item["slugs"]); $i++) {
                                    if ($value === $item["slugs"][$i]) {
                                        $dataVariation["attributes"][$key] =
                                            $item["options"][$i];
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                    }

                    $p["variable_products"][] = $dataVariation;
                }
            }
            $products_arr[] = $p;
        }

        return apply_filters(
            "flutter_get_products",
            $products_arr,
            $request,
            $user_id
        );
    }

    public function flutter_get_orders($request, $user_id)
    {
        $profile_pic = [];
        $api = new WC_REST_Orders_V1_Controller();
        $results = [];
        if (
            is_plugin_active(
                "wc-multivendor-marketplace/wc-multivendor-marketplace.php"
            )
        ) {
            global $wpdb;
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
            $table_name = $wpdb->prefix . "wcfm_marketplace_orders";
            $sql =
                "SELECT * FROM " . $table_name . " WHERE vendor_id = %s AND is_trashed != 1";

            if (isset($request["status"])) {
                $sql .= " AND order_status = %s";
            }
            if (isset($request["search"])) {
                $sql .= " AND order_id LIKE %s";
            }
            if (isset($request['name'])) {
                $results = [];
                $table_name2 = $wpdb->prefix . "users";
                $sql2 = "SELECT {$table_name2}.ID";
                $sql2 .= " FROM {$table_name2}";
                $sql2 .= " WHERE {$table_name2}.display_name LIKE %s";
                $sql2 .= " ORDER BY {$table_name2}.display_name";
                $sql2 = $wpdb->prepare($sql2, '%'.sanitize_text_field($request['name']).'%');
                $users = $wpdb->get_results($sql2);
                if (count($users) > 0) {
                    $user_str = array();
                    foreach ($users as $user) {
                        $user_str[] = $user->ID;
                    }
                    $user_strr = implode(',', $user_str);
                    $sql .= " AND `{$table_name}`.customer_id IN ({$user_strr})";
                } else {
                    return new WP_REST_Response(
                        [
                            "status" => "success",
                            "response" => [],
                        ],
                        200
                    );
                }
            }
            $sql .= " GROUP BY $table_name.`order_id` ORDER BY $table_name.`order_id` DESC LIMIT %d OFFSET %d";
            
            $args = [$user_id];
            if (isset($request["status"])) {
                $args[] = sanitize_text_field($request["status"]);
            }
            if (isset($request["search"])) {
                $args[] = '%'.sanitize_text_field($request["search"]).'%';
            }
            $args[] = $per_page;
            $args[] = $page;
            $sql = $wpdb->prepare($sql, $args);
            $items = $wpdb->get_results($sql);

            foreach ($items as $item) {
                $order = wc_get_order($item->order_id);
                if (is_bool($order)) {
                    continue;
                }

                $response = $api->prepare_item_for_response($order, $request);
                $order = $response->get_data();
                $count = count($order["line_items"]);
                $order["product_count"] = $count;

                $order = getCommissionOrderResponse($order, $user_id);

                $line_items = array();
                for ($i = 0; $i < $count; $i++) {
                    $product_id = absint(
                        $order["line_items"][$i]["product_id"]
                    );

                    $product = get_post($product_id);
                    $product_author = $product->post_author;
                    if (absint($product_author) != absint($user_id)) {
                        continue;
                    }
					$commission_data = get_post_meta($product_id, '_wcfmmp_commission', true);
					if(!empty($commission_data)){
						$order["line_items"][$i]['commission'] = $commission_data;	
					}
                    $image = wp_get_attachment_image_src(
                        get_post_thumbnail_id($product_id)
                    );
                    if (!is_null($image[0])) {
                        $order["line_items"][$i]["featured_image"] = $image[0];
                    }
                    $order_item = new WC_Order_Item_Product($order["line_items"][$i]["id"]);
                    $order["line_items"][$i]["meta"] = $order_item->get_meta_data();
                    if (is_plugin_active('wc-frontend-manager-delivery/wc-frontend-manager-delivery.php')) {
                        $table_name = $wpdb->prefix . "wcfm_delivery_orders";
                        $sql = "SELECT delivery_boy FROM `{$table_name}`";
                        $sql .= " WHERE 1=1";
                        $sql .= " AND product_id = %s";
                        $sql .= " AND order_id = %s";
                        $sql = $wpdb->prepare($sql, $product_id, $item->order_id);
                        $users = $wpdb->get_results($sql);

                        if (count($users) > 0) {
                            $user = get_userdata($users[0]->delivery_boy);
                            $order["line_items"][$i]['delivery_user'] = [
                                "id" => $user->ID,
                                "name" => $user->display_name,
                                "profile_picture" => $profile_pic,
                            ];
                        }
                    }
                    $line_items[] = $order["line_items"][$i];
                }
                $order["line_items"] = $line_items;
                $results[] = $order;
            }
        }
        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => $results,
            ],
            200
        );
    }

    public function flutter_get_sale_stats($user_id)
    {
        $id = $user_id;
        $price_decimal = get_option("woocommerce_price_num_decimals", 2);

        $sales_stats["gross_sales"]["last_month"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "last_month"),
            $price_decimal
        );
        $sales_stats["gross_sales"]["month"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "month"),
            $price_decimal
        );
        $sales_stats["gross_sales"]["year"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "year"),
            $price_decimal
        );
        $sales_stats["gross_sales"]["week_1"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "7day"),
            $price_decimal
        );
        $sales_stats["gross_sales"]["week_2"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "14day"),
            $price_decimal
        );
        $sales_stats["gross_sales"]["week_3"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "21day"),
            $price_decimal
        );
        $sales_stats["gross_sales"]["week_4"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "28day"),
            $price_decimal
        );
        $sales_stats["gross_sales"]["week_5"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "35day"),
            $price_decimal
        );
        $sales_stats["gross_sales"]["all"] = round(
            $this->wcfm_get_gross_sales_by_vendor($id, "all"),
            $price_decimal
        );
        if ($sales_stats["gross_sales"]["last_month"] != 0) {
            $profit_percentage = round(
                $sales_stats["gross_sales"]["month"] -
                $sales_stats["gross_sales"]["last_month"],
                2
            );
            $profit_percentage = round(
                (($profit_percentage /
                        $sales_stats["gross_sales"]["last_month"]) *
                    100) /
                100,
                2
            );
        } else {
            $profit_percentage = round(
                $sales_stats["gross_sales"]["month"] -
                $sales_stats["gross_sales"]["last_month"],
                2
            );
            $profit_percentage = round(
                (($profit_percentage / 1) * 100) / 100,
                2
            );
        }
        $sales_stats["gross_sales"]["profit_percentage"] = $profit_percentage;
        $sales_stats["earnings"]["last_month"] = round(
            $this->wcfm_get_commission_by_vendor($id, "last_month"),
            $price_decimal
        );
        $sales_stats["earnings"]["month"] = round(
            $this->wcfm_get_commission_by_vendor($id, "month"),
            $price_decimal
        );
        $sales_stats["earnings"]["year"] = round(
            $this->wcfm_get_commission_by_vendor($id, "year"),
            $price_decimal
        );
        $sales_stats["earnings"]["week_1"] = round(
            $this->wcfm_get_commission_by_vendor($id, "7day"),
            $price_decimal
        );
        $sales_stats["earnings"]["week_2"] = round(
            $this->wcfm_get_commission_by_vendor($id, "14day"),
            $price_decimal
        );
        $sales_stats["earnings"]["week_3"] = round(
            $this->wcfm_get_commission_by_vendor($id, "21day"),
            $price_decimal
        );
        $sales_stats["earnings"]["week_4"] = round(
            $this->wcfm_get_commission_by_vendor($id, "28day"),
            $price_decimal
        );
        $sales_stats["earnings"]["week_5"] = round(
            $this->wcfm_get_commission_by_vendor($id, "35day"),
            $price_decimal
        );
        $sales_stats["earnings"]["all"] = round(
            $this->wcfm_get_commission_by_vendor($id, "all"),
            $price_decimal
        );
        if ($sales_stats["earnings"]["last_month"] != 0) {
            $profit_percentage = round(
                $sales_stats["earnings"]["month"] -
                $sales_stats["earnings"]["last_month"],
                2
            );
            $profit_percentage = round(
                (($profit_percentage / $sales_stats["earnings"]["last_month"]) *
                    100) /
                100,
                2
            );
        } else {
            $profit_percentage = round(
                $sales_stats["earnings"]["month"] -
                $sales_stats["earnings"]["last_month"],
                2
            );
            $profit_percentage = round(
                (($profit_percentage / 1) * 100) / 100,
                2
            );
        }
        $sales_stats["earnings"]["profit_percentage"] = $profit_percentage;

        $sales_stats["currency"] = get_woocommerce_currency();

        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => $sales_stats,
            ],
            200
        );
    }

    public function flutter_update_order_status($request, $user_id)
    {
        global $WCFM;

        $order_id = sanitize_text_field($request['order_id']);
        $order_status = sanitize_text_field($request['order_status']);

        if (!is_numeric($order_id)) {
            return new WP_REST_Response(array(
                'status' => 'success',
                'response' => []
            ), 200);
        }

        $order = wc_get_order($order_id);
        $order->update_status($order_status, "", true);
        $shop_name = get_user_by("ID", $user_id)->display_name;

        $note =sanitize_text_field($request["customer_note"]);
        if (!empty($note)) {
            $order->add_order_note($note, true, true);
        }

        if (wcfm_is_vendor()) {
            $shop_name = wcfm_get_vendor_store(absint($user_id));
        }
        $wcfm_messages = sprintf(
            __(
                "Order status updated to <b>%s</b> by <b>%s</b>",
                "wc-frontend-manager"
            ),
            wc_get_order_status_name(str_replace("wc-", "", $order_status)),
            $shop_name
        );
        $is_customer_note = apply_filters(
            "wcfm_is_allow_order_update_note_for_customer",
            "1"
        );

        if (wcfm_is_vendor($user_id)) {
            add_filter(
                "woocommerce_new_order_note_data",
                [$WCFM->wcfm_marketplace, "wcfm_update_comment_vendor"],
                10,
                2
            );
        }
        $comment_id = $order->add_order_note($wcfm_messages, $is_customer_note);
        if (wcfm_is_vendor($user_id)) {
            add_comment_meta($comment_id, "_vendor_id", $user_id);
        }
        if (wcfm_is_vendor($user_id)) {
            remove_filter(
                "woocommerce_new_order_note_data",
                [$WCFM->wcfm_marketplace, "wcfm_update_comment_vendor"],
                10,
                2
            );
        }

        $wcfm_messages = sprintf(
            __(
                "<b>%s</b> order status updated to <b>%s</b> by <b>%s</b>",
                "wc-frontend-manager"
            ),
            '#<a target="_blank" class="wcfm_dashboard_item_title" href="' .
            get_wcfm_view_order_url($order_id) .
            '">' .
            $order->get_order_number() .
            "</a>",
            wc_get_order_status_name(str_replace("wc-", "", $order_status)),
            $shop_name
        );
        $WCFM->wcfm_notification->wcfm_send_direct_message(
            -2,
            0,
            1,
            0,
            $wcfm_messages,
            "status-update"
        );

        do_action("woocommerce_order_edit_status", $order_id, $order_status);
        do_action("wcfm_order_status_updated", $order_id, $order_status);

        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => $order->get_data(),
            ],
            200
        );
    }

    public function flutter_get_reviews($request, $user_id)
    {
        global $WCFM, $wpdb, $WCFMmp;

        $vendor_id = $user_id;

        $length = 10;
        $offset = 1;
        if (isset($request['page'])) {
            $offset = sanitize_text_field($request['page']);
            if(!is_numeric($offset)){
                $offset = 1;
            }
        }
        if (isset($request['per_page'])) {
            $length = sanitize_text_field($request['per_page']);
            if(!is_numeric($length)){
                $length = 10;
            }
        }
        $offset = ($offset - 1) * $length;

        $the_orderby = !empty($request["orderby"])
            ? sanitize_text_field($request["orderby"])
            : "ID";
        $the_order =
            !empty($request["order"]) && "asc" === $request["order"]
                ? "ASC"
                : "DESC";

        $status_filter = "";
        if (isset($request["status_type"]) && $request["status_type"] != "") {
            $status_filter = sanitize_text_field($request["status_type"]);
            if ($status_filter == "approved") {
                $status_filter = " AND `approved` = 1";
            } elseif ($status_filter == "pending") {
                $status_filter = " AND `approved` = 0";
            }
        }

        $reviews_vendor_filter = " AND `vendor_id` = %s";
        $sql = "SELECT COUNT(ID) from {$wpdb->prefix}wcfm_marketplace_reviews";
        $sql .= " WHERE 1=1";
        $sql .= $reviews_vendor_filter;
        $sql .= $status_filter;

        $sql = $wpdb->prepare($sql, $vendor_id);
        $wcfm_review_items = $wpdb->get_var($sql);
        if (!$wcfm_review_items) {
            $wcfm_review_items = 0;
        }

        $sql = "SELECT * from {$wpdb->prefix}wcfm_marketplace_reviews";
        $sql .= " WHERE 1=1";
        $sql .= $reviews_vendor_filter;
        $sql .= $status_filter;
        $sql .= " ORDER BY %s %s";
        $sql .= " LIMIT %d";
        $sql .= " OFFSET %d";

        $sql = $wpdb->prepare($sql, $vendor_id, $the_orderby, $the_order, $length, $offset);
        $wcfm_reviews_array = $wpdb->get_results($sql);
        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => $wcfm_reviews_array,
            ],
            200
        );
    }

    // Update review status
    function flutter_update_review($request)
    {
        global $WCFM, $WCFMmp, $wpdb;
        $reviewid = sanitize_text_field($request["id"]);
        $status = sanitize_text_field($request["status"]);

        if(!is_numeric($reviewid) || !is_numeric($status)){
            return;
        }

        $wcfm_review_categories = get_wcfm_marketplace_active_review_categories();

        if ($reviewid) {
            $sql = "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_reviews WHERE `ID`= %s";
            $sql = $wpdb->prepare($sql, $reviewid);
            $review_data = $wpdb->get_row($sql);

            $sql = "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta WHERE `type` = 'rating_category' AND `review_id`= %s ORDER BY ID ASC";
            $sql = $wpdb->prepare($sql, $reviewid);
            $review_meta = $wpdb->get_results($sql);
            if (
                $review_data &&
                !empty($review_data) &&
                is_object($review_data)
            ) {
                if ($status) {
                    // On Approve
                    $total_review_count = get_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_total_review_count",
                        true
                    );
                    if (!$total_review_count) {
                        $total_review_count = 0;
                    } else {
                        $total_review_count = absint($total_review_count);
                    }
                    $total_review_count++;
                    update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_total_review_count",
                        $total_review_count
                    );

                    $total_review_rating = get_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_total_review_rating",
                        true
                    );
                    if (!$total_review_rating) {
                        $total_review_rating = 0;
                    } else {
                        $total_review_rating = (float)$total_review_rating;
                    }
                    $total_review_rating += (float)$review_data->review_rating;
                    update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_total_review_rating",
                        $total_review_rating
                    );

                    $avg_review_rating =
                        $total_review_rating / $total_review_count;
                    update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_avg_review_rating",
                        $avg_review_rating
                    );

                    $wcfm_store_review_categories = [];
                    if (!empty($review_meta)) {
                        foreach ($review_meta as $review_meta_cat) {
                            $wcfm_store_review_categories[] =
                                $review_meta_cat->value;
                        }
                    }

                    $category_review_rating = get_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_category_review_rating",
                        true
                    );
                    if (!$category_review_rating) {
                        $category_review_rating = [];
                    }
                    foreach (
                        $wcfm_review_categories
                        as $wcfm_review_cat_key => $wcfm_review_category
                    ) {
                        if (
                            isset(
                                $wcfm_store_review_categories[$wcfm_review_cat_key]
                            )
                        ) {
                            $total_category_review_rating = 0;
                            $avg_category_review_rating = 0;
                            if (
                                $category_review_rating &&
                                !empty($category_review_rating) &&
                                isset(
                                    $category_review_rating[$wcfm_review_cat_key]
                                )
                            ) {
                                $total_category_review_rating =
                                    $category_review_rating[$wcfm_review_cat_key]["total"];
                                $avg_category_review_rating =
                                    $category_review_rating[$wcfm_review_cat_key]["avg"];
                            }
                            $total_category_review_rating +=
                                (float)$wcfm_store_review_categories[$wcfm_review_cat_key];
                            $avg_category_review_rating =
                                $total_category_review_rating /
                                $total_review_count;
                            $category_review_rating[$wcfm_review_cat_key]["total"] = $total_category_review_rating;
                            $category_review_rating[$wcfm_review_cat_key]["avg"] = $avg_category_review_rating;
                        } else {
                            $category_review_rating[$wcfm_review_cat_key]["total"] = 0;
                            $category_review_rating[$wcfm_review_cat_key]["avg"] = 0;
                        }
                    }
                    $category_review_rating = update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_category_review_rating",
                        $category_review_rating
                    );

                    update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_last_author_id",
                        $review_data->author_id
                    );
                    update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_last_author_name",
                        $review_data->author_name
                    );

                    $wpdb->update(
                        "{$wpdb->prefix}wcfm_marketplace_reviews",
                        [
                            "approved" => 1,
                        ],
                        [
                            "ID" => $reviewid,
                        ],
                        ["%d"],
                        ["%d"]
                    );
                } else {
                    // On UnApprove
                    $total_review_count = get_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_total_review_count",
                        true
                    );
                    if (!$total_review_count) {
                        $total_review_count = 0;
                    } else {
                        $total_review_count = absint($total_review_count);
                    }
                    if ($total_review_count) {
                        $total_review_count--;
                    }
                    update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_total_review_count",
                        $total_review_count
                    );

                    $total_review_rating = get_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_total_review_rating",
                        true
                    );
                    if (!$total_review_rating) {
                        $total_review_rating = 0;
                    } else {
                        $total_review_rating = (float)$total_review_rating;
                    }
                    if ($total_review_rating) {
                        $total_review_rating -=
                            (float)$review_data->review_rating;
                    }
                    update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_total_review_rating",
                        $total_review_rating
                    );

                    $avg_review_rating = 0;
                    if ($total_review_rating && $total_review_count) {
                        $avg_review_rating =
                            $total_review_rating / $total_review_count;
                    }
                    update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_avg_review_rating",
                        $avg_review_rating
                    );

                    $wcfm_store_review_categories = [];
                    if (!empty($review_meta)) {
                        foreach ($review_meta as $review_meta_cat) {
                            $wcfm_store_review_categories[] =
                                $review_meta_cat->value;
                        }
                    }

                    $category_review_rating = get_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_category_review_rating",
                        true
                    );
                    if (!$category_review_rating) {
                        $category_review_rating = [];
                    }
                    foreach (
                        $wcfm_review_categories
                        as $wcfm_review_cat_key => $wcfm_review_category
                    ) {
                        if (
                            isset(
                                $wcfm_store_review_categories[$wcfm_review_cat_key]
                            )
                        ) {
                            $total_category_review_rating = 0;
                            $avg_category_review_rating = 0;
                            if (
                                $category_review_rating &&
                                !empty($category_review_rating) &&
                                isset(
                                    $category_review_rating[$wcfm_review_cat_key]
                                )
                            ) {
                                $total_category_review_rating =
                                    $category_review_rating[$wcfm_review_cat_key]["total"];
                                $avg_category_review_rating =
                                    $category_review_rating[$wcfm_review_cat_key]["avg"];
                            }
                            if ($total_category_review_rating) {
                                $total_category_review_rating -=
                                    (float)$wcfm_store_review_categories[$wcfm_review_cat_key];
                            }
                            if (
                                $total_category_review_rating &&
                                $total_review_count
                            ) {
                                $avg_category_review_rating =
                                    $total_category_review_rating /
                                    $total_review_count;
                            }
                            $category_review_rating[$wcfm_review_cat_key]["total"] = $total_category_review_rating;
                            $category_review_rating[$wcfm_review_cat_key]["avg"] = $avg_category_review_rating;
                        } else {
                            $category_review_rating[$wcfm_review_cat_key]["total"] = 0;
                            $category_review_rating[$wcfm_review_cat_key]["avg"] = 0;
                        }
                    }
                    $category_review_rating = update_user_meta(
                        $review_data->vendor_id,
                        "_wcfmmp_category_review_rating",
                        $category_review_rating
                    );

                    $wpdb->update(
                        "{$wpdb->prefix}wcfm_marketplace_reviews",
                        [
                            "approved" => 0,
                        ],
                        [
                            "ID" => $reviewid,
                        ],
                        ["%d"],
                        ["%d"]
                    );
                }
            }
        }
    }

    /* GET WCFM SALE STATS FUNCTIONS. CUSTOM BY TOAN 04/11/2020 */

    function wcfm_query_time_range_filter(
        $sql,
        $time,
        $interval = "7day",
        $start_date = "",
        $end_date = "",
        $table_handler = "commission"
    )
    {
        switch ($interval) {
            case "year":
                $sql .= " AND YEAR( {$table_handler}.{$time} ) = YEAR( CURDATE() )";
                break;
            case "last_month":
                $sql .= " AND MONTH( {$table_handler}.{$time} ) = MONTH( NOW() ) - 1";
                break;
            case "month":
                $sql .= " AND MONTH( {$table_handler}.{$time} ) = MONTH( NOW() )";
                break;
            case "all":
                break;
            case "7day":
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 7 DAY ) AND NOW()";
                break;
            case "14day":
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 14 DAY ) AND DATE_SUB( NOW(), INTERVAL 7 DAY )";
                break;
            case "21day":
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 21 DAY ) AND DATE_SUB( NOW(), INTERVAL 14 DAY )";
                break;
            case "28day":
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 28 DAY ) AND DATE_SUB( NOW(), INTERVAL 21 DAY )";
                break;
            case "35day":
                $sql .= " AND DATE( {$table_handler}.{$time} ) BETWEEN DATE_SUB( NOW(), INTERVAL 35 DAY ) AND DATE_SUB( NOW(), INTERVAL 28 DAY )";
                break;
            case "default":
        }

        return $sql;
    }

    function wcfm_get_gross_sales_by_vendor(
        $vendor_id = "",
        $interval = "7day",
        $is_paid = false,
        $order_id = 0,
        $filter_date_form = "",
        $filter_date_to = ""
    )
    {
        global $WCFM, $wpdb, $WCMp, $WCFMmp;

        if ($vendor_id) {
            $vendor_id = absint($vendor_id);
        }

        $gross_sales = 0;

        $marketplece = wcfm_is_marketplace();
        if ($marketplece == "wcvendors") {
            $sql = "SELECT order_id, GROUP_CONCAT(product_id) product_ids, SUM( commission.total_shipping ) AS total_shipping FROM {$wpdb->prefix}pv_commission AS commission";
            $sql .= " WHERE 1=1";
            if ($vendor_id) {
                $sql .= " AND `vendor_id` = %s";
            }
            if ($order_id) {
                $sql .= " AND `order_id` = %s";
            } else {
                if ($is_paid) {
                    $sql .= " AND commission.status = 'paid'";
                }
                $sql = $this->wcfm_query_time_range_filter(
                    $sql,
                    "time",
                    $interval,
                    $filter_date_form,
                    $filter_date_to
                );
            }
            $sql .= " GROUP BY commission.order_id";

            $args = array();
            if ($vendor_id) {
                $args[] = $vendor_id;
            }
            if ($order_id) {
                $args[] = $order_id;
            }
            $sql = $wpdb->prepare($sql, $args);
            $gross_sales_whole_week = $wpdb->get_results($sql);
            if (!empty($gross_sales_whole_week)) {
                foreach ($gross_sales_whole_week as $net_sale_whole_week) {
                    if ($net_sale_whole_week->order_id) {
                        $order_post_title = get_the_title(
                            $net_sale_whole_week->order_id
                        );
                        if (!$order_post_title) {
                            continue;
                        }
                        try {
                            $order = wc_get_order(
                                $net_sale_whole_week->order_id
                            );
                            $line_items = $order->get_items("line_item");
                            $valid_items = (array)($order_item_ids = explode(
                                ",",
                                $net_sale_whole_week->product_ids
                            ));

                            foreach ($line_items as $key => $line_item) {
                                if ($line_item->get_product_id() == 0) {
                                    $_product_id = wc_get_order_item_meta(
                                        $key,
                                        "_product_id",
                                        true
                                    );
                                    $_variation_id = wc_get_order_item_meta(
                                        $key,
                                        "_variation_id",
                                        true
                                    );
                                    if (
                                        in_array($_product_id, $valid_items) ||
                                        in_array($_variation_id, $valid_items)
                                    ) {
                                        $gross_sales += (float)sanitize_text_field(
                                            $line_item->get_total()
                                        );
                                        if (
                                            version_compare(
                                                WCV_VERSION,
                                                "2.0.0",
                                                "<"
                                            )
                                        ) {
                                            if (
                                                WC_Vendors::$pv_options->get_option(
                                                    "give_tax"
                                                )
                                            ) {
                                                $gross_sales += (float)sanitize_text_field(
                                                    $line_item->get_total_tax()
                                                );
                                            }
                                        } else {
                                            if (
                                                get_option(
                                                    "wcvendors_vendor_give_taxes"
                                                )
                                            ) {
                                                $gross_sales += (float)sanitize_text_field(
                                                    $line_item->get_total_tax()
                                                );
                                            }
                                        }
                                    }
                                } elseif (
                                    in_array(
                                        $line_item->get_variation_id(),
                                        $valid_items
                                    ) ||
                                    in_array(
                                        $line_item->get_product_id(),
                                        $valid_items
                                    )
                                ) {
                                    $gross_sales += (float)sanitize_text_field(
                                        $line_item->get_total()
                                    );
                                    if (
                                        version_compare(
                                            WCV_VERSION,
                                            "2.0.0",
                                            "<"
                                        )
                                    ) {
                                        if (
                                            WC_Vendors::$pv_options->get_option(
                                                "give_tax"
                                            )
                                        ) {
                                            $gross_sales += (float)sanitize_text_field(
                                                $line_item->get_total_tax()
                                            );
                                        }
                                    } else {
                                        if (
                                            get_option(
                                                "wcvendors_vendor_give_taxes"
                                            )
                                        ) {
                                            $gross_sales += (float)sanitize_text_field(
                                                $line_item->get_total_tax()
                                            );
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                    if (version_compare(WCV_VERSION, "2.0.0", "<")) {
                        if (
                            WC_Vendors::$pv_options->get_option("give_shipping")
                        ) {
                            $gross_sales +=
                                (float)$net_sale_whole_week->total_shipping;
                        }
                    } else {
                        if (get_option("wcvendors_vendor_give_shipping")) {
                            $gross_sales +=
                                (float)$net_sale_whole_week->total_shipping;
                        }
                    }
                }
            }
        } elseif ($marketplece == "wcmarketplace") {
            $sql = "SELECT order_item_id, shipping, shipping_tax_amount FROM {$wpdb->prefix}wcmp_vendor_orders AS commission";
            $sql .= " WHERE 1=1";
            if ($vendor_id) {
                $sql .= " AND `vendor_id` = %s";
            }
            if ($order_id) {
                $sql .= " AND `order_id` = %s";
            } else {
                $sql .=
                    " AND `line_item_type` = 'product' AND `commission_id` != 0 AND `commission_id` != '' AND `is_trashed` != 1";
                if ($is_paid) {
                    $sql .= " AND commission.commission_status = 'paid'";
                    $sql = $this->wcfm_query_time_range_filter(
                        $sql,
                        "commission_paid_date",
                        $interval,
                        $filter_date_form,
                        $filter_date_to
                    );
                } else {
                    $sql = $this->wcfm_query_time_range_filter(
                        $sql,
                        "created",
                        $interval,
                        $filter_date_form,
                        $filter_date_to
                    );
                }
            }

            $args = array();
            if ($vendor_id) {
                $args[] = $vendor_id;
            }
            if ($order_id) {
                $args[] = $order_id;
            }
            $sql = $wpdb->prepare($sql, $args);
            $gross_sales_whole_week = $wpdb->get_results($sql);
            if (!empty($gross_sales_whole_week)) {
                foreach ($gross_sales_whole_week as $net_sale_whole_week) {
                    if ($net_sale_whole_week->order_item_id) {
                        try {
                            $line_item = new WC_Order_Item_Product(
                                $net_sale_whole_week->order_item_id
                            );
                            $gross_sales += (float)sanitize_text_field(
                                $line_item->get_total()
                            );
                            if (
                                $WCMp->vendor_caps->vendor_payment_settings(
                                    "give_tax"
                                )
                            ) {
                                $gross_sales += (float)sanitize_text_field(
                                    $line_item->get_total_tax()
                                );
                                $gross_sales +=
                                    (float)$net_sale_whole_week->shipping_tax_amount;
                            }
                            if (
                                $WCMp->vendor_caps->vendor_payment_settings(
                                    "give_shipping"
                                )
                            ) {
                                $gross_sales +=
                                    (float)$net_sale_whole_week->shipping;
                            }
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                }
            }
        } elseif ($marketplece == "wcpvendors") {
            $sql =
                "SELECT SUM( commission.product_amount ) AS total_product_amount, SUM( commission.product_shipping_amount ) AS product_shipping_amount, SUM( commission.product_shipping_tax_amount ) AS product_shipping_tax_amount, SUM( commission.product_tax_amount ) AS product_tax_amount FROM " .
                WC_PRODUCT_VENDORS_COMMISSION_TABLE .
                " AS commission";
            $sql .= " WHERE 1=1";
            if ($vendor_id) {
                $sql .= " AND commission.vendor_id = %s";
            }
            if ($order_id) {
                $sql .= " AND `order_id` = %s";
            } else {
                if ($is_paid) {
                    $sql .= " AND commission.commission_status = 'paid'";
                    $sql = $this->wcfm_query_time_range_filter(
                        $sql,
                        "paid_date",
                        $interval,
                        $filter_date_form,
                        $filter_date_to
                    );
                } else {
                    $sql = $this->wcfm_query_time_range_filter(
                        $sql,
                        "order_date",
                        $interval,
                        $filter_date_form,
                        $filter_date_to
                    );
                }
            }

            $args = array();
            if ($vendor_id) {
                $args[] = $vendor_id;
            }
            if ($order_id) {
                $args[] = $order_id;
            }
            $sql = $wpdb->prepare($sql, $args);
            $total_sales = $wpdb->get_results($sql);
            if (!empty($total_sales)) {
                foreach ($total_sales as $total_sale) {
                    $gross_sales =
                        $total_sale->total_product_amount +
                        $total_sale->product_shipping_amount +
                        $total_sale->product_shipping_tax_amount +
                        $total_sale->product_tax_amount;
                }
            }
        } elseif ($marketplece == "dokan") {
            $sql = "SELECT SUM( commission.order_total ) AS total_order_amount FROM {$wpdb->prefix}dokan_orders AS commission LEFT JOIN {$wpdb->posts} p ON commission.order_id = p.ID";
            $sql .= " WHERE 1=1";
            if ($vendor_id) {
                $sql .= " AND commission.seller_id = %s";
            }
            if ($order_id) {
                $sql .= " AND `commission.order_id` = %s";
            } else {
                $status = dokan_withdraw_get_active_order_status_in_comma();
                $sql .= " AND commission.order_status IN ({$status})";
                $sql = $this->wcfm_query_time_range_filter(
                    $sql,
                    "post_date",
                    $interval,
                    "",
                    "",
                    "p"
                );
            }

            $args = array();
            if ($vendor_id) {
                $args[] = $vendor_id;
            }
            if ($order_id) {
                $args[] = $order_id;
            }
            $sql = $wpdb->prepare($sql, $args);
            $total_sales = $wpdb->get_results($sql);
            if (!empty($total_sales)) {
                foreach ($total_sales as $total_sale) {
                    $gross_sales = $total_sale->total_order_amount;
                }
            }
        } elseif ($marketplece == "wcfmmarketplace") {
            $sql = "SELECT ID, order_id, item_id, item_total, item_sub_total, refunded_amount, shipping, tax, shipping_tax_amount FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
            $sql .= " WHERE 1=1";
            if ($vendor_id) {
                $sql .= " AND `vendor_id` = %s";
            }
            if ($order_id) {
                $sql .= " AND `order_id` = %s";
                //$sql .= " AND `is_refunded` != 1";
            } else {
                $sql .= apply_filters(
                    "wcfm_order_status_condition",
                    "",
                    "commission"
                );
                $sql .= " AND `is_trashed` = 0";
                if ($is_paid) {
                    $sql .= " AND commission.withdraw_status = 'completed'";
                    $sql = $this->wcfm_query_time_range_filter(
                        $sql,
                        "commission_paid_date",
                        $interval,
                        $filter_date_form,
                        $filter_date_to
                    );
                } else {
                    $sql = $this->wcfm_query_time_range_filter(
                        $sql,
                        "created",
                        $interval,
                        $filter_date_form,
                        $filter_date_to
                    );
                }
            }

            $args = array();
            if ($vendor_id) {
                $args[] = $vendor_id;
            }
            if ($order_id) {
                $args[] = $order_id;
            }
            $sql = $wpdb->prepare($sql, $args);
            $gross_sales_whole_week = $wpdb->get_results($sql);
            $gross_commission_ids = [];
            $gross_total_refund_amount = 0;
            if (!empty($gross_sales_whole_week)) {
                foreach ($gross_sales_whole_week as $net_sale_whole_week) {
                    $gross_commission_ids[] = $net_sale_whole_week->ID;
                    $gross_total_refund_amount += (float)sanitize_text_field(
                        $net_sale_whole_week->refunded_amount
                    );
                }

                if (!empty($gross_commission_ids)) {
                    try {
                        if (
                            apply_filters(
                                "wcfmmmp_gross_sales_respect_setting",
                                true
                            )
                        ) {
                            $gross_sales = (float)$WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta_sum(
                                $gross_commission_ids,
                                "gross_total"
                            );
                        } else {
                            $gross_sales = (float)$WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta_sum(
                                $gross_commission_ids,
                                "gross_sales_total"
                            );
                        }

                        // Deduct Refunded Amount
                        $gross_sales -= (float)$gross_total_refund_amount;
                    } catch (Exception $e) {
                        //continue;
                    }
                }
            }
        }

        if (!$gross_sales) {
            $gross_sales = 0;
        }

        return $gross_sales;
    }

    /**
     * Total commission paid by Admin
     */
    function wcfm_get_commission_by_vendor(
        $vendor_id = "",
        $interval = "7day",
        $is_paid = false,
        $order_id = 0,
        $filter_date_form = "",
        $filter_date_to = ""
    )
    {
        global $WCFM, $wpdb, $WCMp;

        if ($vendor_id) {
            $vendor_id = absint($vendor_id);
        }

        $commission = 0;

        $marketplece = wcfm_is_marketplace();
        if ($marketplece == "wcvendors") {
            $commission_table = "pv_commission";
            $total_due = "total_due";
            $total_shipping = "total_shipping";
            $tax = "tax";
            $shipping_tax = "tax";
            $status = "status";
            $time = "time";
            $vendor_handler = "vendor_id";
            $table_handler = "commission";
        } elseif ($marketplece == "wcmarketplace") {
            $commission_table = "wcmp_vendor_orders";
            $total_due = "commission_amount";
            $total_shipping = "shipping";
            $tax = "tax";
            $shipping_tax = "shipping_tax_amount";
            $status = "commission_status";
            $vendor_handler = "vendor_id";
            $table_handler = "commission";
            if ($is_paid) {
                $time = "commission_paid_date";
            } else {
                $time = "created";
            }
        } elseif ($marketplece == "wcpvendors") {
            $commission_table = "wcpv_commissions";
            $total_due = "total_commission_amount";
            $total_shipping = "product_shipping_amount";
            $tax = "product_tax_amount";
            $shipping_tax = "product_shipping_tax_amount";
            $status = "commission_status";
            $vendor_handler = "vendor_id";
            $table_handler = "commission";
            if ($is_paid) {
                $time = "paid_date";
            } else {
                $time = "order_date";
            }
        } elseif ($marketplece == "dokan") {
            $order_status = apply_filters("wcfm_dokan_allowed_order_status", [
                "completed",
                "processing",
                "on-hold",
            ]);
            $commission_table = "dokan_orders";
            $total_due = "net_amount";
            $time = "post_date";
            $vendor_handler = "seller_id";
            $table_handler = "p";
            if ($is_paid) {
                $sql = "SELECT SUM( withdraw.amount ) AS amount FROM {$wpdb->prefix}dokan_withdraw AS withdraw";
                $sql .= " WHERE 1=1";
                if ($vendor_id) {
                    $sql .= " AND withdraw.user_id = %s";
                }
                $sql .= " AND withdraw.status = 1";
                $sql = $this->wcfm_query_time_range_filter(
                    $sql,
                    "date",
                    $interval,
                    $filter_date_form,
                    $filter_date_to,
                    "withdraw"
                );
                if($vendor_id){
                    $sql = $wpdb->prepare($sql, $vendor_id);
                }else{
                    $sql = $wpdb->prepare($sql);
                }
                $total_commissions = $wpdb->get_results($sql);
                $commission = 0;
                if (!empty($total_commissions)) {
                    foreach ($total_commissions as $total_commission) {
                        $commission += $total_commission->amount;
                    }
                }
                if (!$commission) {
                    $commission = 0;
                }
                return $commission;
            }
        } elseif ($marketplece == "wcfmmarketplace") {
            $commission_table = "wcfm_marketplace_orders";
            $total_due = "total_commission";
            $total_shipping = "shipping";
            $tax = "tax";
            $shipping_tax = "shipping_tax_amount";
            $status = "withdraw_status";
            $vendor_handler = "vendor_id";
            $table_handler = "commission";
            if ($is_paid) {
                $time = "commission_paid_date";
            } else {
                $time = "created";
            }
        }

        if ($marketplece == "dokan") {
            $order_status = apply_filters("wcfm_dokan_allowed_order_status", [
                "completed",
                "processing",
                "on-hold",
            ]);
            $sql = "SELECT SUM( commission.{$total_due} ) AS total_due FROM {$wpdb->prefix}{$commission_table} AS commission LEFT JOIN {$wpdb->posts} p ON commission.order_id = p.ID";
        } else {
            $sql = "SELECT SUM( commission.{$total_due} ) AS total_due, SUM( commission.{$total_shipping} ) AS total_shipping, SUM( commission.{$tax} ) AS tax, SUM( commission.{$shipping_tax} ) AS shipping_tax FROM {$wpdb->prefix}{$commission_table} AS commission";
        }

        $sql .= " WHERE 1=1";
        if ($vendor_id) {
            $sql .= " AND commission.{$vendor_handler} = %s";
        }
        if ($is_paid) {
            $sql .= " AND (commission.{$status} = 'paid' OR commission.{$status} = 'completed')";
        }
        if ($marketplece == "wcmarketplace") {
            $sql .=
                " AND commission.commission_id != 0 AND commission.commission_id != '' AND `is_trashed` != 1";
        }
        if ($marketplece == "dokan") {
            $status = dokan_withdraw_get_active_order_status_in_comma();
            $sql .= " AND commission.order_status IN ({$status})";
        }
        if ($marketplece == "wcfmmarketplace") {
            if ($order_id) {
                $sql .= " AND `order_id` = %s";
            } else {
                $sql .= apply_filters(
                    "wcfm_order_status_condition",
                    "",
                    "commission"
                );
                $sql .= " AND `is_refunded` = 0 AND `is_trashed` = 0";
            }
        }
        if (!$order_id) {
            $sql = $this->wcfm_query_time_range_filter(
                $sql,
                $time,
                $interval,
                $filter_date_form,
                $filter_date_to,
                $table_handler
            );
        }
        $args = array();
        if ($vendor_id) {
            $args[] = $vendor_id;
        }
        if ($order_id) {
            $args[] = $order_id;
        }
        $sql = $wpdb->prepare($sql, $args);
        $total_commissions = $wpdb->get_results($sql);
        $commission = 0;
        if (!empty($total_commissions)) {
            foreach ($total_commissions as $total_commission) {
                $commission += $total_commission->total_due;
                if ($marketplece == "wcvendors") {
                    if (version_compare(WCV_VERSION, "2.0.0", "<")) {
                        if (WC_Vendors::$pv_options->get_option("give_tax")) {
                            $commission += $total_commission->total_shipping;
                        }
                        if (
                            WC_Vendors::$pv_options->get_option("give_shipping")
                        ) {
                            $commission += $total_commission->tax;
                        }
                    } else {
                        if (get_option("wcvendors_vendor_give_taxes")) {
                            $commission += $total_commission->total_shipping;
                        }
                        if (get_option("wcvendors_vendor_give_shipping")) {
                            $commission += $total_commission->tax;
                        }
                    }
                } elseif ($marketplece == "wcmarketplace") {
                    if (
                        $WCMp->vendor_caps->vendor_payment_settings(
                            "give_shipping"
                        )
                    ) {
                        $commission +=
                            $total_commission->total_shipping == "NAN"
                                ? 0
                                : $total_commission->total_shipping;
                    }
                    if (
                        $WCMp->vendor_caps->vendor_payment_settings("give_tax")
                    ) {
                        $commission +=
                            $total_commission->tax == "NAN"
                                ? 0
                                : $total_commission->tax;
                        $commission +=
                            $total_commission->shipping_tax == "NAN"
                                ? 0
                                : $total_commission->shipping_tax;
                    }
                }
            }
        }
        if (!$commission) {
            $commission = 0;
        }

        return $commission;
    }

    /* GET WCFM SALE STATS FUNCTIONS. CUSTOM BY TOAN 04/11/2020 */

    /* GET NOTIFICATIONS */
    function get_notification_by_vendor($request, $user_id)
    {
        global $WCFM, $wpdb;
        $wcfm_messages = array();
        if (isset($request["per_page"]) && $request["per_page"]) {
            $limit = sanitize_text_field($request['per_page']);
            $offset = sanitize_text_field($request['page']);
            if(!is_numeric($offset)){
                $offset = 1;
            }
            if(!is_numeric($limit)){
                $limit = 10;
            }
            $offset = ($offset - 1) * $limit;
            $message_to = apply_filters("wcfm_message_author", $user_id);

            $sql =
                "SELECT wcfm_messages.* FROM " .
                $wpdb->prefix .
                "wcfm_messages AS wcfm_messages";
            $vendor_filter = " WHERE ( `author_id` = %s OR `message_to` = -1 OR `message_to` = %s )";
            $sql .= $vendor_filter;
            $message_status_filter = " AND NOT EXISTS (SELECT * FROM {$wpdb->prefix}wcfm_messages_modifier as wcfm_messages_modifier_2 WHERE wcfm_messages.ID = wcfm_messages_modifier_2.message AND wcfm_messages_modifier_2.read_by=%s)";
            $sql .= $message_status_filter;
            $sql .= " ORDER BY wcfm_messages.`ID` DESC";
            $sql .= " LIMIT %d";
            $sql .= " OFFSET %d";
            $sql = $wpdb->prepare($sql, $message_to, $message_to, $message_to, $limit, $offset);
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
        }
        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => $wcfm_messages,
            ],
            200
        );
    }

    /// CREATE ///
    public function vendor_admin_create_product($request, $user_id)
    {
        $user = get_userdata($user_id);
        $isSeller = in_array("wcfm_vendor", $user->roles);

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

            if ($product->get_type() != $request["type"]) {
                // Get the correct product classname from the new product type
                $product_classname = WC_Product_Factory::get_product_classname(
                    $product->get_id(),
                    $type
                );

                // Get the new product object from the correct classname
                $product = new $product_classname($product->get_id());
                $product->save();
            }
            if (isset($featured_image)) {
                if (!empty($featured_image)) {
                    if ($this->http_check($featured_image)) {
                        $featured_image_id = $this->find_image_id(
                            $featured_image
                        );
                        $product->set_image_id($featured_image_id);
                    } else {
                        $featured_image_id = upload_image_from_mobile(
                            $featured_image,
                            $count,
                            $user_id
                        );
                        $product->set_image_id($featured_image_id);
                        $count = $count + 1;
                    }
                } else {
                    $product->set_image_id("");
                }
            }

            if (isset($product_images)) {
                $product_images_array = array_filter(
                    explode(",", $product_images)
                );
                $img_array = [];

                foreach ($product_images_array as $p_img) {
                    if (!empty($p_img)) {
                        if ($this->http_check($p_img)) {
                            $img_id = $this->find_image_id($p_img);
                            array_push($img_array, $img_id);
                        } else {
                            $img_id = upload_image_from_mobile(
                                $p_img,
                                $count,
                                $user_id
                            );
                            array_push($img_array, $img_id);
                            $count = $count + 1;
                        }
                    }
                }
                $product->set_gallery_image_ids($img_array);
            }

            if (isset($tags)) {
                $tags = array_filter(explode(",", $tags));
                wp_set_object_terms($post_id, $tags, "product_tag");
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
                if (isset($request["sku"])) {
                    $product->set_sku(wc_clean($request["sku"]));
                }

                // Sales and prices.
                if (
                    in_array(
                        $product->get_type(),
                        ["variable", "grouped"],
                        true
                    )
                ) {
                    $product->set_regular_price("");
                    $product->set_sale_price("");
                    $product->set_date_on_sale_to("");
                    $product->set_date_on_sale_from("");
                    $product->set_price("");
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
                if ("yes" === get_option("woocommerce_manage_stock")) {
                    // Manage stock.
                    if (isset($manage_stock)) {
                        $product->set_manage_stock($manage_stock);
                    }

                    // Backorders.
                    if (isset($backorders)) {
                        $product->set_backorders($backorders);
                    }

                    if ($product->is_type("grouped")) {
                        $product->set_manage_stock("no");
                        $product->set_backorders("no");
                        $product->set_stock_quantity("");
                        $product->set_stock_status($stock_status);
                    } elseif ($product->is_type("external")) {
                        $product->set_manage_stock("no");
                        $product->set_backorders("no");
                        $product->set_stock_quantity("");
                        $product->set_stock_status("instock");
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
                        $product->set_manage_stock("no");
                        $product->set_stock_quantity("");
                        $product->set_stock_status($stock_status);
                    }
                } elseif (!$product->is_type("variable")) {
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
                    }
                }


                //Description
                $product->set_short_description($short_description);
                $product->set_description($description);
                $attribute_json = json_decode($productAttributes, true);
                $pro_attributes = [];
                foreach ($attribute_json as $key => $value) {
                    if ($value["isActive"]) {
                        $attribute_name = strtolower($value["slug"]);
                        if ($value["default"]) {
                            $attribute_name = strtolower(
                                "pa_" . $value["slug"]
                            );
                        }
                        $attribute_id = wc_attribute_taxonomy_id_by_name(
                            $attribute_name
                        );
                        $attribute = new WC_Product_Attribute();
                        $attribute->set_id($attribute_id);
                        $attribute->set_name(wc_clean($attribute_name));
                        $options = $value["options"];
                        $attribute->set_options($options);
                        $attribute->set_visible($value["visible"]);
                        $attribute->set_variation($value["variation"]);
                        $pro_attributes[] = $attribute;
                    }
                }

                $product->set_props([
                    "attributes" => $pro_attributes,
                ]);
                if (is_wp_error($product)) {
                    return $this->sendError("request_failed", "Bad data", 400);
                }

                $product->save();

                if ($product->get_type() == "variable") {
                    $variations_arr = json_decode($variations, true);
                    foreach ($variations_arr as $variation) {
                        // Creating the product variation
                        $variation_post = [
                            "post_title" => $product->get_title(),
                            "post_name" =>
                                "product-" . $product->get_id() . "-variation",
                            "post_status" => "publish",
                            "post_parent" => $product->get_id(),
                            "post_type" => "product_variation",
                            "guid" => $product->get_permalink(),
                        ];
                        $variation_id = wp_insert_post($variation_post);
                        foreach ($variation["slugs"] as $key => $value) {
                            $variationAttrArr[$key] = strtolower(
                                strval($value)
                            );
                        }
                        $variationProduct = new WC_Product_Variation(
                            $variation_id
                        );
                        $variationProduct->set_regular_price(
                            $variation["display_regular_price"]
                        );
                        $variationProduct->set_sale_price(
                            $variation["display_price"]
                        );
                        $variationProduct->set_stock_quantity(
                            $variation["max_qty"]
                        );
                        $variationProduct->set_attributes($variationAttrArr);
                        $variationProduct->set_manage_stock(
                            boolval($variation["manage_stock"])
                        );
                        $variationProduct->set_status(
                            $variation["variation_is_active"]
                                ? "publish"
                                : "private"
                        );
                        $variationProduct->save();
                    }
                }

                wp_update_post([
                    "ID" => $product->get_id(),
                    "post_author" => $user_id,
                ]);
                //print_r($product);
                $image_arr = [];
                $p = $product->get_data();
                foreach (array_filter($p["gallery_image_ids"]) as $img) {
                    $image = wp_get_attachment_image_src($img, "full");

                    if (!is_null($image[0])) {
                        $image_arr[] = $image[0];
                    }
                }
                $p["description"] = strip_tags($p["description"]);
                $p["short_description"] = strip_tags($p["short_description"]);
                $p["images"] = $image_arr;
                $image = wp_get_attachment_image_src($p["image_id"], "full");
                if (!is_null($image[0])) {
                    $p["featured_image"] = $image[0];
                }
                $p["type"] = $product->get_type();
                $p["on_sale"] = $product->is_on_sale();
                if ($product->get_type() == "variable") {
                    $query = [
                        "post_parent" => $product->get_id(),
                        "post_status" => ["publish", "private"],
                        "post_type" => ["product_variation"],
                        "posts_per_page" => -1,
                    ];

                    $wc_query = new WP_Query($query);
                    while ($wc_query->have_posts()) {
                        $wc_query->next_post();
                        $result[] = $wc_query->post;
                    }

                    foreach ($result as $variation) {
                        $p_varation = new WC_Product_Variation($variation->ID);
                        $dataVariation = array();
                        $dataVariation["variation_id"] = $p_varation->get_id();
                        $dataVariation["max_qty"] = $p_varation->get_stock_quantity();
                        $dataVariation["variation_is_active"] =
                            $p_varation->get_status() == "publish";
                        $dataVariation["display_price"] = $p_varation->get_sale_price();
                        $dataVariation["display_regular_price"] = $p_varation->get_regular_price();
                        $dataVariation["attributes"] = $p_varation->get_attributes();
                        $dataVariation["manage_stock"] = $p_varation->get_manage_stock();
                        $p["variable_products"][] = $dataVariation;
                    }
                }
                return new WP_REST_Response(
                    [
                        "status" => "success",
                        "response" => $p,
                    ],
                    200
                );
            }
        } else {
            return $this->sendError(
                "invalid_role",
                "You must be seller to create product",
                401
            );
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

        /// Validate requested user_id and product_id
        $post_obj = get_post($product->get_id());
        $author_id = $post_obj->post_author;
        if ($user_id != $author_id) {
            return $this->sendError(
                "unauthorized",
                "You are not allow to do this",
                401
            );
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
            $product_classname = WC_Product_Factory::get_product_classname(
                $product->get_id(),
                $type
            );

            // Get the new product object from the correct classname
            $product = new $product_classname($product->get_id());
            $product->save();
        }
        if (isset($tags)) {
            $tags = array_filter(explode(",", $tags));
            wp_set_object_terms($product->get_id(), $tags, "product_tag");
        }

    

        if (isset($featured_image)) {
            if (!empty($featured_image)) {
                if ($this->http_check($featured_image)) {
                    $featured_image_id = $this->find_image_id($featured_image);
                    $product->set_image_id($featured_image_id);
                } else {
                    $featured_image_id = upload_image_from_mobile(
                        $featured_image,
                        $count,
                        $user_id
                    );
                    $product->set_image_id($featured_image_id);
                    $count = $count + 1;
                }
            } else {
                $product->set_image_id("");
            }
        }

        if (isset($product_images)) {
            $product_images_array = array_filter(explode(",", $product_images));
            $img_array = [];

            foreach ($product_images_array as $p_img) {
                if (!empty($p_img)) {
                    if ($this->http_check($p_img)) {
                        $img_id = $this->find_image_id($p_img);
                        array_push($img_array, $img_id);
                    } else {
                        $img_id = upload_image_from_mobile(
                            $p_img,
                            $count,
                            $user_id
                        );
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

            if (in_array($product->get_type(), ["variable", "grouped"], true)) {
                $product->set_regular_price("");
                $product->set_sale_price("");
                $product->set_date_on_sale_to("");
                $product->set_date_on_sale_from("");
                $product->set_price("");
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
            if ("yes" === get_option("woocommerce_manage_stock")) {
                // Manage stock.
                if (isset($manage_stock)) {
                    $product->set_manage_stock($manage_stock);
                }

                // Backorders.
                if (isset($backorders)) {
                    $product->set_backorders($backorders);
                }

                if ($product->is_type("grouped")) {
                    $product->set_manage_stock("no");
                    $product->set_backorders("no");
                    $product->set_stock_quantity("");
                    $product->set_stock_status($stock_status);
                } elseif ($product->is_type("external")) {
                    $product->set_manage_stock("no");
                    $product->set_backorders("no");
                    $product->set_stock_quantity("");
                    $product->set_stock_status("instock");
                } elseif ($product->get_manage_stock()) {
                    // Stock status is always determined by children so sync later.
                    if (!$product->is_type("variable")) {
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
                    $product->set_manage_stock("no");
                    $product->set_stock_quantity("");
                    $product->set_stock_status($stock_status);
                }
            } elseif (!$product->is_type("variable")) {
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
            $pro_attributes = [];
            foreach ($attribute_json as $key => $value) {
                if ($value["isActive"]) {
                    $attribute_name = strtolower($value["slug"]);
                    if ($value["default"]) {
                        $attribute_name = strtolower("pa_" . $value["slug"]);
                    }
                    $attribute_id = wc_attribute_taxonomy_id_by_name(
                        $attribute_name
                    );
                    $attribute = new WC_Product_Attribute();
                    $attribute->set_id($attribute_id);
                    $attribute->set_name(wc_clean($attribute_name));
                    $options = $value["options"];
                    $attribute->set_options($options);
                    $attribute->set_visible($value["visible"]);
                    $attribute->set_variation($value["variation"]);
                    $pro_attributes[] = $attribute;
                }
            }

            $product->set_props([
                "attributes" => $pro_attributes,
            ]);
            $product->save();

            if ($product->is_type("variable")) {
                $variations_arr = json_decode($variations, true);
                foreach ($variations_arr as $variation) {
                    if ($variation["variation_id"] != -1) {
                        foreach ($variation["slugs"] as $key => $value) {
                            $variationAttrArr[$key] = strtolower(
                                strval($value)
                            );
                        }
                        $variationProduct = new WC_Product_Variation(
                            $variation["variation_id"]
                        );
                        $variationProduct->set_regular_price(
                            $variation["display_regular_price"]
                        );
                        $variationProduct->set_sale_price(
                            $variation["display_price"]
                        );
                        $variationProduct->set_stock_quantity(
                            $variation["max_qty"]
                        );
                        $variationProduct->set_attributes($variationAttrArr);
                        $variationProduct->set_manage_stock(
                            boolval($variation["manage_stock"])
                        );
                        $variationProduct->set_status(
                            $variation["variation_is_active"]
                                ? "publish"
                                : "private"
                        );
                        $variationProduct->save();
                    } else {
                        // Creating the product variation
                        $variation_post = [
                            "post_title" => $product->get_title(),
                            "post_name" =>
                                "product-" . $product->get_id() . "-variation",
                            "post_status" => "publish",
                            "post_parent" => $product->get_id(),
                            "post_type" => "product_variation",
                            "guid" => $product->get_permalink(),
                        ];
                        $variation_id = wp_insert_post($variation_post);
                        foreach ($variation["slugs"] as $key => $value) {
                            $variationAttrArr[$key] = strtolower(
                                strval($value)
                            );
                        }
                        $variationProduct = new WC_Product_Variation(
                            $variation_id
                        );
                        $variationProduct->set_regular_price(
                            $variation["display_regular_price"]
                        );
                        $variationProduct->set_sale_price(
                            $variation["display_price"]
                        );
                        $variationProduct->set_stock_quantity(
                            $variation["max_qty"]
                        );
                        $variationProduct->set_attributes($variationAttrArr);
                        $variationProduct->set_manage_stock(
                            boolval($variation["manage_stock"])
                        );
                        $variationProduct->set_status(
                            $variation["variation_is_active"]
                                ? "publish"
                                : "private"
                        );
                        $variationProduct->save();
                    }
                }
            }

            wp_update_post([
                "ID" => $product->get_id(),
                "post_author" => $user_id,
            ]);
            //print_r($product);
            $image_arr = [];
            $p = $product->get_data();

            foreach (array_filter($p["gallery_image_ids"]) as $img) {
                $image = wp_get_attachment_image_src($img, "full");

                if (!is_null($image[0])) {
                    $image_arr[] = $image[0];
                }
            }
            $p["description"] = strip_tags($p["description"]);
            $p["short_description"] = strip_tags($p["short_description"]);
            $p["images"] = $image_arr;
            $image = wp_get_attachment_image_src($p["image_id"], "full");
            if (!is_null($image[0])) {
                $p["featured_image"] = $image[0];
            }
            $p["type"] = $product->get_type();
            $p["on_sale"] = $product->is_on_sale();
            $attributes = [];
            foreach ($product->get_attributes() as $attribute) {
                $attributes[] = [
                    "id" => $attribute["is_taxonomy"]
                        ? wc_attribute_taxonomy_id_by_name($attribute["name"])
                        : 0,
                    "name" => $this->get_attribute_taxonomy_name(
                        $attribute["name"],
                        $product
                    ),
                    "position" => (int)$attribute["position"],
                    "visible" => (bool)$attribute["is_visible"],
                    "variation" => (bool)$attribute["is_variation"],
                    "options" => $this->get_attribute_options(
                        $product->get_id(),
                        $attribute
                    ),
                    "slugs" => $this->get_attribute_slugs(
                        $product->get_id(),
                        $attribute
                    ),
                    "default" => 0 === strpos($attribute["name"], "pa_"),
                ];
            }

            $p["attributesData"] = $attributes;
            if ($product->is_type("variable")) {
                $query = [
                    "post_parent" => $product->get_id(),
                    "post_status" => ["publish", "private"],
                    "post_type" => ["product_variation"],
                    "posts_per_page" => -1,
                ];

                $wc_query = new WP_Query($query);
                while ($wc_query->have_posts()) {
                    $wc_query->next_post();
                    $result[] = $wc_query->post;
                }

                foreach ($result as $variation) {
                    $p_varation = new WC_Product_Variation($variation->ID);
                    $dataVariation = array();
                    $dataVariation["variation_id"] = $p_varation->get_id();
                    $dataVariation["max_qty"] = $p_varation->get_stock_quantity();
                    $dataVariation["variation_is_active"] =
                        $p_varation->get_status() == "publish";
                    $dataVariation["display_price"] = $p_varation->get_sale_price();
                    $dataVariation["display_regular_price"] = $p_varation->get_regular_price();
                    $attributes = $p_varation->get_attributes();
                    foreach ($attributes as $attribute) {
                        $slugs[] = $attribute["value"];
                    }
                    $dataVariation["attributes"] = $attributes;
                    $dataVariation["slugs"] = $slugs;
                    $dataVariation["manage_stock"] = $p_varation->get_manage_stock();
                    $p["variable_products"][] = $dataVariation;
                }
            }
            return new WP_REST_Response(
                [
                    "status" => "success",
                    "response" => $p,
                ],
                200
            );
        }
    }

    /// DELETE ///
    public function vendor_admin_delete_product($request, $user_id)
    {
        /// Validate product ID
        $id = isset($request['id']) ? $request['id'] : 0;
        if (isset($request['id']) && is_numeric($id)) {
            $product = $this->get_product_item($id);
        } else {
            return $this->sendError("request_failed", "Invalid data", 400);
        }
        /// Validate requested user_id and product_id
        $post_obj = get_post($product->get_id());
        $author_id = $post_obj->post_author;
        if ($user_id != $author_id) {
            return $this->sendError(
                "unauthorized",
                "You are not allow to do this",
                401
            );
        }
        wp_delete_post($product->get_id());
        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => "",
            ],
            200
        );
    }

    function wcfm_update_order_delivery_boys_meta($order_id, $delivery_boys_array = array())
    {
        if (empty($delivery_boys_array)) $delivery_boys_array = wcfm_get_order_delivery_boys($order_id);
        $delivery_boys_string = '';
        if (!empty($delivery_boys_array)) {
            foreach ($delivery_boys_array as $delivery_boy) {
                if (!empty($delivery_boy['delivery_boy'])) {
                    $delivery_boys_string .= ',' . $delivery_boy['delivery_boy'];
                }
            }
            update_post_meta($order_id, '_wcfm_delivery_boys', $delivery_boys_string);
        }
    }

    function wcfm_get_order_delivery_boys($order_id, $order_item_id = '')
    {
        global $WCFM, $WCFMd, $wpdb;

        $delivery_boys_array = array();

        if (!$order_id) return $delivery_boys_array;

        $sql = "SELECT * FROM `{$wpdb->prefix}wcfm_delivery_orders`";
        $sql .= " WHERE 1=1";
        $sql .= " AND order_id = %s";
        if (apply_filters('wcfm_is_show_marketplace_itemwise_orders', true)) {
            if ($order_item_id) $sql .= " AND item_id = %s";
        } else {
            $sql .= " GROUP BY vendor_id";
        }

        if(apply_filters('wcfm_is_show_marketplace_itemwise_orders', true) &&  $order_item_id){
            $sql = $wpdb->prepare($sql, $order_id, $order_item_id);
        }else{
            $sql = $wpdb->prepare($sql, $order_id);
        }
        $delivery_boys = $wpdb->get_results($sql);
        if (!empty($delivery_boys)) {
            foreach ($delivery_boys as $delivery_boy) {
                $delivery_boys_array[] = array('order' => $order_id, 'item' => $delivery_boy->item_id, 'vendor' => $delivery_boy->vendor_id, 'delivery_boy' => $delivery_boy->delivery_boy, 'status' => $delivery_boy->delivery_status);
            }
        }

        return apply_filters('wcfm_delivery_boys', $delivery_boys_array, $order_id, $order_item_id, $delivery_boys);
    }

    public function wcfmd_delivery_boy_assigned($request, $vendor_id)
    {
        global $WCFM, $WCFMmp, $WCFMu, $WCFMd, $wpdb;

        $order_id = sanitize_text_field($request["wcfm_tracking_order_id"]);
        $order_item_id = sanitize_text_field($request["wcfm_tracking_order_item_id"]);
        $wcfm_delivery_boy = sanitize_text_field($request["wcfm_delivery_boy"]);
        $product_id = sanitize_text_field($request["wcfm_tracking_product_id"]);
        $wcfm_tracking_data = [
            "wcfm_tracking_code" => sanitize_text_field($request["wcfm_tracking_code"]),
            "wcfm_tracking_url" => sanitize_text_field($request["wcfm_tracking_url"]),
            "wcfm_tracking_order_id" => sanitize_text_field($request["wcfm_tracking_order_id"]),
            "wcfm_tracking_product_id" => sanitize_text_field($request["wcfm_tracking_product_id"]),
            "wcfm_tracking_order_item_id" =>
            sanitize_text_field($request["wcfm_tracking_order_item_id"]),
            "wcfm_delivery_boy" => sanitize_text_field($request["wcfm_delivery_boy"]),
        ];

        $wcfm_delivery_boy = absint($wcfm_delivery_boy);

        if ($wcfm_delivery_boy) {
            $wcfm_delivery_boy_user = get_userdata($wcfm_delivery_boy);

            // Order Item Meta Update
            if (apply_filters("wcfm_is_allow_delivery_boy_as_meta", true)) {
                wc_update_order_item_meta(
                    $order_item_id,
                    "wcfm_delivery_boy",
                    $wcfm_delivery_boy
                );
            }

            // Order Meta Update
            wcfm_update_order_delivery_boys_meta($order_id);

            // Delivery Order Update
            $order = wc_get_order($order_id);

            $customer_id = 0;
            if ($order->get_user_id()) {
                $customer_id = $order->get_user_id();
            }

            $payment_method = !empty($order->get_payment_method())
                ? $order->get_payment_method()
                : "";

            $line_item = new WC_Order_Item_Product($order_item_id);
            $product = $line_item->get_product();
            $product_id = $line_item->get_product_id();
            $variation_id = $line_item->get_variation_id();

            $sql = $wpdb->prepare(
                "INSERT INTO `{$wpdb->prefix}wcfm_delivery_orders` 
                                  ( vendor_id
                                  , order_id
                                  , customer_id
                                  , payment_method
                                  , product_id
                                  , variation_id
                                  , quantity
                                  , product_price
                                  , item_id
                                  , item_sub_total
                                  , item_total
                                  , delivery_boy
                                  ) VALUES ( %d
                                  , %d
                                  , %d
                                  , %s
                                  , %d
                                  , %d
                                  , %d
                                  , %s
                                  , %d
                                  , %s
                                  , %s
                                  , %d
                                  ) ON DUPLICATE KEY UPDATE `delivery_boy` = %d",
                $vendor_id,
                $order_id,
                $customer_id,
                $payment_method,
                $product_id,
                $variation_id,
                $line_item->get_quantity(),
                $product->get_price(),
                $order_item_id,
                $line_item->get_subtotal(),
                $line_item->get_total(),
                $wcfm_delivery_boy,
                $wcfm_delivery_boy
            );
            $wpdb->query($sql);
            $delivery_id = $wpdb->insert_id;

            // Update Delivery Meta
            $order_item_processed_id = wc_get_order_item_meta(
                $order_item_id,
                "_wcfmmp_order_item_processed",
                true
            );
            if ($WCFMmp && $order_item_processed_id) {
                $gross_sales_total = (float)$WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta(
                    $order_item_processed_id,
                    "gross_sales_total"
                );
                $key = "gross_sales_total";
                $value = $gross_sales_total;
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO `{$wpdb->prefix}wcfm_delivery_orders_meta` 
									( order_delivery_id
									, `key`
									, `value`
									) VALUES ( %d
									, %s
									, %s
									)",
                        $delivery_id,
                        $key,
                        $value
                    )
                );
                $delivery_meta_id = $wpdb->insert_id;
            }

            // Notification Update

            if (apply_filters("wcfm_is_allow_itemwise_notification", true)) {
                $wcfm_messages = sprintf(
                    __(
                        "<b>%s</b> assigned as Delivery Boy for order <b>%s</b> item <b>%s</b>.",
                        "wc-frontend-manager-delivery"
                    ),
                    $wcfm_delivery_boy_user->first_name .
                    " " .
                    $wcfm_delivery_boy_user->last_name,
                    "#" . $order_id,
                    get_the_title($product_id)
                );
                $comment_id = $order->add_order_note(
                    $wcfm_messages,
                    apply_filters(
                        "wcfm_is_allow_delivery_note_to_customer",
                        "1"
                    )
                );
            } else {
                if (
                    ($vendor_id &&
                        !get_post_meta(
                            $order_id,
                            "_wcfm_order_delivery_assigned_" . $vendor_id,
                            true
                        )) ||
                    (!$vendor_id &&
                        !get_post_meta(
                            $order_id,
                            "_wcfm_order_delivery_assigned_" . $order_id,
                            true
                        ))
                ) {
                    $wcfm_messages = sprintf(
                        __(
                            "<b>%s</b> assigned as Delivery Boy for order <b>%s</b>.",
                            "wc-frontend-manager-delivery"
                        ),
                        $wcfm_delivery_boy_user->first_name .
                        " " .
                        $wcfm_delivery_boy_user->last_name,
                        "#" . $order_id
                    );
                    $comment_id = $order->add_order_note(
                        $wcfm_messages,
                        apply_filters(
                            "wcfm_is_allow_delivery_note_to_customer",
                            "1"
                        )
                    );
                }
            }

            // Deivery Boy Notification
          
            if (apply_filters("wcfm_is_allow_itemwise_notification", true)) {
                $wcfm_messages = sprintf(
                    __(
                        "You have assigned to order <b>%s</b> item <b>%s</b>.",
                        "wc-frontend-manager-delivery"
                    ),
                    '#<span class="wcfm_dashboard_item_title">' .
                    $order_id .
                    "</span>",
                    get_the_title($product_id)
                );
                $WCFM->wcfm_notification->wcfm_send_direct_message(
                    -1,
                    $wcfm_delivery_boy,
                    1,
                    0,
                    $wcfm_messages,
                    "delivery_boy_assign"
                );

                do_action(
                    "wcfmd_after_delivery_boy_assigned",
                    $order_id,
                    $order_item_id,
                    $wcfm_tracking_data,
                    $product_id,
                    $wcfm_delivery_boy,
                    $wcfm_messages
                );

            } else {
                if (
                    ($vendor_id &&
                        !get_post_meta(
                            $order_id,
                            "_wcfm_order_delivery_assigned_" . $vendor_id,
                            true
                        )) ||
                    (!$vendor_id &&
                        !get_post_meta(
                            $order_id,
                            "_wcfm_order_delivery_assigned_" . $order_id,
                            true
                        ))
                ) {
                    $wcfm_messages = sprintf(
                        __(
                            "You have assigned to order <b>%s</b>.",
                            "wc-frontend-manager-delivery"
                        ),
                        '#<span class="wcfm_dashboard_item_title">' .
                        $order_id .
                        "</span>"
                    );
                    $WCFM->wcfm_notification->wcfm_send_direct_message(
                        -1,
                        $wcfm_delivery_boy,
                        1,
                        0,
                        $wcfm_messages,
                        "delivery_boy_assign"
                    );

                    do_action(
                        "wcfmd_after_delivery_boy_assigned",
                        $order_id,
                        $order_item_id,
                        $wcfm_tracking_data,
                        $product_id,
                        $wcfm_delivery_boy,
                        $wcfm_messages
                    );
                }
            }

            if ($vendor_id) {
                update_post_meta(
                    $order_id,
                    "_wcfm_order_delivery_assigned_" . $vendor_id,
                    "yes"
                );
                update_post_meta(
                    $order_id,
                    "_wcfm_order_delivery_assigned_" . $order_id,
                    "yes"
                );
            } else {
                update_post_meta(
                    $order_id,
                    "_wcfm_order_delivery_assigned_" . $order_id,
                    "yes"
                );
            }
        }
        return new WP_REST_Response(
            [
                "status" => "success",
            ],
            200
        );
    }

    public function get_delivery_users($name)
    {
        global $wpdb;
        $results = [];
        $table_name = $wpdb->prefix . "users";
        $table_name2 = $wpdb->prefix . "usermeta";

        $sql = "SELECT {$table_name}.ID, {$table_name}.display_name, {$table_name}.user_login, {$table_name}.user_email";
        $sql .= " FROM {$table_name} INNER JOIN {$table_name2}";
        $sql .= " ON {$table_name}.ID = {$table_name2}.user_id";
        $sql .= " WHERE {$table_name2}.meta_key = '{$wpdb->prefix}capabilities' ";
        $sql .= " AND {$table_name2}.meta_value LIKE '%wcfm_delivery_boy%' AND ({$table_name}.display_name LIKE %s OR {$table_name}.user_login LIKE %s OR {$table_name}.user_email LIKE %s)";
        $sql .= " ORDER BY {$table_name}.display_name";

        $search_text = '%'.sanitize_text_field($name).'%';
        $sql  = $wpdb->prepare($sql, $search_text, $search_text, $search_text);
        $users = $wpdb->get_results($sql);

        if (count($users) == 0) {
            return new WP_REST_Response(
                [
                    "status" => "success",
                    "response" => $results,
                ],
                200
            );
        }
        $user_ids = [];
        foreach ($users as $user) {
            $profile_pic = wp_get_attachment_image_src(get_user_meta($user->ID, 'wclovers_user_avatar', true))[0];
            if (!$profile_pic) {
                $profile_pic = null;
            }
            $user_ids[] = [
                "id" => $user->ID,
                "name" => $user->display_name,
                "user_email" => $user->user_email,
                "user_login" => $user->user_login,
                "profile_picture" => $profile_pic,
            ];
        }

        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => $user_ids,
            ],
            200
        );
    }
}
