<?php

class ProductManagementHelper
{
    public function sendError($code, $message, $statusCode)
    {
        return new WP_Error($code, $message, [
            "status" => $statusCode,
        ]);
    }

    protected function get_product_item($id)
    {
        $result = wc_get_product($id);
        if (!$result) {
            return $this->sendError(
                "invalid_product",
                "This product does not exist",
                404
            );
        }
        return $result;
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

    private function get_product_info_by_id($id){
        $product = wc_get_product($id);
            $p = $product->get_data();
            $image_arr = [];
            foreach (array_filter($p["gallery_image_ids"]) as $img) {
                $image = wp_get_attachment_image_src($img, "full");
                if (is_array($image) && !is_null($image[0])) {
                    $image_arr[] = $image[0];
                }
            }

            $image = wp_get_attachment_image_src($p["image_id"], "full");
            if (is_array($image) && !is_null($image[0])) {
                $p["featured_image"] = $image[0];
            }

            $p["images"] = $image_arr;
            $p["category_ids"] = [];
			$p['categories'] = [];
            $category_ids = wp_get_post_terms($p["id"], "product_cat");
            foreach ($category_ids as $cat) {
                if ($cat->slug != "uncategorized") {
                    $p["category_ids"][] = $cat->term_id;
					$p['categories'][] = $cat;
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
                    $variation_data = array();
                    $variation_p = new WC_Product_Variation($variation->ID);
                    $variation_data['id'] = $variation->ID;
                    $variation_data['product_id'] = $product->get_id();
                    $variation_data['price'] = $variation_p->get_price();
                    $variation_data['regular_price'] = $variation_p->get_regular_price() ;
                    $variation_data['sale_price'] =$variation_p->get_sale_price() ;
                    $variation_data['date_on_sale_from'] = $variation_p->get_date_on_sale_from();
                    $variation_data['date_on_sale_to'] = $variation_p->get_date_on_sale_to();
                    $variation_data['on_sale'] = $variation_p->is_on_sale();
                    $variation_data['in_stock'] =$variation_p->is_in_stock() ;
                    $variation_data['stock_quantity'] = $variation_p->get_stock_quantity();
                    $variation_data['stock_status'] = $variation_p->get_stock_status();
                    $variation_data['manage_stock'] = $variation_p->get_manage_stock();
                    $feature_image = wp_get_attachment_image_src( $variation_p->get_image_id(), 'single-post-thumbnail' );
                    $variation_data['feature_image'] = $feature_image ? $feature_image[0] : null;
            
                    $attr_arr = array();
                    $variation_attributes = $variation_p->get_attributes();
                    foreach($variation_attributes as $k=>$v){
                        $attr_data = array();
                        $attr_data['name'] = $k;
                        $attr_data['slug'] = $v;
                        $meta = get_post_meta($variation->ID, 'attribute_'.$k, true);
                        $term = get_term_by('slug', $meta, $k);
                        if($term){
                            $attr_data['attribute_name'] = $term->name;
                        }
                        $attr_arr[]=$attr_data;
                    }
                    $variation_data['attributes_arr'] = $attr_arr;

                    $p["variation_products"][] = $variation_data;
                }
            }
            return $p;
    }

    public function get_products($request, $user_id)
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
            $user = get_userdata($user_id);
            $is_admin = $user != false ? (in_array('administrator', (array)$user->roles) || in_array('shop_manager', (array)$user->roles)) : false;
            $vendor_id = absint($user_id);
        }

        $table_name = $wpdb->prefix . "posts";
        $postmeta_table = $wpdb->prefix . "postmeta";
        $is_admin = isset($is_admin) && $is_admin == true;
        if($is_admin){
            $sql = "SELECT * FROM `$table_name` WHERE `$table_name`.`post_type` = 'product' AND `$table_name`.`post_status` != 'trash'";
        }else{
            $sql = "SELECT * FROM `$table_name` WHERE `$table_name`.`post_author` = %s AND `$table_name`.`post_type` = 'product' AND `$table_name`.`post_status` != 'trash'";
        }

        if (isset($request["search"])) {
            $search =  sanitize_text_field($request["search"]);
            $search = "%$search%";

            if ($is_admin) {
                $sql = "SELECT DISTINCT `$table_name`.ID, `$table_name`.* FROM `$table_name` LEFT JOIN `$postmeta_table` ON {$table_name}.ID = {$postmeta_table}.post_id WHERE `$table_name`.`post_type` = 'product' AND `$table_name`.`post_status` != 'trash'";
            } else {
                $sql = "SELECT DISTINCT `$table_name`.ID, `$table_name`.* FROM `$table_name` LEFT JOIN `$postmeta_table` ON {$table_name}.ID = {$postmeta_table}.post_id WHERE `$table_name`.`post_author` = %s AND `$table_name`.`post_type` = 'product' AND `$table_name`.`post_status` != 'trash'";
            }

            $sql .= " AND (`$table_name`.`post_content` LIKE %s OR `$table_name`.`post_title` LIKE %s OR `$table_name`.`post_excerpt` LIKE %s OR (`$postmeta_table`.`meta_key` = '_sku' AND `$postmeta_table`.`meta_value` LIKE %s))";
        }
        $sql .= " ORDER BY `ID` DESC LIMIT %d OFFSET %d";

