=== WooCommerce Legacy REST API ===
Contributors: automattic, konamiman
Tags: woo, woocommerce, rest api
Requires at least: 6.2
Tested up to: 6.3
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The WooCommerce Legacy REST API, which is now part of WooCommerce itself but will be removed in WooCommerce 9.0.


== Description ==

[The Legacy REST API will no longer part of WooCommerce as of version 9.0](https://developer.woocommerce.com/2023/10/03/the-legacy-rest-api-will-move-to-a-dedicated-extension-in-woocommerce-9-0/). This plugin restores the full functionality of the removed Legacy REST API code in WooCommerce 9.0 and later versions.

For all intents and purposes, having this plugin installed and active in WooCommerce 9.0 and newer versions is equivalent to enabling the Legacy REST API in WooCommerce 8.9 and older versions (via WooCommerce - Settings - Advanced - Legacy API). All the endpoints work the same way, and existing user keys also continue working.

On the other hand, installing this plugin together with WooCommerce 8.9 or an older version is safe: the plugin detects that the Legacy REST API is still part of WooCommerce and doesn't initialize itself as to not interfere with the built-in code.

Please note that **the Legacy REST API is not compatible with [High-Performance Order Storage](https://woocommerce.com/document/high-performance-order-storage/)**. Upgrading the code that relies on the Legacy REST API to use the current WooCommerce REST API instead is highly recommended.


== Installation ==

Simply install and activate the plugin. In WooCommerce 8.9 and earlier nothing will change. Starting with WooCommerce 9.0 having the plugin installed will provide the full functionality of the Legacy REST API.

Note that since the Legacy REST API is not compatible with HPOS, once the plugin is active you will see a "WooCommerce has detected that some of your active plugins are incompatible with currently enabled WooCommerce features" notice in your WordPress admin area.


== Changelog ==

= 1.0.0 2023-11-01 =

First version, replicates the WooCommerce Legacy REST API v3.1.0 present in WooCommerce 8.3.

= 1.0.1 2024-01-08 =

- Replace the text domain for human-readable strings from 'woocommerce' to 'woocommerce-legacy-rest-api'.
- Add sanitization for data received via query string arguments and the $_SERVER array.

= 1.0.2 2024-05-01 =

- Add a dismissable admin notice indicating that the Legacy REST API is not compatible with HPOS.
- The notice will appear if the orders table is (or has been) selected as the orders data store in the WooCommerce features settings page, and will disappear when that ceases to be true. Once the notice is dismissed it will never appear again.

= 1.0.3 2024-05-15 =

- Fix a bug introduced in 1.0.2 that caused a fatal error when checking if HPOS is enabled.

= 1.0.4 2024-05-16 =

- Correct a problem in which the attempted removal of admin notices (warning of HPOS incompatibility) could lead to a fatal error during plugin deactivation.
