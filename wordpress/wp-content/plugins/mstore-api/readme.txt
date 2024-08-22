=== MStore API - Create Native Android & iOS Apps On The Cloud ===
Contributors:      inspireui
Tags:              flutter, app builder, app creator, mobile app builder, woocommerce app
Requires at least: 4.4
Tested up to:      6.5.3
Stable tag:        4.15.3
License:           GPL-2.0
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Take your WordPress store mobile with MStore API!
This plugin bridges the gap between your WordPress website and the powerful FluxBuilder app builder. 

== Description ==
Take your WordPress store mobile with MStore API!

This plugin bridges the gap between your WordPress website and the powerful FluxBuilder app builder. By enabling the REST API, MStore API seamlessly connects your store data (products, users, orders) to [FluxBuilder App](https://www.fluxbuilder.com), allowing you to create a custom mobile app for your business  without writing any code.

[youtube https://youtu.be/xJ9EQmSw1XU?si=ujuFzKCeYQ5eA0xQ]

### Key benefits:

- **Effortless mobile app creation**: Leverage FluxBuilder's drag-and-drop interface and pre-built templates to design your dream mobile app.
- **Seamless data integration**: MStore API ensures smooth communication between your WordPress store and the mobile app, keeping product information, user accounts, and orders in sync.
- **Enhanced customer experience**: Offer a convenient mobile shopping experience to your customers, boosting engagement and sales.

Ready to go mobile? Download the MStore API plugin and unlock the power of FluxBuilder for your WordPress store!

### Reference links
- FluxBuilder - Flutter App Builder: [https://www.fluxbuilder.com](https://www.fluxbuilder.com)
- Guide to use: [docs.fluxbuilder.com](https://docs.fluxbuilder.com)
- Download The App Builder: [fluxbuilder.com/download](https://www.fluxbuilder.com/download)
- Showcase: [https://showcase.fluxbuilder.com](https://showcase.fluxbuilder.com)
- [Youtube](https://www.youtube.com/inspireui?sub_confirmation=1) 
- [Facebook](https://www.facebook.com/groups/1401824449973438) 
- [Document](https://docs.fluxbuilder.com) 


== Installation ==

= Manual Installation =

1. Upload the entire `/mstore-api` directory to the `/wp-content/plugins/` directory.
1. Activate 'MStore API' through the 'Plugins' menu in WordPress.

= Better Installation =

1. Go to Plugins > Add New in your WordPress admin and search for 'MStore API'.
1. Click Install.

== Changelog ==
= 4.15.3 =
  * Fix sms login checking

= 4.15.2 =
  * Fix waring issues

= 4.15.1 =
  * Support Tera Wallet Referrals
  
= 4.15.0 =
  * Remove api/flutter_user/firebase_sms_login and api/flutter_user/firebase_sms_login_v2 endpoint. They're replaced by api/flutter_user/firebase_sms and api/flutter_user/firebase_sms_login_v2
  * Note: this version works on FluxStore v4.1.1 or later. If you're using the old FluxStore version, please doesn't upgrade MStore Api plugin v4.15.0 or later. 

= 4.14.7 =
  * Fix firebase sms security

= 4.14.6 =
  * Fix crash products api

= 4.14.5 =
  * Support YITH WooCommerce Badge Management Premium plugin

= 4.14.4 =
  * Push notification to admin when has new order

= 4.14.3 =
  * Fix update product in vendor admin

= 4.14.2 =
  * Fix syntax error

= 4.14.1 =
  * Support to push notification to delivery boy when order is assigned

= 4.14.0 =
  * Upgrade Filter function
  * Add size guide feature

= 4.13.7 =
  * Update plugin description 

= 4.13.6 =
  * Fix to avatar that is uploaded on the app to website

= 4.13.5 =
  * Fix to check review module on WCFM

= 4.13.4 =
  * Update push notification via Firebase

= 4.13.3 =
  * Fix shipping on webview checkout

= 4.13.2 =
  * Fix to save order to vendor dashboard for WCFM

= 4.13.1 =
  * Fix duplicate review

= 4.13.0 =
  * Fix to get products to rate

= 4.12.9 =
  * Fix list nearby vendors dokan api

= 4.12.8 =
  * Support to get listings by featured

= 4.12.7 =
  * Get purchased products api

= 4.12.6 =
  * Update error code for digits api

= 4.12.5 =
  * Fix TeraWallet Withdrawal api

= 4.12.4 =
  * Support TeraWallet Withdrawal api

= 4.12.3 =
  * Fix submit review mylisting

= 4.12.2 =
  * Add submit review api for listeo

= 4.12.1 =
  * Support min max prices api

= 4.12.0 =
  * Fix auth on webview

= 4.11.9 =
  * Add custom information table api for B2BKing

= 4.11.8 =
  * Update vendor dashboard api

= 4.11.7 =
  * Support B2BKing

= 4.11.6 =
  * Update description

= 4.11.5 =
  * Fix appointment checkout issues

= 4.11.4 =
  * Fix crash variation

= 4.11.3 =
  * Support tag/category for Product Composite

= 4.11.2 =
  * Fix home cache

= 4.11.1 =
  * Support Composite Product api

= 4.11.0 =
  * Support Store Locator api

= 4.10.10 =
  * Fix digits login with password

= 4.10.9 =
  * Fix sql injection

= 4.10.8 =
  * Fix security apple login
  * Fix get drivers api
  * Fix create product api

= 4.10.7 =
  * Update promptpay order detail

= 4.10.6 =
  * Fix to scan product

= 4.10.5 =
  * Fix to show product price with tax settings

= 4.10.4 =
  * Support Flow Flow api
  
= 4.10.3 =
  * Fix register account for wholesale

= 4.10.2 =
  * Fix security issues when upload file
  * Fix Product Gift Cards

= 4.10.1 =
  * Add whosale prices for product

= 4.10.0 =
  * Remove  verify purchase code

= 4.0.9 =
  * Fix to register vendor account on Manager app

= 4.0.8 =
  * Update  Apple Login

= 4.0.7 =
  * Support 2c2p payment gateway

= 4.0.6 =
  * Fix load vendor orders

= 4.0.5 =
  * Fix ExpressPay api

= 4.0.4 =
  * Support ExpressPay api
  
= 4.0.3 =
  * Fix search delivery boy api

= 4.0.2 =
  * Fix security

= 4.0.1 =
  * Add api to get dokan orders for listeo

= 3.9.9 =
  * Fix security issues

= 3.9.8 =
  * Fix security issues and support Thawani api

= 3.9.7 =
  * Fix security issues

= 3.9.6 =
  * Fix security issues

= 3.9.5 =
  * Fix load categories for vendor

= 3.9.4 =
  * Fix login api for listeo theme

= 3.9.3 =
  * Fix security issue for listing api

= 3.9.2 =
  * Fix security issue for cart api

= 3.9.1 =
  * Fix security issue for coupon api

= 3.9.0 =
  * Fix to push notification to seller when order created

= 3.8.9 =
  * Add Stripe api

= 3.8.8 =
  * Fix shop orders api
  
= 3.8.7 =
  * Support Wholesale api

= 3.8.6 =
  * Fix nearby for mylisting

= 3.8.5 =
  * Get video products api
  * Support Midtrans payment

= 3.8.4 =
  * Show bank info after order completed

= 3.8.3 =
  * Fix product variant issues

= 3.8.2 =
  * Update apple login api to save first name and last name

= 3.8.1 =
  * Fix mylisting api to show location, lat, long

= 3.8.0 =
  * Support to send sms via Digits

= 3.7.9 =
  * Add delete order api

= 3.7.8 =
  * Fix upload image

= 3.7.7 =
  * Fix validate zh config json

= 3.7.6 =
  * Add product rating counts api

= 3.7.5 =
  * Fix send email when register

= 3.7.4 =
  * Whitelist demo account

= 3.7.3 =
  * Fix security issue when upload config json file

= 3.7.2 =
  * Fix api to get products by vendor

= 3.7.1 =
  * Update Active Api

= 3.7.0 =
  * Fix some issues for Fluxstore Manager

= 3.6.9 =
  * Fix to upload image for variant product

= 3.6.8 =
  * Support api to add order note after paying by MyFatoorah

= 3.6.7 =
  * Support api to add order note after paying by Tap

= 3.6.6 =
  * Fix crash dynamic link for category

= 3.6.5 =
  * Fix limit products in home cache api

= 3.6.4 =
  * Support Flutterwave payment method

= 3.6.3 =
  * Support PayStack payment method

= 3.6.2 =
  * Fix shipping methods to response required_shipping value

= 3.6.1 =
  * Fix to show payment methods based on the country

= 3.6.0 =
  * Fix to show payment methods based on the country

= 3.5.9 =
  * Fix add-on checkout issue

= 3.5.8 =
  * Fix checkout issue

= 3.5.7 =
  * Fix checkout issue

= 3.5.6 =
  * Fix add-on checkout issue

= 3.5.5 =
  * Remove required card for free level for paid membership pro

= 3.5.4 =
  * Fix wordpress 6

= 3.5.3 =
  * Sending email after creating order
  * Delete user api

= 3.5.2 =
  * Support Listing Api

= 3.5.1 =
  * Support Digits SMS login

= 3.5.0 =
  * Fix load attributes for product variant

= 3.4.9 =
  * Improve home cache performance

= 3.4.8 =
  * Support Paid Membership Pro

= 3.4.7 =
  * Support PayTM payment

= 3.4.6 =
  * Fix the warning issues on PHP 8.x
  * Update some Text sanitize.

= 3.4.5 =
  * Fix security issue when upload config file
  * Fix Authenticated Arbitrary File Deletion Vulnerability
  * Update to compatible with the latest WordPress 5.8
  * Rename files to upgrade Naming Convention.
  * Release the MStore API Postman Collection - https://www.getpostman.com/collections/8f9c088b0e5b82b90400
  
= 3.4.4 =
  * Support Tera Wallet

= 3.4.3 =
  * Add APIs for WooCommerce Delivery App
  * Fix WCFM Vendor Categories

= 3.4.2 =
  * Update Delivery Dates function
  * Fix multi-languages in single vendor
  * Add dynamic link APIs

= 3.4.1 =
  * Add new apis for Membership Ultimate Pro

= 3.4.0 =
  * Add new apis for FluxStore Delivery
  * Add new apis for FluxStore Manager

= 3.3.9 =
  * Add api for FluxStore Delivery

= 3.3.8 =
  * Add api to get dynamic link
  * Fix minor FluxStore Manager bugs.

= 3.3.7 =
  * Add api to send push notification for chat
  * Update create order for guest user

= 3.3.6 =
  * Fix adding attributes in FluxStore Manager
  * Add "update store profile" in FluxStore Manager - WCFM
  * Fix minor bugs in FluxStore Manager

= 3.3.5 =
  * Update api for WooCommerce Points and Rewards

= 3.3.4 =
  * Update user profile
  * Update store settings (FluxStore Manager - WCFM)
  * Implement New Order Notification Alert (FluxStore Manager)

= 3.3.3 =
  * Fix update user profile

= 3.3.2 =
  * Fix order bugs FluxStore Manager.
  * Add get reviews api for Dokan in FluxStore Manager.

= 3.3.1 =
  * Fix minor bugs FluxStore Manager.
  * Improve update user profile.

= 3.3.0 =
  * Update APIs for FluxStore Manager.

= 3.2.9 =
  * Update to send push notification for new order to vendor in WCFM

= 3.2.8 =
  * Fix home cache api

= 3.2.7 =
  * Fix to check booking product type

= 3.2.6 =
  * Fix waring in PHP8

= 3.2.5 =
  * Fix to get shipping methods and payment methods for booking product

= 3.2.4 =
  * Fix to post booking info to webview to checkout

= 3.2.3 =
  * Fix undefined billing and shipping when login

= 3.2.2 =
  * Fix to get products by store for WCFM

= 3.2.1 =
  * Show confirm alert when click Deactive button
  * Update home cache api to get sale products for saleOff layout
  * Support add/edit product attribute in FluxStore Manager app.
  * Support switching product statuses and types(simple/variable) in FluxStore Manager app.

= 3.2.0 =
  * Remove apple login by GET method

= 3.1.9 =
  * Update apple login to post token instead of email

= 3.1.8 =
  * Fix import error

= 3.1.7 =
  * Fix crash MStore Api plugin when install for FluxNews
  * Update deactive license

= 3.1.6 =
  * Add deactive button

= 3.1.5 =
  * Add apis to user WooCommerce keys with readonly permission

= 3.1.4 =
  * Sort shop orders desc for WCFM
  * Fix duplicate orders issue in FluxStore Manager

= 3.1.3 =
  * Support get stores list based on current location for WCFM and Dokan

= 3.1.2 =
  * Support wc/v3/flutter version 3 for vendor api to use in MStore Dokan.

= 3.1.1 =
  * Fix crash when get products

= 3.1.0 =
  * Allow to edit push notificaion message when order status changed

= 3.0.9 =
  * Fix banner image in stores api

= 3.0.8 =
  * Support Product Add On

= 3.0.7 =
  * Fix crash order api

= 3.0.6 =
  * Remove draft products in home cache api
  
= 3.0.5 =
  * Add endpoints for FluxStore admin (compatible with WCFM plugin)
  * Add endpoint for finding nearby stores based on user location (compatible with WCFM plugin)
  * Fix get stores api

= 3.0.4 =
  * Add reset password api

= 3.0.3 =
  * Add product data to line_items in orders list api

= 3.0.2 =
  * Remove meta_data product for home cache api

= 3.0.1 =
  * Send notification to vendor as new orders

= 3.0.0 =
  * Send notification to user when order status changed

= 2.9.9 =
  * Support widgets cache
  * Decode cookie in GET request

= 2.9.8 =
  * Support open vendor admin in Fluxstore
  * Fix shipping address form doesn't show in checkout page

= 2.9.7 =
  * Fix undefined constant __return_true in old wordpress version

= 2.9.6 =
  * Support Points and Rewards for WooCommerce
  * Fix showing order note for one page checkout

= 2.9.5 =
  * Fix apple login issue
  * Fix one page checkout with product variantion
  * Sync cart from mobile to website
  * Get taxes api
  * Fix search store for WCFM
  * Allow to setting limit per page

= 2.9.4 =
  * Support Fluxstore version 1.7.6

= 2.0.0 =
  * Major update to remove the depend on JSON API plugins
  * Add category caching API
  * Fix security issues

= 1.4.0 =
  * Update Caching

= 1.3.0 =
  * Add firebase phone auth

= 1.2.0 =
  * Support FluxStore
  * Update SMS Firebase Login

= 1.0.0 =
  * First Release
  * Support Mstore App
