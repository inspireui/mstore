=== Razorpay for WooCommerce ===
Contributors: razorpay
Tags: razorpay, payments, india, woocommerce, ecommerce
Requires at least: 3.9.2
Tested up to: 4.8
Stable tag: 1.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to use Razorpay payment gateway with the WooCommerce plugin.

== Description ==

This is the official Razorpay payment gateway plugin for WooCommerce. Allows you to accept credit cards, debit cards, netbanking, wallet, and UPI payments with the WooCommerce plugin. It uses a seamles integration, allowing the customer to pay on your website without being redirected away. This allows for refunds, works across all browsers, and is compatible with the latest WooCommerce.

This is compatible with WooCommerce>=2.4, including the new 3.0 release. It has been tested upto the 3.1.1 WooCommerce release.

== Installation ==

1. Install the plugin from the [Wordpress Plugin Directory](https://wordpress.org/plugins/woo-razorpay/).
2. To use this plugin correctly, you need to be able to make network requests. Please make sure that you have the php-curl extension installed.

== Dependencies == 

1. Wordpress v3.9.2 and later
2. Woocommerce v2.4 and later
3. PHP v5.6.0 and later
4. php-curl

== Configuration ==

1. Visit the WooCommerce settings page, and click on the Checkout/Payment Gateways tab.
2. Click on Razorpay to edit the settings. If you do not see Razorpay in the list at the top of the screen make sure you have activated the plugin in the WordPress Plugin Manager.
3. Enable the Payment Method, name it Credit Card / Debit Card / Internet Banking (this will show up on the payment page your customer sees), add in your Key id and Key Secret.
4. The Payment Action should be set to "Authorize and Capture". If you want to capture payments manually from the Dashboard after manual verification, set it to "Authorize".

== Changelog ==

= 1.6.2 =
* Fixes webhook capture flow by re-fetching payment and checking for status

= 1.6.1 =
* Fixes payment title/description in WC Checkout page.
* Adds WooCommerce version tested in the plugin metadata

= 1.6.0 = 
* Adds Razorpay Subscriptions plugin support.
* Code cleanup.

= 1.5.3 =
* Webhooks are now disabled by default ([#52](https://github.com/razorpay/razorpay-woocommerce/pull/52))

= 1.5.2 = 
* Fixed an issue with some websites "Pay now" button click not working. ([#50](https://github.com/razorpay/razorpay-woocommerce/pull/50))

= 1.5.1 = 
* Fixes backward compatibilty with older WooCommerce releases. ([#49](https://github.com/razorpay/razorpay-woocommerce/pull/49))

= 1.5.0 = 
* Javascript fixes for additional compatibility with other plugins ([#47](https://github.com/razorpay/razorpay-woocommerce/pull/47))
* Adds multi-currency support using [WooCommerce Currency Switcher](https://wordpress.org/plugins/woocommerce-currency-switcher/) plugin. ([#46](https://github.com/razorpay/razorpay-woocommerce/pull/46))

= 1.4.6 =
* Webhooks signature verification fix

= 1.4.4 =
* Added webhooks to the plugin (includes/razorpay-webhook.php) ([#18](https://github.com/razorpay/razorpay-woocommerce/pull/18))

= 1.4.2 =
* Added missing classes in the WordPress release (Utility.php was missing)

= 1.4.0 = 
* Added Support for WooCommerce 3.x ([#35](https://github.com/razorpay/razorpay-woocommerce/pull/35]))
* Fixes around discount coupon handling (Order Amount mismatch)
* Updates Razorpay SDK
* Improves Javascript Caching ([#39](https://github.com/razorpay/razorpay-woocommerce/pull/39]))
* Adds support for mobile browsers ([#37](https://github.com/razorpay/razorpay-woocommerce/pull/37]):)
    * Chrome on iOS
    * Facebook Browser
    * Internet Explorer Mobile
    * AOSP Browser
    * Opera Mini
    * Google Search App
    * Any other apps using webviews  
* Adds support for refunding payments from within WooCommerce

= 1.3.2 =
* Fixes a Notice about WC_Shortcode_Checkout->output being deprecated
* PR: [#28](https://github.com/razorpay/razorpay-woocommerce/pull/28])

= 1.3.1 =
* Improves Session management
* Diff: https://git.io/vHVBM

= 1.3.0 =
* Shifts to the official [Razorpay SDK](https://github.com/razorpay/razorpay-php)
* Shifts to the Razorpay Orders API. Allows for auto-capturing and improves success rates
* Wordpress Versions >=3.9.2 only are supported

= 1.2.11 = 
* Fixes issues with Safari and Internet Explorer

= 1.2.10 =
* Improves error handling in case customer clicks on cancel.
* Orders are now marked as failed if customer clicks cancel
* Note is added to the order mentioning that the customer cancelled the order.

= 1.2.9 =
* Fixed error handling and capture call

= 1.2.8 =
* Disables buttons while payment is in progress
* Refactors error message display

= 1.2.7 = 
* Redirects customer to order details page, as per WooCommerce guidelines.

= 1.2.6 =
* Adds manual capture option

== Support ==

Visit [razorpay.com](https://razorpay.com) for support requests or email us at <integrations@razorpay.com>.

== License ==

The Razorpay WooCommerce plugin is released under the GPLv2 license, same as that
of WordPress. See the LICENSE file for the complete LICENSE text.
