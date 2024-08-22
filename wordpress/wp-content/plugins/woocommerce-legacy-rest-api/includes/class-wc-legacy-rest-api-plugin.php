<?php

defined( 'ABSPATH' ) || exit;

/**
 * This class controls initialization, activation and deactivation of the plugin.
 */
class WC_Legacy_REST_API_Plugin
{
    /**
     * Holds the path of the main plugin file.
     */
    private static $plugin_filename;

    /**
     * Plugin initialization, to be invoked inside the woocommerce_init hook.
     */
    public static function on_woocommerce_init() {
        if( ! self::legacy_api_still_in_woocommerce() ) {
            require_once __DIR__ . '/legacy/class-wc-legacy-api.php';
            require_once __DIR__ . '/class-wc-api.php';

            WC()->api = new WC_API();
            WC()->api->init();
            WC()->api->add_endpoint();
        }

        if( ! self::maybe_add_hpos_incompatibility_admin_notice() ) {
            self::maybe_remove_hpos_incompatibility_admin_notice();
        }
    }

    /**
     * Register the proper hook handlers.
     * 
     * @param string $plugin_filename The path to the main plugin file.
     */
    public static function register_hook_handlers( $plugin_filename ) {
        self::$plugin_filename = $plugin_filename;

        register_activation_hook( $plugin_filename, self::class . '::on_plugin_activated' );
        register_deactivation_hook( $plugin_filename, self::class . '::on_plugin_deactivated' );
        register_uninstall_hook( $plugin_filename, self::class . '::on_plugin_deactivated' );

        add_action( 'before_woocommerce_init', self::class . '::on_before_woocommerce_init' );
        add_action( 'woocommerce_init', self::class . '::on_woocommerce_init' );

        // 1717192800 = June 1st, 2024
        if( time() < 1717192800 ) {
            add_action( 'all_plugins', self::class . '::on_all_plugins' );
        }
    }

    /**
     * Act on plugin activation.
     */
    public static function on_plugin_activated() {
        if( ! self::woocommerce_is_active() ) {
            return;
        }
        
        if( ! self::legacy_api_still_in_woocommerce() ) {
            require_once __DIR__ . '/legacy/class-wc-legacy-api.php';
            require_once __DIR__ . '/class-wc-api.php';

            update_option( 'woocommerce_api_enabled', 'yes' );
            WC_API::add_endpoint();
        }
    }

    /**
     * Add the "legacy REST API and HPOS are incompatible" admin notice if needed.
     * 
     * @returns bool True if the notice has been added, false otherwise.
     */
    private static function maybe_add_hpos_incompatibility_admin_notice() {
        if( ! self::hpos_is_enabled() || self::user_has_dismissed_admin_notice( 'legacy_rest_api_is_incompatible_with_hpos' ) ) {
            return false;
        }
    
        if ( ! WC_Admin_Notices::has_notice( 'legacy_rest_api_is_incompatible_with_hpos' ) ) {
            $features_page_url = admin_url( 'admin.php?page=wc-settings&tab=advanced&section=features' );
            WC_Admin_Notices::add_custom_notice(
                'legacy_rest_api_is_incompatible_with_hpos',
                sprintf(
                    wpautop( __( '⚠ <b>The Legacy REST API plugin and HPOS are both active on this site.</b><br/><br/>Please be aware that the WooCommerce Legacy REST API is <b>not</b> compatible with HPOS. <a target="_blank" href="%s">Manage features</a>', 'woocommerce-legacy-rest-api' ) ),
                    $features_page_url
                )
            );

            return true;
        }

        return false;
    }

    /**
     * Check if HPOS is currently in use.
     * 
     * @returns bool True if HPOS is currently in use.
     */
    private static function hpos_is_enabled(): bool {
        return class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    /**
     * Remove the "legacy REST API and HPOS are incompatible" admin notice if needed.
     */
    private static function maybe_remove_hpos_incompatibility_admin_notice() {
        if ( WC_Admin_Notices::has_notice( 'legacy_rest_api_is_incompatible_with_hpos' ) && ! self::hpos_is_enabled() ) {
            WC_Admin_Notices::remove_notice( 'legacy_rest_api_is_incompatible_with_hpos' );
        }
    }

    /**
     * Act on plugin deactivation/uninstall.
     */
    public static function on_plugin_deactivated() {
        if( ! self::woocommerce_is_active() ) {
            return;
        }

        if( ! self::legacy_api_still_in_woocommerce() ) {
            update_option( 'woocommerce_api_enabled', 'no' );
            flush_rewrite_rules();
        }

        if ( WC_Admin_Notices::has_notice( 'legacy_rest_api_is_incompatible_with_hpos' ) ) {
            WC_Admin_Notices::remove_notice( 'legacy_rest_api_is_incompatible_with_hpos' );
        }
    }

    /**
     * Handler for the before_woocommerce_init hook, needed to declare HPOS incompatibility.
     */
    public static function on_before_woocommerce_init() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', self::$plugin_filename, false );
        }
    }

    /**
     * Checks if the legacy REST API is still present in the current WooCommerce install.
     */
    private static function legacy_api_still_in_woocommerce() {
        return class_exists( 'WC_API' ) && ! property_exists( 'WC_API', 'legacy_api_is_in_separate_plugin' );
    }

    /**
     * Handler for the all_plugins hook, used to change the description of the plugin if it's seen before June 2024.
     */
    public static function on_all_plugins( $all_plugins ) {
        $plugin_relative_path = str_replace( WP_PLUGIN_DIR . '/', '', self::$plugin_filename );
        $all_plugins[ $plugin_relative_path ][ 'Description' ] = 'The legacy WooCommerce REST API, which is now part of WooCommerce itself but will be removed in WooCommerce 9.0.';
        return $all_plugins;
    }

    /**
     * Check if WooCommerce itself is active in the site.
     */
    private static function woocommerce_is_active() {
        return class_exists( 'WooCommerce' );
    }

    /**
     * Check if the current user has dismissed an admin notice.
     * 
     * @param string $notice_id Id of the notice.
     */
    private static function user_has_dismissed_admin_notice( $notice_id ) {
        if ( method_exists( 'WC_Admin_Notices', 'user_has_dismissed_notice' ) ) {
            return WC_Admin_Notices::user_has_dismissed_notice( $notice_id );
        } else {
            return (bool) get_user_meta( get_current_user_id(), "dismissed_{$notice_id}_notice", true );
        }
    }
}