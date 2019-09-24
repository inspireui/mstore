<?php
/* Original code from: 
 * http://bradt.ca/archives/image-crop-position-in-wordpress/
 *
 * Modified to WordPress Answers:
 * http://wordpress.stackexchange.com/q/51920/12615
 *
 * Check the function bt_image_make_intermediate_size
 * That's where the Thumbnails renaming occurs and all added images must be inserted
 */

/* Example Usage:
 * bt_add_image_size( 'product-screenshot', 300, 300, array( 'left', 'top' ) );
 * bt_add_image_size( 'product-feature', 460, 345, array( 'center', 'top' ) );
 */
add_filter( 'intermediate_image_sizes_advanced', 'bt_intermediate_image_sizes_advanced' );
add_filter( 'wp_generate_attachment_metadata', 'bt_generate_attachment_metadata', 10, 2 );

/**
 * Registers a new image size with cropping positions
 *
 * The $crop parameter works as in the 'add_image_size' function taking true or
 * false values. If set to true, the default cropping position is 'center', 'center'.
 * 
 * The $crop parameter also takes an array of the format
 * array( x_crop_position, y_crop_position )
 * x_crop_position can be 'left', 'center', 'right'
 * y_crop_position can be 'top', 'center', 'bottom'
 * 
 * @param string $name Image size identifier.
 * @param int $width Image width.
 * @param int $height Image height.
 * @param bool|array $crop Optional, default is false. Whether to crop image to specified height and width or resize. An array can specify positioning of the crop area.
 * @return bool|array False, if no image was created. Metadata array on success.
 */
function bt_add_image_size( $name, $width = 0, $height = 0, $crop = false ) {
	global $_wp_additional_image_sizes;
	$_wp_additional_image_sizes[$name] = array( 'width' => absint( $width ), 'height' => absint( $height ), 'crop' => $crop );
}


/**
 * Returning no sizes (an empty array) will force
 * wp_generate_attachment_metadata to skip creating intermediate image sizes on
 * upload, then we can run our own resizing functions by hooking into the
 * 'wp_generate_attachment_metadata' filter
 */
function bt_intermediate_image_sizes_advanced() {
	return array();
}


function bt_generate_attachment_metadata( $metadata, $attachment_id ) {
    $attachment = get_post( $attachment_id );
    
    $uploadPath = wp_upload_dir();
    $file = path_join($uploadPath['basedir'], $metadata['file']);

	if ( !preg_match('!^image/!', get_post_mime_type( $attachment )) || !file_is_displayable_image( $file ) ) return $metadata;


	global $_wp_additional_image_sizes;


    foreach ( get_intermediate_image_sizes() as $s ) {
        $sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => FALSE );
        if ( isset( $_wp_additional_image_sizes[$s]['width'] ) )
            $sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
        else
            $sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
        if ( isset( $_wp_additional_image_sizes[$s]['height'] ) )
            $sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
        else
            $sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
        if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) )
            $sizes[$s]['crop'] = $_wp_additional_image_sizes[$s]['crop'];
        else
            $sizes[$s]['crop'] = get_option( "{$s}_crop" );
    }

    foreach ( $sizes as $size => $size_data ) {
        $resized = bt_image_make_intermediate_size( $file, $size_data['width'], $size_data['height'], $size_data['crop'], $size );
        if ( $resized )
            $metadata['sizes'][$size] = $resized;
    }
    
    return $metadata;
}


/**
 * Resize an image to make a thumbnail or intermediate size.
 *
 * The returned array has the file size, the image width, and image height. The
 * filter 'image_make_intermediate_size' can be used to hook in and change the
 * values of the returned array. The only parameter is the resized file path.
 *
 * @param string $file File path.
 * @param int $width Image width.
 * @param int $height Image height.
 * @param bool|array $crop Optional, default is false. Whether to crop image to specified height and width or resize. An array can specify positioning of the crop area.
 * @return bool|array False, if no image was created. Metadata array on success.
 */
function bt_image_make_intermediate_size( $file, $width, $height, $crop = false, $size ) {
	if ( $width || $height ) {
		switch($size) {
			case 'thumbnail':
				$suffix = 'small';
				break;
			case 'medium':
				$suffix = 'medium';
				break;
			case 'large':
				$suffix = 'large';
				break;
			default:
				$suffix = null;
				break;
		}
		$resized_file = bt_image_resize( $file, $width, $height, $crop, $suffix, null, 90 );
		if ( !is_wp_error( $resized_file ) && $resized_file && $info = getimagesize( $resized_file ) ) {
			$resized_file = apply_filters('image_make_intermediate_size', $resized_file);
			return array(
				'file' => wp_basename( $resized_file ),
				'width' => $info[0],
				'height' => $info[1],
			);
		}
	}
	return false;
}



/**
 * Retrieve calculated resized dimensions for use in imagecopyresampled().
 *
 * Calculate dimensions and coordinates for a resized image that fits within a
 * specified width and height. If $crop is true, the largest matching central
 * portion of the image will be cropped out and resized to the required size.
 *
 * @param int $orig_w Original width.
 * @param int $orig_h Original height.
 * @param int $dest_w New width.
 * @param int $dest_h New height.
 * @param bool $crop Optional, default is false. Whether to crop image or resize.
 * @return bool|array False, on failure. Returned array matches parameters for imagecopyresampled() PHP function.
 */
