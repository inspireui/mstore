<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package FlowFlow
 */

class FlutterFlowFlow extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_flow_flow';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_flow_flow_routes'));
    }

    public function register_flutter_flow_flow_routes()
    {
        register_rest_route($this->namespace, '/stream', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_stream_data'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function get_stream_data($request)
    {
        if (!is_plugin_active('flow-flow/flow-flow.php')) {
            return parent::send_invalid_plugin_error("You need to install Flow-Flow plugin to use this api");
        }
        global $wpdb;
        $stream_id = sanitize_text_field($request['stream_id']);
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
        $offset = ($page - 1) * $per_page;

        $table_prefix = $wpdb->prefix . 'ff_';
        $context = [
            'slug'              => 'flow-flow',
            'slug_down'         => 'flow_flow',
            'table_name_prefix' => $wpdb->prefix . 'ff_',
            'version' 			=> '4.9.5',
            'faq_url' 			=> 'https://docs.social-streams.com/',
            'count_posts_4init'	=> 30
        ];
        $dbm = new flow\db\FFDBManager($context);

		$boosted = false;
		$dbm->dataInit(true, false, $boosted);
		$stream = $dbm->getStream($stream_id);
		if (isset($stream)) {
            $conditions = "stream.stream_id = ".$stream_id." AND cach.enabled = 1 AND cach.boosted = 'nope'";
            $order = "post.smart_order, post.post_timestamp DESC";
            $data = $dbm->getPostsIf($this->getGetFields(),$conditions,$order,$offset, $per_page);
        }
        return $data;
    }

    private function getGetFields(){
		$select  = "post.post_id as id, post.post_type as type, post.user_nickname as nickname, ";
		$select .= "post.user_pic as userpic, ";
		$select .= "post.post_timestamp as system_timestamp, ";
		$select .= "post.location as location, ";
		$select .= "post.user_link as userlink, post.post_permalink as permalink, ";
		$select .= "post.image_url, post.image_width, post.image_height, post.media_url, post.media_type, ";
		$select .= "post.user_counts_media, post.user_counts_follows, post.user_counts_followed_by, ";
		$select .= "post.media_width, post.media_height, post.post_source, post.post_additional, post.feed_id, ";
		$select .= "post.carousel_size ";
		$select .= ", post.user_screenname as screenname, post.post_header, post.post_text as text, post.user_bio ";
		return $select;
	}
}

new FlutterFlowFlow;