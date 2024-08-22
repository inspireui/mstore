<?php

class Flutter_WCFMmp_Store extends WCFMmp_Store {
	public function get_address_string( $branch_id = '' ) {
		$vendor_data = apply_filters( 'wcfmmp_store_vendor_data', $this->get_shop_info(), $this->id, $branch_id );

		$address = isset( $vendor_data['address'] ) ? $vendor_data['address'] : '';
		$addr_1  = isset( $vendor_data['address']['street_1'] ) ? $vendor_data['address']['street_1'] : '';
		$addr_2  = isset( $vendor_data['address']['street_2'] ) ? $vendor_data['address']['street_2'] : '';
		$city    = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
		$zip     = isset( $vendor_data['address']['zip'] ) ? $vendor_data['address']['zip'] : '';
		$country = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
		$state   = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
		
		// Country -> States
		$country_obj   = new WC_Countries();
		$countries     = $country_obj->countries;
		$states        = $country_obj->states;
		$country_name  = '';
		$state_name    = '';
		if( $country ) $country_name = $country;
		if( $state ) $state_name = $state;
		if( $country && isset( $countries[$country] ) ) {
			$country_name = $countries[$country];
		}
		if( $state && isset( $states[$country] ) && is_array( $states[$country] ) && !empty($states[$country]) ) {
			$state_name = isset($states[$country][$state]) ? $states[$country][$state] : '';
		}

		$placeholders = [ 
			'{address1}' 	=> $addr_1, 
			'{address2}' 	=> $addr_2, 
			'{city}' 		=> $city, 
			'{zip}' 		=> $zip, 
			'{state}' 		=> $state_name, 
			'{country}' 	=> $country_name
		];

		$format = apply_filters( 'wcfmmp_store_address_string_format', "{address1}, {address2}, {city}, {state}, {country} - {zip}" );

		$store_address = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $format );

		$store_address = str_replace( '"', '&quot;', $store_address );
	
		return apply_filters( 'wcfmmp_store_address_string', $store_address, $vendor_data );
	}
}