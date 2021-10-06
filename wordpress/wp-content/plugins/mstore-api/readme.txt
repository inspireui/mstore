=== MStore API ===
Contributors:      InspireUI Ltd
Tags:              mstore, fluxstore, react-native, flutter, inspireui, ios, android
Requires at least: 4.4
Tested up to:      5.8.1
Stable tag:        3.4.5
License:           GPL-2.0
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

The plugin is used for config the Mstore/FluxStore mobile and support RestAPI to connect to the app.

== Description ==

The plugin is used for config the Mstore/FluxStore mobile and support RestAPI to connect to the app.

[youtube https://youtu.be/sYnHhnS5WnQ]

Fluxstore is a universal e-commerce app inspired by Flutter framework, made by Google. With the mission of reducing thousands of hours of business spent on designing, developing and testing a mobile app, Fluxstore comes as a complete solution for optimizing to deliver your app to the market with high productivity and cost efficiency. It could be able to satisfy all of the business requirements including e-commerce functionalities, impressive UX design and smooth performance on both iOS and Android devices.

If your business has already had the website that built based on WooCommerce, Magento or Opencart, then it is easy to integrate with Fluxstore by just a few steps, and quickly release the final app to both Appstore and Google Play store. The download package is included the full source code and many related resources (designs, documents, videos…) that help you install in the smoothest way.

Either you are business people with raising sale ambition or developers with faster mobile application creation need, Fluxstore provides you solutions.
Faster- Smoother- Closer. 

### Reference links
- Company Website: [https://inspireui.com](https://inspireui.com)
- App demo: [iOS](https://apps.apple.com/us/app/mstore-flutter/id1469772800), [Android](https://play.google.com/store/apps/details?id=com.inspireui.fluxstore)
- Youtube Channel: [https://www.youtube.com/inspireui](https://www.youtube.com/inspireui)
- Document: [https://docs.inspireui.com](https://docs.inspireui.com)
- MStore website: [https://mstore.io](https://mstore.io)
- Fluxstore website: [https://fluxstore.app](https://fluxstore.app)

== Installation ==

= Manual Installation =

1. Upload the entire `/mstore-api` directory to the `/wp-content/plugins/` directory.
1. Activate 'MStore API' through the 'Plugins' menu in WordPress.

= Better Installation =

1. Go to Plugins > Add New in your WordPress admin and search for 'MStore API'.
1. Click Install.

== Changelog ==
= 3.4.5 =
  * Fix security issue when upload config file
  * Fix Authenticated Arbitrary File Deletion Vulnerability
  * Update to compatible with the latest WordPress 5.8
  
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
