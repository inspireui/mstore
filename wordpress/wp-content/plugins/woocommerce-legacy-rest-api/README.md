## WooCommerce Legacy REST API

[The Legacy REST API is no longer part of WooCommerce as of version 9.0](https://developer.woocommerce.com/2023/10/03/the-legacy-rest-api-will-move-to-a-dedicated-extension-in-woocommerce-9-0/). This plugin restores the full functionality of the removed Legacy REST API code in WooCommerce 9.0 and later versions.

For all intents and purposes, having this plugin installed and active in WooCommerce 9.0 and newer versions is equivalent to enabling the Legacy REST API in WooCommerce 8.9 and older versions (via WooCommerce - Settings - Advanced - Legacy API). All the endpoints work the same way, and existing user keys also continue working.

On the other hand, installing this plugin together with WooCommerce 8.9 or an older version is safe: the plugin detects that the Legacy REST API is still part of WooCommerce and doesn't initialize itself as to not interfere with the built-in code.

Please note that **the Legacy REST API is not compatible with [High-Performance Order Storage](https://woocommerce.com/document/high-performance-order-storage/)**: this will cause a _"WooCommerce has detected that some of your active plugins are incompatible with currently enabled WooCommerce features"_ notice in the top of your WordPress admin area once the plugin is activated. Upgrading the code that relies on the Legacy REST API to use the current WooCommerce REST API instead is highly recommended.
