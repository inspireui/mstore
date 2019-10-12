=== WooCommerce PayPal Powered by Braintree Payment Gateway ===
Contributors: automattic, akeda, allendav, royho, slash1andy, woosteve, spraveenitpro, mikedmoore, fernashes, shellbeezy, danieldudzic, dsmithweb, fullysupportedphil, corsonr, zandyring, skyverge
Tags: ecommerce, e-commerce, commerce, woothemes, wordpress ecommerce, store, sales, sell, shop, shopping, cart, checkout, configurable, paypal, braintree
Requires at least: 4.4
Tested up to: 5.2.3
Requires PHP: 5.4
Stable tag: 2.3.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Accept PayPal, Credit Cards, and Debit Cards on your WooCommerce store.

== Description ==

This is a PayPal Powered by Braintree Payment Gateway for WooCommerce, which will let you accept **credit card, debit card, and PayPal payments** on your WooCommerce store via Braintree.

PayPal Powered by Braintree allows you to securely sell your products online using Hosted Fields to help you meet security requirements without losing flexibility and an integrated checkout process. Hosted Fields are little iFrames, hosted on PayPal's servers, that fit inside the checkout form elements and provide a secure means for your customers to enter their card information.

This plugin supports [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) to let you sell products that require recurring billing, and [WooCommerce Pre-Orders](https://woocommerce.com/products/woocommerce-pre-orders/) to let you take payment up-front for products that are coming soon, or to automatically charge customers when pre-orders ship.

Checkout is seamless either via credit cards or PayPal, and customers can save a payment method to their account for future use or manage saved payment methods with a few clicks.

For US merchants, connecting to PayPal is as simple as clicking a button - no complicated API keys to cut and paste. For merchants outside the US, you'll be up and running once you enter your existing Braintree account credentials.

= Powering Advanced Payments =

PayPal Powered by Braintree provides several advanced features for transaction processing and payment method management.

 - Meets [PCI Compliance SAQ-A standards](https://www.pcisecuritystandards.org/documents/Understanding_SAQs_PCI_DSS_v3.pdf)
 - Supports official Subscriptions & Pre-Orders plugins
 - Customers can securely save payment methods or link a PayPal account to your site
 - Process refunds, void transactions, and capture charges right from within WooCommerce
 - Ability to add multiple merchant IDs to support multi-currency when used with a currency switcher
 - Supports Braintree Advanced Fraud tools and Kount Direct (if enabled)
 - Supports 3D Secure if enabled in your Braintree account
 - and more!

== Installation ==

= Minimum Requirements =

* WordPress 4.4 or greater
* WooCommerce 2.6 or greater
* PHP version 5.4 or greater
* cURL

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type `WooCommerce PayPal Powered by Braintree` and click "Search Plugins". Once you’ve found our plugin, you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

If on the off-chance you do encounter issues with the shop/category pages after an update you simply need to flush the permalinks by going to WordPress > Settings > Permalinks and hitting 'save'. That should return things to normal.

== Frequently Asked Questions ==

= Does this plugin work with credit cards or just PayPal? =

This plugin supports payments using both credit and debit cards as well as PayPal.

= Does this support recurring payments, like for subscriptions? =

Yes! To implement recurring billing, you must use [WooCommerce Subscriptions](http://woocommerce.com/products/woocommerce-subscriptions/) with this plugin.

= What currencies can I use? =

This plugin supports all countries in which Braintree is available. You can use your native currency, or you can add multiple merchant IDs to process different currencies via different Braintree accounts. To use multi-currency, your site must use a **currency switcher** to adjust the order currency (may require purchase). We've tested this plugin with the [Aelia Currency Switcher](https://aelia.co/shop/currency-switcher-woocommerce/) (requires purchase).

= Can non-US merchants use this? =

Yes! Merchants outside the US must manually enter API credentials from Braintree rather than using the Braintree connect workflow, but the plugin functions the same way for US and non-US merchants.

= Does this support both production mode and sandbox mode for testing? =

Yes it does - production and sandbox mode is driven by how you connect. You may choose to connect in either mode, and disconnect and reconnect in the other mode whenever you want.

= Where can I find documentation? =

For help setting up and configuring, please refer to our [user guide](http://docs.woocommerce.com/document/woocommerce-gateway-paypal-powered-by-braintree/).

= Why isn't PayPal working? Credit cards work fine. =

Make sure PayPal is enabled on your Braintree account by following the [Braintree PayPal Setup Guide](https://articles.braintreepayments.com/guides/paypal/setup-guide).

= What do I need to do if I'm updating from the retired SkyVerge Braintree plugin from WooCommerce.com? =

The old merchant account settings from the retired plugin will be migrated, and card tokens will migrate as well. As of version 2.0, this plugin is a drop-in replacement for the premium Braintree plugin, so you can safely upgrade to this plugin, which will deactivate the retired Braintree plugin.

= Can I use this extension just for PayPal and use another gateway for Credit Cards? =

Yes! Please upgrade to version 2.0 to enable gateways individually. If you want to use the PayPal gateway alone and plan to use Subscriptions, please [view our user guide](https://docs.woocommerce.com/document/woocommerce-gateway-paypal-powered-by-braintree/#paypal-only) for set up tips.

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the Plugin Forum.

= Will this plugin work with my theme? =

Yes, this plugin will work with any theme, but may require some styling to make it match nicely. Please see
our [codex](http://docs.woocommerce.com/documentation/plugins/woocommerce/woocommerce-codex/) for help. If you're
looking for a theme with built in WooCommerce integration we recommend [Storefront](http://www.woocommerce.com/storefront/).

= Where can I request new features or report bugs? =

New feature requests and bugs reports can be made in the plugin forum.

== Screenshots ==

1. US Merchants: Connect to Braintree or enter credentials
2. Non-US Merchants: Enter Braintree credentials
3. Credit Card gateway settings
4. PayPal gateway settings
5. Advanced credit card settings
6. Checkout with PayPal directly from the cart.
7. Checkout with PayPal or credit / debit cards.

== Changelog ==

= 2019.10.03 - version 2.3.0 =
* Feature - PayPal buy-now buttons can now be added to product pages
* Tweak - Enable PayPal Credit by default on new installs
* Fix - Fix a styling issue with the merchant account ID field in settings
* Fix - Fix a bug with a regular expression being used in PHP 7.3+

= 2019.09.12 - version 2.2.7 =
* Fix - Fix JavaScript error blocking payments with 3D Secure from the Pay Order page

= 2019.08.07 - version 2.2.6 =
* Tweak - Add support for 3D Secure 2.0
* Misc - Add support for WooCommerce 3.7

= 2019.06.06 - version 2.2.5 =
* Fix - Regenerate client tokens on checkout refresh to use the customer's latest currency
* Fix - Ensure saved PayPal accounts display their associated email address if no nickname is set

= 2019.04.01 - version 2.2.4 =
* Fix - Prevent an error when completing pre-orders that were placed using the PayPal gateway

= 2019.03.20 - version 2.2.3 =
* Fix - Ensure Kount merchant ID is set in device data for stores using advanced fraud tools via Kount

= 2019.02.28 - version 2.2.2 =
* Fix - Prevent JS errors when reloading the payment form in IE and Edge

= 2019.02.06 - version 2.2.1 =
* Fix - Ensure updated order totals are used for validating 3D Secure when the checkout is refreshed
* Fix - Prevent 3D Secure errors when non-US region codes are used during validation
* Fix - Ensure payment forms are available for orders that start at $0 but require payment after shipping selection
* Fix - Update the recurring flag for new API requirements when processing subscription payments
* Misc - Reorder manual connection setting inputs to match documentation

= 2018.11.12 - version 2.2.0 =
* Feature - Add Apple Pay support for iOS users to quickly place orders from the product, cart, and checkout pages
* Feature - Allow the PayPal button to be customized from the plugin settings
* Feature - Add PayPal Credit support
* Feature - Add support for auto-capturing orders when changed to a paid status
* Feature - Customers can now label their saved payment methods for easier identification when choosing how to pay
* Tweak - Improve the My Account Payment Methods table on desktop and mobile
* Tweak - Automatically enable 3D Secure when enabled in the merchant account
* Tweak - Allow users to set the card types that should process 3D Secure
* Tweak - Allow users to set the 3D Secure level and block transactions where liability is not shifted
* Fix - Fix an issue where duplicate addresses were added when processing transactions with a previously saved payment method
* Fix - Ensure the payment forms are re-created after shipping method selection
* Misc - Remove support for WooCommerce 2.5

= 2018.10.17 - version 2.1.4 =
* Misc - Add support for WooCommerce 3.5

= 2018.08.01 - version 2.1.3 =
* Tweak - Generalize the PayPal link error to allow for different PayPal button colors
* Fix - Ensure PayPal charges can still be captured when the Credit Card gateway is disabled
* Fix - Prevent stalled checkout when PayPal is cancelled or closed
* Fix - Prevent duplicate PayPal buttons when checkout is refreshed
* Fix - Don't reset the "Create Account" form when the checkout is refreshed

= 2.1.2 =
* Tweak - Add payment details to the customer data export and remove it for erasure requests
* Tweak - Remove payment tokens for customer data erasure requests
* Misc - Add support for WooCommerce 3.4

= 2.1.1 =
* Fix - Fix the payment form JavaScript compatibility with IE 11

= 2.1.0 =
* Feature - Upgrade to the latest Braintree JavaScript SDK for improved customer experience, reliability, and error handling
* Tweak - Add placeholder text for credit card inputs
* Tweak - Add responsive sizing to the PayPal buttons and update to the recommended styling for the Cart and Checkout pages
* Tweak - Add setting and filter to disable PayPal on the cart page
* Tweak - Update all translatable strings to the same text domain
* Tweak - Hide Kount as a fraud tool option for US-based stores as it's not currently supported
* Tweak - Only load the Braintree scripts when required on payment pages
* Fix - Ensure that new customers have their billing address stored in the vault on their first transaction
* Fix - Prevent linked PayPal accounts from being cleared if there are address errors at checkout
* Fix - Fix some deprecated function notices

= 2.0.4 =
* Fix - Prevent a fatal error when completing pre-orders
* Fix - Prevent JavaScript errors when applying a 100%-off coupon at checkout

= 2.0.3 =
* Fix - Add a missing namespace that could cause JavaScript issues with some configurations

= 2.0.2 =
* Fix - Ensure refunds succeed for legacy orders that are missing the necessary meta data
* Fix - Add fallbacks for certain subscriptions upgrades after WooCommerce 3.0 compatibility issues
* Fix - Default set the Require CSC setting for legacy upgrades to avoid inaccurate error notices at checkout
* Fix - Prevent PayPal JavaScript errors in certain cases
* Fix - Ensure subscriptions are not affected if Change Payment fails due to declines or other problems
* Fix - Ensure old payment methods can be removed by the customer after changing subscription payment to a new method

= 2.0.1 =
* Fix - Purchasing a subscription with PayPal could lead to a blank order note being added
* Fix - Ensure all upgrade routines run for users who have used both the SkyVerge Braintree and PayPal Powered by Braintree v1 in the past
* Fix - Issue where existing subscriptions in some cases couldn't switch to using a new PayPal account
* Fix - Ensure "Place Order" button always remains visible for PayPal when accepting terms

= 2.0.0 =
* Feature - Now supports non-USA Braintree merchant accounts! Bonjour, hola, hallo, and g'day :)
* Feature - Supports WooCommerce Pre-Orders plugin
* Feature - Credit cards and PayPal gateways can be enabled individually
* Feature - Customers can opt to save cards or link a PayPal account at checkout for future use, or use saved methods during checkout
* Feature - Customers can manage or add new payment methods from the account area
* Feature - Uses an enhanced payment form with retina icons
* Feature - Add multiple merchant IDs to support multi-currency shops (requires a currency switcher)
* Feature - Supports Advanced Fraud tools and Kount Direct
* Feature - Supports 3D Secure for Visa / MasterCard transactions
* Feature - Add dynamic descriptors to be displayed for the transaction on customer's credit card statements
* Feature - Can show detailed decline messages at checkout to better inform customers of transaction decline reasons
* Feature - Allows bulk action to capture charges
* Feature - Orders with only virtual items can now force a charge instead of authorization
* Tweak - Capturing a charge now moves order status to "processing" automatically
* Tweak - Voided orders are now marked as "cancelled" instead of "refunded"
* Tweak - Admins can now manually update Subscription payment methods and view payment tokens
* Fix - Subscription orders will no longer force a charge and allow an authorization depending on settings
* Fix - Handle Subscriptions renewal failures by failing the order
* Fix - Customers can switch Subscriptions payment methods on their own from the account
* Fix - Stores sandbox and live customer tokens separately to avoid `Customer ID is invalid.` messages
* Fix - Ensures that payment can be made from the "My Account" page for pending orders
* Misc - Adds support for WooCommerce 3.0+
* Misc - Removes support for WooCommerce 2.4 and lower
* Misc - Added upgrade routine from SkyVerge Braintree plugin to allow for migrating existing tokens and subscriptions
* Misc - Refactor for improved performance and stability
* Misc - Other small fixes and improvements

= 1.2.7 =
* Fix - If you connected but did not save the settings, the enabled value would not be set and scripts would not enqueue
* Fix - Disable customer initiated payment method changes - PayPal Braintree does not support zero amount transactions
* Tweak - On new installs, debug messages are no longer sent to the WooCommerce System Status log by default

= 1.2.6 =
* Fix - Issue where buyer unable to change subscription payment method with free-trial (order total is 0).

= 1.2.5 =
* Fix - Prevent void on unsettled transaction when refunding partially.
* Tweak - Add filter wc_gateway_paypal_braintree_sale_args to filter arguments passed to sale call.

= 1.2.4 =
* Fix - Free subscription trails not allowed.
* Fix - Subscription recurring billing after free trial not working.

= 1.2.3 =
* Fix - Handle uncaught exceptions thrown by Braintree SDK. API calls from SDK may throws exception, thus it need to be handled properly in try/catch block.
* Fix - Issue where deactivating WooCommerce might throws an error

= 1.2.2 =
* Tweak - Updated FAQ that emphasizes this plugin only works in the U.S. currently
* Fix - Updated JS SDK to 2.24.1 which should fixes issue where credit card fields working intermittently
* Tweak - Add filter on credit card icons
* Tweak - Provide default title for cards and PayPal account methods

= 1.2.1 =
* Fix - Issue where Subscriptions with free trial was not processed
* Fix - Missing "Change Payment" button in "My Subscriptions" section
* Tweak - Make enabled option default to 'yes'
* Tweak - Add adnmin notice to setup / connect after plugin is activated
* Fix - Consider more statuses (settling, submitted_for_settlement, settlement_pending) to mark order as in-processing
* Fix - Issue where settings section rendered twice

= 1.2.0 =
* Replace array initialization code that causes a fatal error on PHP 5.2 or earlier. PHP 5.4+ is still required, but this code prevented the compatibility check from running and displaying the version requirements
* Update to the latest Braintree SDK (3.8.0)
* Add authorize/capture feature, allowing delayed settlement
* Pre-fill certain merchant and store details when connecting
* Fix missing gateway title and transaction URL when order in-hold

= 1.1.0 =
* Fixed a bug which would cause the gateway settings to report that the gateway was enabled when it actually was not fully enabled.
* Updated contributors list

= 1.0.1 =
* Remove duplicate SSL warnings
* Update environment check to also check after activation for environment problems
* Fix link in enabled-but-not-connected notice

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 2.1.0 =
* Feature - Upgrade to the latest Braintree JavaScript SDK for improved customer experience, reliability, and error handling

= 2.0.4 =
* Fix - Prevent a fatal error when completing pre-orders
* Fix - Prevent JavaScript errors when applying a 100%-off coupon at checkout

= 1.2.4 =
* Fix - Free subscription trials not allowed.
* Fix - Subscription recurring billing after free trial not working.