        $args = array();
        if(!$is_admin){
            $args[] = $vendor_id;
        }
        if (isset($search)) {
            $args[] = $search;
            $args[] = $search;
            $args[] = $search;
            $args[] = $search;
        }
        $args[] = $limit;
        $args[] = $page;
        $sql = $wpdb->prepare($sql, $args);
        $item = $wpdb->get_results($sql);

        $products_arr = [];
        foreach ($item as $pro) {
            $products_arr[] = $this->get_product_info_by_id($pro->ID);
        }

        return apply_filters(
            "get_products",
            $products_arr,
            $request,
            $user_id
        );
    }

    /// CREATE ///
    public function create_or_update_product($request, $user_id)
    {
	
        $user = get_userdata($user_id);

        $is_seller = false;

        $role_arr = ['wcfm_vendor','seller','administrator'];

        foreach($user->roles as $role){
            if(in_array($role, $role_arr)){
                $is_seller = true;
                break;
            }
        }

        $requestStatus = "draft";
        if ($request["status"] != null) {
            $requestStatus = sanitize_text_field($request["status"]);
        }
		
		$is_create_product = false;
		


		$id = sanitize_text_field($request["id"]);
        $name = sanitize_text_field($request["name"]);
		$sku = sanitize_text_field($request['sku']);
		$type = sanitize_text_field($request['type']);
        $description = sanitize_text_field($request["description"]);
        $short_description = sanitize_text_field($request["short_description"]);

        $tags = sanitize_text_field($request['tags']);
		
        $regular_price = sanitize_text_field($request['regular_price']);
        $sale_price = sanitize_text_field($request['sale_price']);
        $stock_quantity = sanitize_text_field($request['stock_quantity']);
        $manage_stock  = sanitize_text_field($request['manage_stock']);
		
		$category_ids  = sanitize_text_field($request['category_ids']);   
		
        if(isset($request['featuredImage'])){
            $featured_image = sanitize_text_field($request['featuredImage']);
        }
		if(isset($request['images'])){
            $product_images = sanitize_text_field($request['images']);
        }
		
		$product_attributes = $request['product_attributes'];
        $variations = $request['variation_products'];
        $stock_status = $request['stock_status'];

		
        $count = 1;
		if(empty($id)){
			$is_create_product = true;
		}
        if ($is_seller) {
			if($is_create_product){
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
			}
			else{
				$post_id = sanitize_text_field($request["id"]);
			}
					
			$product = wc_get_product($post_id);
			if ($product->get_type() !== $type) {
					
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
                        if($featured_image_id > 0){
                            $product->set_image_id($featured_image_id);
                        }
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
                            if($img_id > 0){
                                array_push($img_array, $img_id);
                            }
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
          
            if (isset($product) && !is_wp_error($product)) {
                $product->set_name($name);
  				
                // Sales and prices.
                if (in_array($product->get_type(),["variable", "grouped"],true)) {
                    $product->set_regular_price("");
                    $product->set_sale_price("");
                    $product->set_date_on_sale_to("");
                    $product->set_date_on_sale_from("");
                    $product->set_price("");
                } else {
				
                      // Regular Price.
                    if (isset($regular_price) && !empty($regular_price)) {
                        $product->set_regular_price($regular_price);
                    }
                    // Sale Price.
                    if (isset($sale_price) && !empty($sale_price)) {
                        $product->set_sale_price($sale_price);
                    }
                }
                // Description
                if (isset($description) && !empty($description)) {
                    $product->set_description($description);
                }
                if (isset($short_description) && !empty($short_description) ) {
                    $product->set_description($short_description);
                }

                // Stock status.
                if(!isset($stock_status)){
                    if (!empty($manage_stock) && is_bool($manage_stock) && $manage_stock && !empty($stock_quantity) && $stock_quantity > 0) {
                        $stock_status = 'instock';
                    } else {
                        $stock_status = $product->get_stock_status();
                    }
                }
                $product->set_stock_status($stock_status);
                
                // Stock data.
                if ("yes" === get_option("woocommerce_manage_stock")) {
                    // Manage stock.
                    if (isset($manage_stock)) {
                        $product->set_manage_stock($manage_stock);
                    }
					if ($product->get_manage_stock()){
						// Stock quantity.
                        if (!empty($stock_quantity)) {
                            $product->set_stock_quantity(wc_stock_amount($stock_quantity));
                        }  else {
                        	// Don't manage stock.
                        	$product->set_manage_stock("no");
                        	$product->set_stock_quantity("");
                    	}
					}
                }

                //Assign categories
                if (!empty($category_ids)) {
                    $category_ids = array_filter(explode(',', $category_ids));
                    if (!empty($category_ids)) {
                        $categoryArray = array();
                        foreach ($category_ids as $index) {
                            $categoryArray[] = absint($index);
                        }
                        $product->set_category_ids($categoryArray);
                    }
                }

		

                //Description
                $product->set_short_description($short_description);
                $product->set_description($description);
			
                $attribute_json = json_decode($product_attributes,true);
			
                $pro_attributes = [];
                foreach ($attribute_json as $key => $value) {
                    if ($value["isActive"]) {
                        $attribute_name = $value["slug"];
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
				
				

                if ($product->get_type() == "variable") {
                    $variations_arr = json_decode($variations,true);
					$available_variations_arr = $product->get_children();
                    foreach ($variations_arr as $variation) {
                        if(isset($variation['id'])){
							$variation_id = $variation['id'];
							if(!in_array(intval($variation_id),$available_variations_arr)){
								$var_product = wc_get_product($variation_id);  
								$var_product->delete();
								continue;
							}
						}else{
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
						}
						$variations_attr = json_decode($variation["attributes"], true);
                        foreach ($variations_attr as $item) {
							$attribute = json_decode($item,true);
                            $variationAttrArr[$attribute['name']] = $attribute['slug'];
                        }
                        $variationProduct = new WC_Product_Variation(
                            $variation_id
                        );
                        $variationProduct->set_regular_price(
                            $variation["regular_price"]
                        );
                        $variationProduct->set_sale_price(
                            $variation["sale_price"]
                        );
                        $variationProduct->set_stock_quantity(
                            $variation["stock_quantity"]
                        );
				
						if(isset($variationAttrArr)){
							 $variationProduct->set_attributes($variationAttrArr);
						}
        
                        $variationProduct->set_manage_stock(
                            boolval($variation["manage_stock"])
                        );
                        $variationProduct->set_status(
                            $variation["is_active"]
                                ? "publish"
                                : "private"
                        );
                        $variationProduct->save();

                        if(isset($variation['wholesale_prices']) && count($variation['wholesale_prices']) > 0){
                            foreach($variation['wholesale_prices'] as $item){
                                update_post_meta($variation_id, $item['type'].'_wholesale_discount_type', $item['wholesale_discount_type']);
                                if(!empty($item['wholesale_percentage_discount'])){
                                    update_post_meta($variation_id, $item['type'].'_wholesale_percentage_discount', $item['wholesale_percentage_discount']);
                                }
                                update_post_meta($variation_id, $item['type'].'_wholesale_price', $item['wholesale_price']);
                            }
                        }
                    }
                }


                if (isset($tags)) {
                    $tags = array_filter(explode(",", $tags));
                    wp_set_object_terms($post_id, $tags, "product_tag");
                }
                update_post_meta($product->get_id(),'_sku',$sku);
                wp_update_post([
                    "ID" => $product->get_id(),
                    "post_status" => $requestStatus,
                ]);

                if(isset($request['wholesale_prices']) && count($request['wholesale_prices']) > 0){
                    foreach($request['wholesale_prices'] as $item){
                        update_post_meta($product->get_id(), $item['type'].'_wholesale_discount_type', $item['wholesale_discount_type']);
                        if(!empty($item['wholesale_percentage_discount'])){
                            update_post_meta($product->get_id(), $item['type'].'_wholesale_percentage_discount', $item['wholesale_percentage_discount']);
                        }
                        update_post_meta($product->get_id(), $item['type'].'_wholesale_price', $item['wholesale_price']);
                    }
                }
                wp_update_post([
                    "ID" => $product->get_id(),
                    "post_author" => $user_id,
                ]);
                //print_r($product);
                $p = $product->get_data();
		
                /**** WC Rest Api doesn't return featured_image and the gallery is incorrect, it includes featured_image, so need to separate to get gallery and featured_image *****/
                $image_arr = [];
                foreach (array_filter($p["gallery_image_ids"]) as $img) {
                    $image = wp_get_attachment_image_src($img, "full");
                    if (is_array($image) && !is_null($image[0])) {
                        $image_arr[] = $image[0];
                    }
                }

                $image = wp_get_attachment_image_src($p["image_id"], "full");
                if (is_array($image) && !is_null($image[0])) {
                    $featured_image = $image[0];
                }
                /*********************/
                return new WP_REST_Response(
                        [
                            "status" => "success",
                            "response" => $this->get_product_info_by_id($p['id']),
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
    /// DELETE ///
    public function delete_product($request, $user_id)
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
}
