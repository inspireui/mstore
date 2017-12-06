<?php
$shophistic_lite_theme_data = wp_get_theme();
return array(
	'theme_name' => $shophistic_lite_theme_data['Name'], 
	'theme_slug' => sanitize_title($shophistic_lite_theme_data['Name']),
	'theme_author' => $shophistic_lite_theme_data['Author'],
	'theme_author_uri' => $shophistic_lite_theme_data['AuthorURI'],
	'theme_version' => $shophistic_lite_theme_data['Version'],
	'required_wp_version' => '3.1'
);