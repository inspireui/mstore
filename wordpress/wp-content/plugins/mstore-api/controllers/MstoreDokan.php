<?php

/*
 * Base REST Controller for mstore
 *
 * @since 1.0.0
 *
 * @package dokan
 */
class MStoreDokan extends WP_REST_Controller
{

    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'mstore/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'stores';

    protected $post_type = 'product';

    protected $post_status = ['publish'];
    protected $template;

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
        $isChild = strstr(strtolower(wp_get_theme()), "child");
        if ($isChild == 'child') {
            $string = explode(" ", wp_get_theme());
            $this->template = strtolower($string[0]);
        } else {
            $this->template = strtolower(wp_get_theme());
        }
        // if($this->template == 'handystore'){
        add_action('rest_api_init', array($this, 'register_routes_for_handy'));
        // }

    }

    /**
     * Register all routes releated with stores - handy theme
     *
     * @return void
     */
    public function register_routes_for_handy()
    {

        register_rest_route($this->namespace, '/stores', array(
            '',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_stores_handy'),
            ),
        ));

        //get products by store
        register_rest_route($this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/products', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the object.', 'MStore-api'),
                ),
            ),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_store_products'),
            ),
        ));

    }

    /**
     * Get store - Handy
     *
     * @param object $request
     *
     * @return json
     */
    public function get_stores_handy()
    {

        $vendors = array();

        $args = array(
            'role' => 'vendor',
            'number' => 10,
            'offset' => 0,
            'orderby' => 'registered',
            'order' => 'ASC',
        );

        $user_query = new WP_User_Query($args);
        $results = $user_query->get_results();

        foreach ($results as $result) {
            $meta = get_user_meta($result->data->ID);
            $meta = array_filter(array_map(function ($item) {
                return $item[0];
            }, $meta));
            $meta['id'] = $result->data->ID;
            $vendors[] = $meta;

        }

        return $vendors;
    }

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function register_routes()
    {

        register_rest_route($this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/products', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the object.', 'MStore-api'),
                ),
            ),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_store_products'),
            ),
        ));

    }

    /**
     * Get store Products
     *
     * @param object $request
     *
     * @return json
     */
    public function get_store_products($request)
    {

        $response = $this->get_items($request);
        return $response;
    }

    /**
     * Get a collection of posts.
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items($request)
    {

        $query_args = [
            'author' => $request['id'],
            'post_type' => 'product',
            'posts_per_page' => $request['per_page'] ? $request['per_page'] : 20,
            'paged' => $request['page'] ? $request['page'] : 1,
        ];

        // $query_args = $this->prepare_objects_query( $request );

        $query = new WP_Query();
        $result = $query->query($query_args);

        $data = array();
        $objects = array_map(array($this, 'get_object'), $result);

        foreach ($objects as $object) {

            $data = $this->prepare_response($object, $request);
            $data_objects[] = $this->prepare_collection($data);
        }

        $response = rest_ensure_response($data_objects);
        $response = $this->format_collection_response($response, $request, $query->found_posts);

        return $response;
    }

    public function get_object($product)
    {
        return wc_get_product($product);
    }

    protected function getData($product, $context){
        $data = array(
            'id' => $product->get_id(),
            'name' => $product->get_name($context),
            'slug' => $product->get_slug($context),
            'post_author' => get_post_field('post_author', $product->get_id()),
            'permalink' => $product->get_permalink(),
            'date_created' => wc_rest_prepare_date_response($product->get_date_created($context), false),
            'date_created_gmt' => wc_rest_prepare_date_response($product->get_date_created($context)),
            'date_modified' => wc_rest_prepare_date_response($product->get_date_modified($context), false),
            'date_modified_gmt' => wc_rest_prepare_date_response($product->get_date_modified($context)),
            'type' => $product->get_type(),
            'status' => $product->get_status($context),
            'featured' => $product->is_featured(),
            'catalog_visibility' => $product->get_catalog_visibility($context),
            'description' => 'view' === $context ? wpautop(do_shortcode($product->get_description())) : $product->get_description($context),
            'short_description' => 'view' === $context ? apply_filters('woocommerce_short_description', $product->get_short_description()) : $product->get_short_description($context),
            'sku' => $product->get_sku($context),
            'price' => $product->get_price($context),
            'regular_price' => $product->get_regular_price($context),
            'sale_price' => $product->get_sale_price($context) ? $product->get_sale_price($context) : '',
            'date_on_sale_from' => wc_rest_prepare_date_response($product->get_date_on_sale_from($context), false),
            'date_on_sale_from_gmt' => wc_rest_prepare_date_response($product->get_date_on_sale_from($context)),
            'date_on_sale_to' => wc_rest_prepare_date_response($product->get_date_on_sale_to($context), false),
            'date_on_sale_to_gmt' => wc_rest_prepare_date_response($product->get_date_on_sale_to($context)),
            'price_html' => $product->get_price_html(),
            'on_sale' => $product->is_on_sale($context),
            'purchasable' => $product->is_purchasable(),
            'total_sales' => $product->get_total_sales($context),
            'virtual' => $product->is_virtual(),
            'downloadable' => $product->is_downloadable(),
            'downloads' => $this->get_downloads($product),
            'download_limit' => $product->get_download_limit($context),
            'download_expiry' => $product->get_download_expiry($context),
            'external_url' => $product->is_type('external') ? $product->get_product_url($context) : '',
            'button_text' => $product->is_type('external') ? $product->get_button_text($context) : '',
            'tax_status' => $product->get_tax_status($context),
            'tax_class' => $product->get_tax_class($context),
            'manage_stock' => $product->managing_stock(),
            'stock_quantity' => $product->get_stock_quantity($context),
            'in_stock' => $product->is_in_stock(),
            'backorders' => $product->get_backorders($context),
            'backorders_allowed' => $product->backorders_allowed(),
            'backordered' => $product->is_on_backorder(),
            'sold_individually' => $product->is_sold_individually(),
            'weight' => $product->get_weight($context),
            'dimensions' => array(
                'length' => $product->get_length($context),
                'width' => $product->get_width($context),
                'height' => $product->get_height($context),
            ),
            'shipping_required' => $product->needs_shipping(),
            'shipping_taxable' => $product->is_shipping_taxable(),
            'shipping_class' => $product->get_shipping_class(),
            'shipping_class_id' => $product->get_shipping_class_id($context),
            'reviews_allowed' => $product->get_reviews_allowed($context),
            'average_rating' => 'view' === $context ? wc_format_decimal($product->get_average_rating(), 2) : $product->get_average_rating($context),
            'rating_count' => $product->get_rating_count(),
            'related_ids' => array_map('absint', array_values(wc_get_related_products($product->get_id()))),
            'upsell_ids' => array_map('absint', $product->get_upsell_ids($context)),
            'cross_sell_ids' => array_map('absint', $product->get_cross_sell_ids($context)),
            'parent_id' => $product->get_parent_id($context),
            'purchase_note' => 'view' === $context ? wpautop(do_shortcode(wp_kses_post($product->get_purchase_note()))) : $product->get_purchase_note($context),
            'categories' => $this->get_taxonomy_terms($product),
            'tags' => $this->get_taxonomy_terms($product, 'tag'),
            'images' => $this->get_images($product),
            'attributes' => $this->get_attributes($product),
            'default_attributes' => $this->get_default_attributes($product),
            'variations' => array(),
            'grouped_products' => array(),
            'menu_order' => $product->get_menu_order($context),
            'meta_data' => $product->get_meta_data(),

        );
        return $data;
    }

    protected function prepare_response($product, $request)
    {

        $context = !empty($request['context']) ? $request['context'] : 'view';
        $author_id = get_post_field('post_author', $product->get_id());

        $data = $this->getData($product, $context);

        if ($this->template != 'handystore') {
            $store = dokan()->vendor->get($author_id);
            $storeAddress = $store->get_address();
            $data['store'] = array(
                'id' => $store->get_id(),
                'name' => $store->get_name(),
                'shop_name' => $store->get_shop_name(),
                'url' => $store->get_shop_url(),
                'address' => $storeAddress['street_1'] . ", " . $storeAddress['city'] . ", " . $storeAddress['state'],
            );
            $data['rating'] = $store->get_rating();
        } else {
            $meta = get_user_meta($author_id);
            $meta = array_filter(array_map(function ($item) {
                return $item[0];
            }, $meta));
            $data['store'] = $meta;
            global $wpdb;

            // $sql = ;
            
            $result = $wpdb->query($wpdb->prepare("SELECT AVG(cm.meta_value) as average, COUNT(wc.comment_ID) as count FROM $wpdb->posts p INNER JOIN $wpdb->comments wc ON p.ID = wc.comment_post_ID LEFT JOIN $wpdb->commentmeta cm ON cm.comment_id = wc.comment_ID
            WHERE p.post_author = %s AND p.post_type = %s AND p.post_status = %s AND ( cm.meta_key = %s OR cm.meta_key IS NULL) AND wc.comment_approved = %s ORDER BY wc.comment_post_ID", $author_id, 'product', 'publish', 'rating', 1));
            $rating_value = array(
                'rating' => number_format($result->average, 2),
                'count' => (int) $result->count,
            );
            $data['rating'] = $rating_value;
        }

        $response = rest_ensure_response($data);

        $response->add_links($this->prepare_links($product, $request));
        return apply_filters("rest_prepare_{$this->post_type}_object", $response, $product, $request);
    }

    public function prepare_collection($response)
    {
        if (!($response instanceof WP_REST_Response)) {
            return $response;
        }

        $data = (array) $response->get_data();
        $server = rest_get_server();

        if (method_exists($server, 'get_compact_response_links')) {
            $links = $server->get_compact_response_links($response);
        } else {
            $links = $server->get_compact_response_links($response);
        }

        if (!empty($links)) {
            $data['_links'] = $links;
        }

        return $data;
    }

    /**
     * Get the downloads for a product or product variation.
     *
     * @param WC_Product|WC_Product_Variation $product Product instance.
     * @return array
     */
    protected function get_downloads($product)
    {
        $downloads = array();

        if ($product->is_downloadable()) {
            foreach ($product->get_downloads() as $file_id => $file) {
                $downloads[] = array(
                    'id' => $file_id, // MD5 hash.
                    'name' => $file['name'],
                    'file' => $file['file'],
                );
            }
        }

        return $downloads;
    }

    protected function get_taxonomy_terms($product, $taxonomy = 'cat')
    {
        $terms = array();

        foreach (wc_get_object_terms($product->get_id(), 'product_' . $taxonomy) as $term) {
            $terms[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }

        return $terms;
    }
    protected function get_images($product)
    {
        $images = array();
        $attachment_ids = array();

        // Add featured image.
        if (has_post_thumbnail($product->get_id())) {
            $attachment_ids[] = $product->get_image_id();
        }

        // Add gallery images.
        $attachment_ids = array_merge($attachment_ids, $product->get_gallery_image_ids());

        // Build image data.
        foreach ($attachment_ids as $position => $attachment_id) {
            $attachment_post = get_post($attachment_id);
            if (empty($attachment_post)) {
                continue;
            }

            $attachment = wp_get_attachment_image_src($attachment_id, 'full');
            if (!is_array($attachment)) {
                continue;
            }

            $images[] = array(
                'id' => (int) $attachment_id,
                'date_created' => wc_rest_prepare_date_response($attachment_post->post_date, false),
                'date_created_gmt' => wc_rest_prepare_date_response(strtotime($attachment_post->post_date_gmt)),
                'date_modified' => wc_rest_prepare_date_response($attachment_post->post_modified, false),
                'date_modified_gmt' => wc_rest_prepare_date_response(strtotime($attachment_post->post_modified_gmt)),
                'src' => current($attachment),
                'name' => get_the_title($attachment_id),
                'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                'position' => (int) $position,
            );
        }

        // Set a placeholder image if the product has no images set.
        if (empty($images)) {
            $images[] = array(
                'id' => 0,
                'date_created' => wc_rest_prepare_date_response(current_time('mysql'), false), // Default to now.
                'date_created_gmt' => wc_rest_prepare_date_response(current_time('timestamp', true)), // Default to now.
                'date_modified' => wc_rest_prepare_date_response(current_time('mysql'), false),
                'date_modified_gmt' => wc_rest_prepare_date_response(current_time('timestamp', true)),
                'src' => wc_placeholder_img_src(),
                'name' => __('Placeholder', 'dokan-lite'),
                'alt' => __('Placeholder', 'dokan-lite'),
                'position' => 0,
            );
        }

        return $images;
    }

    protected function get_attribute_taxonomy_label($name)
    {
        $tax = get_taxonomy($name);
        $labels = get_taxonomy_labels($tax);

        return $labels->singular_name;
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

    protected function get_default_attributes($product)
    {
        $default = array();

        if ($product->is_type('variable')) {
            foreach (array_filter((array) $product->get_default_attributes(), 'strlen') as $key => $value) {
                if (0 === strpos($key, 'pa_')) {
                    $default[] = array(
                        'id' => wc_attribute_taxonomy_id_by_name($key),
                        'name' => $this->get_attribute_taxonomy_name($key, $product),
                        'option' => $value,
                    );
                } else {
                    $default[] = array(
                        'id' => 0,
                        'name' => $this->get_attribute_taxonomy_name($key, $product),
                        'option' => $value,
                    );
                }
            }
        }

        return $default;
    }

    protected function get_attributes($product)
    {
        $attributes = array();

        if ($product->is_type('variation')) {
            $_product = wc_get_product($product->get_parent_id());
            foreach ($product->get_variation_attributes() as $attribute_name => $attribute) {
                $name = str_replace('attribute_', '', $attribute_name);

                if (!$attribute) {
                    continue;
                }

                // Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
                if (0 === strpos($attribute_name, 'attribute_pa_')) {
                    $option_term = get_term_by('slug', $attribute, $name);
                    $attributes[] = array(
                        'id' => wc_attribute_taxonomy_id_by_name($name),
                        'name' => $this->get_attribute_taxonomy_name($name, $_product),
                        'option' => $option_term && !is_wp_error($option_term) ? $option_term->name : $attribute,
                    );
                } else {
                    $attributes[] = array(
                        'id' => 0,
                        'name' => $this->get_attribute_taxonomy_name($name, $_product),
                        'option' => $attribute,
                    );
                }
            }
        } else {
            foreach ($product->get_attributes() as $attribute) {
                $attributes[] = array(
                    'id' => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name($attribute['name']) : 0,
                    'name' => $this->get_attribute_taxonomy_name($attribute['name'], $product),
                    'position' => (int) $attribute['position'],
                    'visible' => (bool) $attribute['is_visible'],
                    'variation' => (bool) $attribute['is_variation'],
                    'options' => $this->get_attribute_options($product->get_id(), $attribute),
                );
            }
        }

        return $attributes;
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

    /// Params $request is unused
    protected function prepare_links($object)
    {
        $links = array(
            'self' => array(
                'href' => rest_url(sprintf('/%s/%s/%d', $this->namespace, $this->base, $object->get_id())),
            ),
            'collection' => array(
                'href' => rest_url(sprintf('/%s/%s', $this->namespace, $this->base)),
            ),
        );

        if ($object->get_parent_id()) {
            $links['up'] = array(
                'href' => rest_url(sprintf('/%s/products/%d', $this->namespace, $object->get_parent_id())),
            );
        }

        return $links;
    }

    /**
     * Format item's collection for response
     *
     * @param  object $response
     * @param  object $request
     * @param  array $items
     * @param  int $total_items
     *
     * @return object
     */
    public function format_collection_response($response, $request, $total_items)
    {
        if ($total_items === 0) {
            return $response;
        }

        // Store pagation values for headers then unset for count query.
        $per_page = (int) (!empty($request['per_page']) ? $request['per_page'] : 20);
        $page = (int) (!empty($request['page']) ? $request['page'] : 1);

        $response->header('X-WP-Total', (int) $total_items);

        $max_pages = ceil($total_items / $per_page);

        $response->header('X-WP-TotalPages', (int) $max_pages);
        $base = add_query_arg($request->get_query_params(), rest_url(sprintf('/%s/%s', $this->namespace, $this->base)));

        if ($page > 1) {
            $prev_page = $page - 1;
            if ($prev_page > $max_pages) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg('page', $prev_page, $base);
            $response->link_header('prev', $prev_link);
        }
        if ($max_pages > $page) {

            $next_page = $page + 1;
            $next_link = add_query_arg('page', $next_page, $base);
            $response->link_header('next', $next_link);
        }

        return $response;
    }

}

new MStoreDokan;
