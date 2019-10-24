<?php
/**
 * The plugin bootstrap file
 *
 * @link:   http://www.acato.nl
 * @since   2018.1
 * @package WP_Rest_Cache_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:     WP REST Cache
 * Plugin URI:      http://www.acato.nl
 * Description:     Adds caching to the WP REST API
 * Version:         2019.4.2
 * Author:          Acato
 * Author URI:      http://www.acato.nl
 * Text Domain:     wp-rest-cache
 * Domain Path:     /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-autoloader.php';
spl_autoload_register( [ '\WP_Rest_Cache_Plugin\Includes\Autoloader', 'autoload' ] );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-rest-cache-activator.php
 */
register_activation_hook( __FILE__, [ '\WP_Rest_Cache_Plugin\Includes\Activator', 'activate' ] );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-rest-cache-deactivator.php
 */
register_deactivation_hook( __FILE__, [ '\WP_Rest_Cache_Plugin\Includes\Deactivator', 'deactivate' ] );

/**
 * Begins execution of the plugin.
 */
new \WP_Rest_Cache_Plugin\Includes\Plugin();
