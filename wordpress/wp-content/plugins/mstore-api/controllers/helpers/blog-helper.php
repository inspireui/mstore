<?php

class FlutterBlogHelper
{
    public function get_blog_from_dynamic_link($request)
    {
        if (isset($request['url'])) {
            $url = $request['url'];
            $product_id = url_to_postid($url);
            if ($product_id) {
                $post = get_post($product_id);
                $controller = new WP_REST_Posts_Controller('post');
                $req = new WP_REST_Request('GET');
                $params = array('id' => $product_id);
                $req->set_query_params($params);
                $response = $controller->prepare_item_for_response($post, $req);
                $data = $controller->prepare_response_for_collection($response);
                return $data;
            }
        }
        return new WP_Error("invalid_url", "Not Found", array('status' => 404));
    }
    
    public function create_blog($request){
		$title = sanitize_text_field($request['title']);
		$content = sanitize_text_field($request['content']);
		$author = sanitize_text_field($request['author']);
		$date = sanitize_text_field($request['date']);
		$status = sanitize_text_field($request['status']);
		$categories = sanitize_text_field($request['categories']);
		$token = sanitize_text_field($request['token']);
		$image = sanitize_text_field($request['image']);

        if (isset($token)) {
            $cookie = urldecode(base64_decode($token));
        } else {
            return new WP_Error("unauthorized", "You are not allowed to do this", array('status' => 401));
        }
        $user_id = validateCookieLogin($cookie);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
		if($user_id != $author){
            return new WP_Error("unauthorized", "You are not allowed to do this", array('status' => 401));
		}
		if($status == 'publish' || $status == 'published'){
            return new WP_Error("unauthorized", "You are not allowed to publish this post", array('status' => 401));
        }
        $my_post = array(
            'post_author' => $user_id,
            'post_title'   => $title,
            'post_content' => $content,
            'post_status' => $status,
			'post_category' => [$categories],
        );
		
        $post_id = wp_insert_post( $my_post );
		
		if(isset($image)){
            $img_id = upload_image_from_mobile($image, 0 ,$user_id);
            if($img_id != false){
                set_post_thumbnail($post_id, $img_id);
            }
		}
		
        return new WP_REST_Response(
            [
                "status" => "success",
                "response" => '',
            ],
            200
        );
	}

    public function create_comment($request){
		$content = sanitize_text_field($request['content']);
		$token = sanitize_text_field($request['token']);
		$post_id = sanitize_text_field($request['post_id']);
		
        if (!empty($token)) {
            $cookie = urldecode(base64_decode($token));
        } else {
            return new WP_Error("unauthorized", "You are not allowed to do this", array('status' => 401));
        }
        $user_id = validateCookieLogin($cookie);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
		
		$is_approved = get_option( 'comment_moderation' ) ;
	    if ( comments_open( $post_id ) ) {
			$current_user = get_user_by('ID',$user_id);
                $data = array(
                'comment_post_ID'      => $post_id,
                'comment_content'      => $content,
                'user_id'              => $current_user->ID,
                'comment_author'       => $current_user->user_login,
                'comment_author_email' => $current_user->user_email,
                'comment_author_url'   => $current_user->user_url,
                'comment_approved'	   => empty($is_approved) ? 1 : 0,
            );
    
            $comment_id = wp_insert_comment( $data );
            if ( ! is_wp_error( $comment_id ) ) {
                return true;
            }else{
                return new WP_Error("error", $comment_id, array('status' => 400));
            }
        }else{
            return new WP_Error("comments_open", "This post doesn't allow to  comment", array('status' => 400));
        }
	}
}
?>