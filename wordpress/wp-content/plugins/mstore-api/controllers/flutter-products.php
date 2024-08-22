<?php

class CUSTOM_WC_REST_Products_Controller extends WC_REST_Products_Controller
{
    public function get_items($request)
    {
        $query_args = $this->prepare_objects_query($request);
        if (isset($request['author']) && $request['author'] != null) {
            $query_args['author'] = $request['author'];
        }
        if ( is_bool( $request['in_stock'] ) ) {
			$query_args['meta_query'] = $this->add_meta_query(
				$query_args,
				array(
					'key'   => '_stock_status',
					'value' => ['instock', 'onbackorder'],
                    'compare' => 'IN',
				)
			);
		}
        $query_results = $this->get_objects($query_args);

        $objects = array();
        foreach ($query_results['objects'] as $object) {
            $data = $this->prepare_object_for_response($object, $request);
            $objects[] = $this->prepare_response_for_collection($data);
        }

        $page = (int)$query_args['paged'];
        $max_pages = $query_results['pages'];

        $response = rest_ensure_response($objects);
        $response->header('X-WP-Total', $query_results['total']);
        $response->header('X-WP-TotalPages', (int)$max_pages);

        $base = $this->rest_base;
        $attrib_prefix = '(?P<';
        if (strpos($base, $attrib_prefix) !== false) {
            $attrib_names = array();
            preg_match('/\(\?P<[^>]+>.*\)/', $base, $attrib_names, PREG_OFFSET_CAPTURE);
            foreach ($attrib_names as $attrib_name_match) {
                $beginning_offset = strlen($attrib_prefix);
                $attrib_name_end = strpos($attrib_name_match[0], '>', $attrib_name_match[1]);
                $attrib_name = substr($attrib_name_match[0], $beginning_offset, $attrib_name_end - $beginning_offset);
                if (isset($request[$attrib_name])) {
                    $base = str_replace("(?P<$attrib_name>[\d]+)", $request[$attrib_name], $base);
                }
            }
        }
        $base = add_query_arg($request->get_query_params(), rest_url(sprintf('/%s/%s', $this->namespace, $base)));

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

    public function delete_item($request)
    {
        $id = (int)$request['id'];
        $force = (bool)$request['force'];
        $object = $this->get_object((int)$request['id']);
        $result = false;

        if (!$object || 0 === $object->get_id()) {
            return new WP_Error(
                "woocommerce_rest_{$this->post_type}_invalid_id",
                __('Invalid ID.', 'woocommerce'),
                array(
                    'status' => 404,
                )
            );
        }

        if ('variation' === $object->get_type()) {
            return new WP_Error(
                "woocommerce_rest_invalid_{$this->post_type}_id",
                __('To manipulate product variations you should use the /products/&lt;product_id&gt;/variations/&lt;id&gt; endpoint.', 'woocommerce'),
                array(
                    'status' => 404,
                )
            );
        }

        $supports_trash = EMPTY_TRASH_DAYS > 0 && is_callable(array($object, 'get_status'));

        /**
         * Filter whether an object is trashable.
         *
         * Return false to disable trash support for the object.
         *
         * @param boolean $supports_trash Whether the object type support trashing.
         * @param WC_Data $object The object being considered for trashing support.
         */
        $supports_trash = apply_filters("woocommerce_rest_{$this->post_type}_object_trashable", $supports_trash, $object);

        $request->set_param('context', 'edit');
        $response = $this->prepare_object_for_response($object, $request);

        // If we're forcing, then delete permanently.
        if ($force) {
            if ($object->is_type('variable')) {
                foreach ($object->get_children() as $child_id) {
                    $child = wc_get_product($child_id);
                    if (!empty($child)) {
                        $child->delete(true);
                    }
                }
            } else {
                // For other product types, if the product has children, remove the relationship.
                foreach ($object->get_children() as $child_id) {
                    $child = wc_get_product($child_id);
                    if (!empty($child)) {
                        $child->set_parent_id(0);
                        $child->save();
                    }
                }
            }

            $object->delete(true);
            $result = 0 === $object->get_id();
        } else {
            // If we don't support trashing for this type, error out.
            if (!$supports_trash) {
                return new WP_Error(
                    'woocommerce_rest_trash_not_supported',
                    /* translators: %s: post type */
                    sprintf(__('The %s does not support trashing.', 'woocommerce'), $this->post_type),
                    array(
                        'status' => 501,
                    )
                );
            }

            // Otherwise, only trash if we haven't already.
            if (is_callable(array($object, 'get_status'))) {
                if ('trash' === $object->get_status()) {
                    return new WP_Error(
                        'woocommerce_rest_already_trashed',
                        /* translators: %s: post type */
                        sprintf(__('The %s has already been deleted.', 'woocommerce'), $this->post_type),
                        array(
                            'status' => 410,
                        )
                    );
                }

                $object->delete();
                $result = 'trash' === $object->get_status();
            }
        }

        if (!$result) {
            return new WP_Error(
                'woocommerce_rest_cannot_delete',
                /* translators: %s: post type */
                sprintf(__('The %s cannot be deleted.', 'woocommerce'), $this->post_type),
                array(
                    'status' => 500,
                )
            );
        }

        // Delete parent product transients.
        if (0 !== $object->get_parent_id()) {
            wc_delete_product_transients($object->get_parent_id());
        }

        /**
         * Fires after a single object is deleted or trashed via the REST API.
         *
         * @param WC_Data $object The deleted or trashed object.
         * @param WP_REST_Response $response The response data.
         * @param WP_REST_Request $request The request sent to the API.
         */
        do_action("woocommerce_rest_delete_{$this->post_type}_object", $object, $response, $request);

        return $response;
    }
}