<?php

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package home
 */

class FlutterVendor extends FlutterBaseController
{
        /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'wc/v2/flutter';//prefix must be wc/ or wc- to reuse check permission function in woo commerce

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_vendor_routes'));
    }

    public function register_flutter_vendor_routes()
    {
        register_rest_route( $this->namespace,  '/media', array(
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'upload_image' ),
				'args' => $this->get_params_upload()
			),
        ) );
        register_rest_route( $this->namespace,  '/product', array(
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'flutter_create_product' ),
			),
		) );
		register_rest_route( $this->namespace,  '/products/owner', array(
			array(
				'methods' =>"POST",
				'callback' => array( $this, 'flutter_get_products' ),
			),
		) );
    }

    public function get_params_upload(){
		$params = array(
			'media_attachment' => array(
				'required'          => true,
				'description'       => __( 'Image encoded as base64.', 'image-from-base64' ),
				'type'              => 'string'
			),
			'title' => array(
				'required'          => true,
				'description'       => __( 'The title for the object.', 'image-from-base64' ),
				'type'              => 'json'
			),
			'media_path' => array(
				'description'       => __( 'Path to directory where file will be uploaded.', 'image-from-base64' ),
				'type'              => 'string'
			)
		);
		return $params;
	}

    public function upload_image($request){
		$response = array();
		try{
			if( !empty($request['media_path']) ){
				$this->upload_dir = $request['media_path'];
				$this->upload_dir = '/' . trim($this->upload_dir, '/');
				add_filter( 'upload_dir', array( $this, 'change_wp_upload_dir' ) );
			}

			if( !class_exists('WP_REST_Attachments_Controller') ){
				throw new Exception('WP API not installed.');
            }
			$media_controller = new WP_REST_Attachments_Controller( 'attachment' );

			$filename = $request['title']['rendered'];

			$img = $request['media_attachment'];
			$decoded = base64_decode($img);

			$permission_check = $media_controller->create_item_permissions_check( $request );
			if( is_wp_error($permission_check) ){
				throw new Exception( $permission_check->get_error_message() );
			}

			$request->set_body($decoded);
			$request->add_header('Content-Disposition', "attachment;filename=\"{$filename}\"");
			$result = $media_controller->create_item( $request );
			$response = rest_ensure_response( $result );
		}
        catch(Exception $e){
			$response['result'] = "error";
			$response['message'] = $e->getMessage();
		}

		if( !empty($request['media_path']) ){
			remove_filter( 'upload_dir', array( $this, 'change_wp_upload_dir' ) );
		}

		return $response;
    } 

    public function flutter_create_product($request){
        $cookie = $request["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
		if (!$user_id) {
			return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
		}
        $user = get_userdata($user_id);
        $isSeller = in_array("seller",$user->roles) || in_array("wcfm_vendor",$user->roles);
        if ($isSeller) {
            $args = array(	   
                'post_author' => $user_id, 
                'post_content' => $request["content"],
                'post_status' => $request["status"] ?? "draft", // (Draft | Pending | Publish)
                'post_title' => $request["title"],
                'post_parent' => '',
                'post_type' => "product"
            ); 
            // Create a simple WooCommerce product
			$post_id = wp_insert_post( $args );
			$product = wc_get_product($post_id);

			if ( isset( $request['regular_price'] ) ) {
				$product->set_regular_price( $request['regular_price'] );
			}

			// Sale Price.
			if ( isset( $request['sale_price'] ) ) {
				$product->set_sale_price( $request['sale_price'] );
			}

			if ( isset( $request['date_on_sale_from'] ) ) {
				$product->set_date_on_sale_from( $request['date_on_sale_from'] );
			}

			if ( isset( $request['date_on_sale_from_gmt'] ) ) {
				$product->set_date_on_sale_from( $request['date_on_sale_from_gmt'] ? strtotime( $request['date_on_sale_from_gmt'] ) : null );
			}

			if ( isset( $request['date_on_sale_to'] ) ) {
				$product->set_date_on_sale_to( $request['date_on_sale_to'] );
			}

			if ( isset( $request['date_on_sale_to_gmt'] ) ) {
				$product->set_date_on_sale_to( $request['date_on_sale_to_gmt'] ? strtotime( $request['date_on_sale_to_gmt'] ) : null );
			}

			if ( isset( $request['image_ids'] ) ) {
				update_post_meta($post_id,'_product_image_gallery',join(",",$request['image_ids']));
				if(count($request['image_ids']) > 0){
					set_post_thumbnail( $post_id, $request['image_ids'][0] );
				}
			}

			wp_set_object_terms( $post_id, $request['product_type'] ?? "simple", 'product_type' );
			$product->save();
			$product = wc_get_product($post_id);
            return $product->get_data();
        }else{
            return parent::sendError("invalid_role","You must be seller to create product", 401);
        }
	}
	
	public function flutter_get_products($request){
		$cookie = $request["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
		if (!$user_id) {
			return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
		}

		$products = wc_get_products( array(
			'author' => $user_id,
			'limit'=>100
		));
		$ids = array();
		foreach ($products as $object) {
			$ids[] = $object->id;
		}
		if (count($ids) > 0) {
			$api = new WC_REST_Products_Controller();
			$params = array('status' => 'any','include'=>$ids);
			$request->set_query_params($params);

			$response = $api->get_items($request);
			return $response->get_data();
		}else{
			return [];
		}
		
	}
}

new FlutterVendor;
