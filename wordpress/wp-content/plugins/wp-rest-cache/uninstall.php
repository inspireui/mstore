<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link: http://www.acato.nl
 * @since 2018.1
 *
 * @package WP_Rest_Cache_Plugin
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
