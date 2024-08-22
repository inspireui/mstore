<?php
/**
 * Add tab video setting to product data page
 */
add_filter( 'woocommerce_product_data_tabs', 'mstore_video_setting_custom_product_data_page', 99, 1 );

function mstore_video_setting_custom_product_data_page( $product_data_tabs ) {
	$product_data_tabs[ 'mstore_video_setting' ] = array(
		'label'  => 'Video Setting',
		'target' => 'mstore_video_setting',
	);

	return $product_data_tabs;
}

/**
 * Define fields for video setting
 */
add_action( 'woocommerce_product_data_panels', 'mstore_video_settings_custom_product_data_fields' );
function mstore_video_settings_custom_product_data_fields() {
	global $woocommerce, $post;
	?>
    <div id="mstore_video_setting" class="panel woocommerce_options_panel">
        <script type="text/javascript">
            jQuery( function($){
            // on upload button click
            $( 'body' ).on( 'click', '.rudr-upload', function( event ){
                event.preventDefault();
                
                const button = $(this)
                const imageId = button.next().next().val();
                
                const customUploader = wp.media({
                    title: 'Select video',
                    library : {
                        type : 'video'
                    },
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                }).on( 'select', function() {
                    const attachment = customUploader.state().get( 'selection' ).first().toJSON();
                    $("#_mstore_video_url").val(attachment.url)
                })
                
                customUploader.open()
            });
        });
        </script>
        

		<?php
		woocommerce_wp_text_input(
			array(
				'id'          => '_mstore_video_url',
				'label'       => 'Video URL',
				'description' => 'Enter the URL for the video you want to show in videos list in the app. Support mp4 url.',
				'default'     => '',
				'desc_tip'    => true,
                'data_type'   => 'url',
				'value'       => get_post_meta( get_the_ID(), '_mstore_video_url', true )
			)
		);
        ?>
       
        <p class="form-field">
            <span>OR</span>
            <a href="#" class="button rudr-upload">Choose Video</a>
        </p>
        

        <?php
		woocommerce_wp_text_input(
			array(
				'id'          => '_mstore_video_title',
				'label'       => 'Video Title',
				'description' => 'Please enter the title video',
				'default'     => '',
				'desc_tip'    => true,
				'value'       => get_post_meta( get_the_ID(), '_mstore_video_title', true )
			)
		);
		woocommerce_wp_textarea_input(
			array(
				'id'          => '_mstore_video_description',
				'label'       => 'Video Description',
				'description' => 'Please enter the description video',
				'default'     => '',
				'desc_tip'    => true,
				'value'       => get_post_meta( get_the_ID(), '_mstore_video_description', true )
			)
		);
		?>
    </div>
	<?php
}


add_action( 'woocommerce_process_product_meta', 'mstore_video_setting_save_configs_woocommerce_process_product_meta' );

function mstore_video_setting_save_configs_woocommerce_process_product_meta( $post_id ) {
	$url         = $_POST[ '_mstore_video_url' ] ?? '';
	$title        = $_POST[ '_mstore_video_title' ] ?? '';
	$desc = $_POST[ '_mstore_video_description' ] ?? '';

	update_post_meta( $post_id, '_mstore_video_url', $url );
	update_post_meta( $post_id,'_mstore_video_title', $title );
	update_post_meta( $post_id, '_mstore_video_description', $desc );
}
?>