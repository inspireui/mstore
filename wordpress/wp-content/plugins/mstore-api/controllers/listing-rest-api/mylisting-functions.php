<?php

function myListingExploreListings($request) 
{
    global $wpdb;

    if ( empty( $request['form_data'] ) || ! is_array( $request['form_data'] ) || empty( $request['listing_type'] ) ) {
        return [];
    }

    if ( ! ( $listing_type_obj = ( get_page_by_path( $request['listing_type'], OBJECT, 'case27_listing_type' ) ) ) ) {
        return [];
    }

    $type = new \MyListing\Src\Listing_Type( $listing_type_obj );
    $form_data = $request['form_data'];
    $starttime = microtime(true);

    $page = absint( isset($form_data['page']) ? $form_data['page'] : 0 );
    $per_page = absint( isset($form_data['per_page']) ? $form_data['per_page'] : c27()->get_setting('general_explore_listings_per_page', 9));
    $orderby = sanitize_text_field( isset($form_data['orderby']) ? $form_data['orderby'] : 'date' );
    $context = sanitize_text_field( isset( $form_data['context'] ) ? $form_data['context'] : 'advanced-search' );
    $args = [
        'order' => sanitize_text_field( isset($form_data['order']) ? $form_data['order'] : 'DESC' ),
        'offset' => $page * $per_page,
        'orderby' => $orderby,
        'posts_per_page' => $per_page,
        'tax_query' => [],
        'meta_query' => [],
        'fields' => 'ids',
        'recurring_dates' => [],
    ];

    \MyListing\Src\Queries\Explore_Listings::instance()->get_ordering_clauses( $args, $type, $form_data );

    // Make sure we're only querying listings of the requested listing type.
    if ( ! $type->is_global() ) {
        $args['meta_query']['listing_type_query'] = [
            'key'     => '_case27_listing_type',
            'value'   =>  $type->get_slug(),
            'compare' => '='
        ];
    }
	
    if ( $context === 'term-search' ) {
        $taxonomy = ! empty( $form_data['taxonomy'] ) ? sanitize_text_field( $form_data['taxonomy'] ) : false;
        $term = ! empty( $form_data['term'] ) ? sanitize_text_field( $form_data['term'] ) : false;

        if ( ! $taxonomy || ! $term || ! taxonomy_exists( $taxonomy ) ) {
            return [];
        }

        $tax_query_operator = apply_filters( 'mylisting/explore/match-all-terms', false ) === true ? 'AND' : 'IN';
        $args['tax_query'][] = [
            'taxonomy' => $taxonomy,
            'field' => 'term_id',
            'terms' => $term,
            'operator' => $tax_query_operator,
            'include_children' => $tax_query_operator !== 'AND',
        ];

        // add support for nearby order in single term page
        if ( isset( $form_data['proximity'], $form_data['lat'], $form_data['lng'] ) ) {
            $proximity = absint( $form_data['proximity'] );
            $location = isset( $form_data['search_location'] ) ? sanitize_text_field( stripslashes( $form_data['search_location'] ) ) : false;
            $lat = (float) $form_data['lat'];
            $lng = (float) $form_data['lng'];
            $units = isset($form_data['proximity_units']) && $form_data['proximity_units'] == 'mi' ? 'mi' : 'km';
            if ( $lat && $lng && $proximity && $location ) {
                $earth_radius = $units == 'mi' ? 3959 : 6371;
                $sql = $wpdb->prepare( \MyListing\Helpers::get_proximity_sql(), $earth_radius, $lat, $lng, $lat, $proximity );
                $post_ids = (array) $wpdb->get_results( $sql, OBJECT_K );
                if ( empty( $post_ids ) ) { $post_ids = ['none']; }
                $args['post__in'] = array_keys( (array) $post_ids );
                $args['search_location'] = '';
            }
        }
    } else {
        foreach ( (array) $type->get_advanced_filters() as $filter ) {
            $args = $filter->apply_to_query( $args, $form_data );
        }
    }

    $result = [];
    $listing_wrap = ! empty( $request['listing_wrap'] ) ? sanitize_text_field( $request['listing_wrap'] ) : '';
    $listing_wrap = apply_filters( 'mylisting/explore/listing-wrap', $listing_wrap );

    /**
     * Hook after the search args have been set, but before the query is executed.
     *
     * @since 1.7.0
     */
    do_action_ref_array( 'mylisting/get-listings/before-query', [ &$args, $type, $result ] );
	
    $listings = \MyListing\Src\Queries\Explore_Listings::instance()->query( $args );

    if(count($listings->posts) > 0){
        $in = '(' . implode(',', $listings->posts) .')';
        $table_name = $wpdb->prefix . "posts";
        $sql = "SELECT * FROM {$table_name}";
        $sql .= " WHERE {$table_name}.ID in ".$in;
        $sql = $wpdb->prepare($sql);
        $results = $wpdb->get_results($sql);

        return $results;
    }else{
        return [];
    }
}
?>