function bt_image_resize_dimensions($orig_w, $orig_h, $dest_w, $dest_h, $crop = false) {

	if ($orig_w <= 0 || $orig_h <= 0)
		return false;
	// at least one of dest_w or dest_h must be specific
	if ($dest_w <= 0 && $dest_h <= 0)
		return false;

	if ( $crop ) {
		// crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
		$aspect_ratio = $orig_w / $orig_h;
		$new_w = min($dest_w, $orig_w);
		$new_h = min($dest_h, $orig_h);

		if ( !$new_w ) {
			$new_w = intval($new_h * $aspect_ratio);
		}

		if ( !$new_h ) {
			$new_h = intval($new_w / $aspect_ratio);
		}

		$size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

		$crop_w = round($new_w / $size_ratio);
		$crop_h = round($new_h / $size_ratio);

        if ( !is_array( $crop ) || count( $crop ) != 2 ) {
			$crop = apply_filters( 'image_resize_crop_default', array( 'center', 'center' ) );
		}
		
		switch ( $crop[0] ) {
			case 'left': $s_x = 0; break;
			case 'right': $s_x = $orig_w - $crop_w; break;
			default: $s_x = floor( ( $orig_w - $crop_w ) / 2 );
		}

		switch ( $crop[1] ) {
			case 'top': $s_y = 0; break;
			case 'bottom': $s_y = $orig_h - $crop_h; break;
			default: $s_y = floor( ( $orig_h - $crop_h ) / 2 );
		}
	} else {
		// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
		$crop_w = $orig_w;
		$crop_h = $orig_h;

		$s_x = 0;
		$s_y = 0;

		list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
	}

	// if the resulting image would be the same size or larger we don't want to resize it
	// if ( $new_w >= $orig_w && $new_h >= $orig_h )
	// 	return false;

	// the return array matches the parameters to imagecopyresampled()
	// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
	return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );

}


/**
 * Scale down an image to fit a particular size and save a new copy of the image.
 *
 * The PNG transparency will be preserved using the function, as well as the
 * image type. If the file going in is PNG, then the resized image is going to
 * be PNG. The only supported image types are PNG, GIF, and JPEG.
 *
 * Some functionality requires API to exist, so some PHP version may lose out
 * support. This is not the fault of WordPress (where functionality is
 * downgraded, not actual defects), but of your PHP version.
 *
 * @since 2.5.0
 *
 * @param string $file Image file path.
 * @param int $max_w Maximum width to resize to.
 * @param int $max_h Maximum height to resize to.
 * @param bool $crop Optional. Whether to crop image or resize.
 * @param string $suffix Optional. File Suffix.
 * @param string $dest_path Optional. New image file path.
 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
 * @return mixed WP_Error on failure. String with new destination path.
 */
function bt_image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {

	$image = wp_load_image( $file );
	if ( !is_resource( $image ) )
		return new WP_Error( 'error_loading_image', $image, $file );

	$size = @getimagesize( $file );
	if ( !$size )
		return new WP_Error('invalid_image', __('Could not read image size'), $file);
	list($orig_w, $orig_h, $orig_type) = $size;

	// Rotate if EXIF 'Orientation' is set
	// This code is from the reverted patch at
	// http://core.trac.wordpress.org/changeset/11746/trunk/wp-includes/media.php
	$rotate = false;
	if ( is_callable( 'exif_read_data' ) && in_array( $orig_type, apply_filters( 'wp_read_image_metadata_types', array( IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM ) ) ) ) {
		$exif = @exif_read_data( $file, null, true );
		if ( $exif && isset( $exif['IFD0'] ) && is_array( $exif['IFD0'] ) && isset( $exif['IFD0']['Orientation'] ) ) {
			if ( 6 == $exif['IFD0']['Orientation'] )
				$rotate = 90;
			elseif ( 8 == $exif['IFD0']['Orientation'] )
				$rotate = 270;
		}
	}
	
	if ( $rotate )
		list($max_h,$max_w) = array($max_w,$max_h);

	$dims = bt_image_resize_dimensions($orig_w, $orig_h, $max_w, $max_h, $crop);
	if ( !$dims )
		return new WP_Error( 'error_getting_dimensions', __('Could not calculate resized image dimensions') );
	list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;

	$newimage = wp_imagecreatetruecolor( $dst_w, $dst_h );

	if ( $rotate )
		list($src_y,$src_x) = array($src_x,$src_y);

	imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

	// convert from full colors to index colors, like original PNG.
	if ( IMAGETYPE_PNG == $orig_type && function_exists('imageistruecolor') && !imageistruecolor( $image ) )
		imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );

	// we don't need the original in memory anymore
	imagedestroy( $image );

	// $suffix will be appended to the destination filename, just before the extension
	if ( !$suffix ) {
		if ( $rotate )
			$suffix = "{$dst_h}x{$dst_w}";
		else
			$suffix = "{$dst_w}x{$dst_h}";
	}

	$info = pathinfo($file);
	$dir = $info['dirname'];
	$ext = $info['extension'];
	$name = wp_basename($file, ".$ext");

	if ( !is_null($dest_path) and $_dest_path = realpath($dest_path) )
		$dir = $_dest_path;
	$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

	if ( IMAGETYPE_GIF == $orig_type ) {
		if ( !imagegif( $newimage, $destfilename ) )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	} elseif ( IMAGETYPE_PNG == $orig_type ) {
		if ( !imagepng( $newimage, $destfilename ) )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	} else {
		if ( $rotate ) {
			$newimage = _rotate_image_resource( $newimage, 360 - $rotate );
		}
		
		// all other formats are converted to jpg
		$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
		$return = imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality, 'image_resize' ) );
		if ( !$return )
			return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
	}

	imagedestroy( $newimage );

	// Set correct file permissions
	$stat = stat( dirname( $destfilename ));
	$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
	@ chmod( $destfilename, $perms );

	return $destfilename;
}