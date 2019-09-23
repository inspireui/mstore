=== WOOCS - Currency Switcher for WooCommerce. Multi Currency and Multi Pay for WooCommerce ===
Contributors: RealMag777
Donate link: https://pluginus.net/affiliate/woocommerce-currency-switcher
Tags: woocommerce, currency, switcher, currency switcher, converter
Requires at least: 4.1.0
Tested up to: 5.2
Requires PHP: 5.4
Stable tag: 1.3.0

WooCommerce Currency Switcher is WooCommerce multi currency plugin that allows to switch products prices and get their rates converted in the real time!

== Description ==

**For WooCommerce plugin Currency Switcher** (WOOCS) is  multi currency plugin for woocommerce, that allows your site visitors switch products prices currencies according to set currencies rates in the real time and pay in the selected currency (optionally). Allows to add any currency for WooCommerce store. Ideal solution to make the serious WooCommerce store site in multiple currencies!

WOOCS is available as shortcode **[[woocs]](https://currency-switcher.com/shortcode/woocs/)** so as the widget.

Demo: [demo.currency-switcher.com](https://demo.currency-switcher.com/)

FAQ: [currency-switcher.com/category/faq](https://currency-switcher.com/category/faq/)

API: [currency-switcher.com/codex](https://currency-switcher.com/codex/)

Latest PHP 7.3.x – FULL COMPATIBILITY!


### Currency Switcher Features:

&#9989;&nbsp;**Representation:** Currency Switcher is available as a widget and works in any widgetized area, for flexibility the shortcode is also available [[woocs]](https://currency-switcher.com/shortcode/woocs/).  You can insert shortcode [woocs] in any place of your site, [even in the top menu](https://currency-switcher.com/how-to-drop-woocommerce-currency-switcher-in-menu/).

Also the plugin has ajaxed shortcode/widget of [currency converter](https://demo.currency-switcher.com/shortcode-woocs_converter/) and ajaxed shortcode/widget of [currency rates](https://demo.currency-switcher.com/shortcode-woocs_rates/)

&#9989;&nbsp;**Design**: graphically Currency Switcher can be represented in 3 different ways: drop-down, flags, [side switcher](https://demo.currency-switcher.com/). For each currency it is possible to set flag.

&#9989;&nbsp;**Checkout**: the customers are allowed to pay in their selected(preferred) currency. This feature has name 'Is multiple allowed' and should be enabled in the plugin settings.

&#9989;&nbsp;**Rates**: More than 7 currencies aggregators for automatic rates changing. Also admin can set rates manually if it's necessary! Optionally admin can be noticed about currency rates changes by email.

&#9989;&nbsp;**Rates auto update**: update currency rates hourly, twice daily, daily, weekly, monthly, each 5 minutes,  each 15 minutes,  each 30 minutes,  each 45 minutes. Or you can disable it and set your own currency rates by hands!

&#9989;&nbsp;**Price**: set price format which fit your needs - decimals count, usual money sign or custom sign, money sign position (4 variants). You can show or hide cents for each currency optionally. For each currency you can set its own count of decimals after comma, so BTC is not the problem for this plugin

&#9989;&nbsp;**Custom money signs**: create and use your own money symbols you need. It is possible even use currency which not exists in the reality!

&#9989;&nbsp;**Custom price formats**: each currency can has its own format where price and money sign can be set on the side you want

&#9989;&nbsp;**Statistic**: collect currencies switching statistic for business purposes. No any private data of customers collects, only currency, country and time of switching. Also statistic for orders currencies is there.

&#9989;&nbsp;**Currencies visibility**: possibility to set currencies as Public or as Private. In private mode currency not published in switchers and user cannot set it by link through '?currency=XXX'

&#9989;&nbsp;**Light video to understand basics**:

https://www.youtube.com/watch?v=wUoM9EHjnYs

Note: for today design of the plugin is different of the video (is improved), see screenshots below!

&#9989;&nbsp;**Payments rules**: rules to hide/show payment gates on the checkout page depending on the current currency

&#9989;&nbsp;**Welcome currency**: allows to set any price currency you want for your site visitors first visit. So if your shop currency is INR and you want let your customers on their first visit see prices converted to USD you just need to set 'Welcome currency' in WOOCS options.

&#9989;&nbsp;**Individual prices based on User Role**: gives ability to set different prices for each user role in each currency. Very power feature for your shop customers with loyalty program

&#9989;&nbsp;**Individual GeoIP rules for each product**:   super feature which allows for different countries show different prices in different currencies! This functionality allows to realize [WooCommerce Price Based on Country](https://currency-switcher.com/woocommerce-price-based-on-country/) functionality

&#9989;&nbsp;**Individual fixed prices rules for each product**: feature which allows for each product set fixed price for each currency, in this case for the product will not be applied conversion by rate, [watch video](https://currency-switcher.com/video-tutorials#video_PZugTH80-Eo)

&#9989;&nbsp;**GEO IP rules for visitors local currency detection**: this feature is more targeted and allows to set currency of prices for the site visitors in their native currency when they visited the shop for the first time. Currency changes automatically according to the visitor IP, and even more - if to enable option '**Checkout by GeoIP rules**' your customers always will buy products in their local currency!

&#9989;&nbsp;**Fixed minimum amount for FREE delivery for each currency**: allows to set minimum amount for FREE delivery as fixed value for each currency

&#9989;&nbsp;**Fixed minimum amount for shipping for each currency**: allows to set minimum amount for shipping as fixed value for each currency

&#9989;&nbsp;**Fixed amount for coupons for each currency**: you can set different amounts in coupons of your shop for different currencies. In another way the system will calculate amounts according to the currencies rates and relatively to the basic currency

&#9989;&nbsp;**Fixed minimum and maximum coupon verification amount for each currency**: for different currencies you can set different fixed verification amount instead of calculation by rates relatively to the basic currency

&#9989;&nbsp;**Show approximate price**: shows approximate price on the shop page and the single product page with currency of user defined by IP in the GeoIP rules tab if such rule exists. Works only with currencies rates data and NOT with fixed prices rules and geo rules. If system will define by GeoIP visitor country and visitor will switch currency - he/she always will see near the product price approximate price in his local currency.

&#9989;&nbsp;**Show approximate amount**: shows approximate amount on the checkout page and on the cart page with currency of user defined by IP in the GeoIP rules tab if such rule exists. Works only with currencies rates data and NOT with fixed prices rules and geo rules.

&#9989;&nbsp;**Compatibility with cache plugins**: if your site uses any cache plugins enable option 'I am using cache plugin on my site', reset the site cache and from now your shop visitors can switch currencies without any problems!

&#9989;&nbsp;**Orders keeps in currency of the deal**: each order in your shop keeps in currency the customer paid, if option 'Is multiple allowed' is enabled!

&#9989;&nbsp;**Price info icon**: show info icon near the price of the products which while its under mouse hover shows prices of products in all other currencies

&#9989;&nbsp;**Prices without cents**: recounts prices without cents for such currencies like JPY or TWD which by its nature have not cents. Test it for checkout after setup!

&#9989;&nbsp;Ability to set currency for new order which created through admin panel by hands

&#9989;&nbsp;Ability to recount order from any currency to the basic currency in multi currency mode of the plugin

&#9989;&nbsp;**Possible to change currency according to the language**: if you you using WPML or Polylang plugins in your shop and by business logic you want to set currency according to the current language [it is possible with WOOCS API](https://currency-switcher.com/switch-currency-with-language-change/)

&#9989;&nbsp;WOOCS understand currency in the site link as [demo.currency-switcher.com/?currency=EUR](http://demo.currency-switcher.com/?currency=EUR)

&#9989;&nbsp;**No GET data in the link**: switches currency without GET properties (?currency=USD) in the link (optionally)

&#9989;&nbsp;**Wide API**: advanced [API functionality set](https://currency-switcher.com/codex/) which allows to manipulate with prices and their rates on the fly using conditional logic

&#9989;&nbsp;**Easy to use for administrators and shop customers**: install, set settings for couple of minutes and let your shop make more money!

&#9989;&nbsp;Compatible with [WPML](https://wpml.org/plugin/woocommerce-currency-switcher/)

&#9989;&nbsp;Compatible with [WooCommerce Products Filter](https://products-filter.com/)

&#9989;&nbsp;95% compatibility with different payment gates in multi currency mode, just try it!

&#9989;&nbsp;We do [compatibility](https://currency-switcher.com/codex/#compatibility) with our special program [WOOCS LABS](https://currency-switcher.com/woocs-labs)

&#9989;&nbsp;**Strong technical support which each day works with tones of code!**


### PREMIUM FEATURES
* All features above
* Unlimited count of currencies (in the free version 2 currencies available)


**Get Premium version of the plugin**: [on Codecanyon](https://pluginus.net/affiliate/woocommerce-currency-switcher)


### Make your site more profitable with next powerful scripts:

&#9989;&nbsp;[WOOF - Products Filter for WooCommerce](https://wordpress.org/plugins/woocommerce-products-filter/): is an extendable, flexible and robust plugin for WooCommerce that allows your site customers filter products by products categories, attributes, tags, custom taxonomies and price. Supports latest version of the WooCommerce plugin. A must have plugin for your WooCommerce powered online store! Maximum flexibility!

&#9989;&nbsp;[WOOBE - Bulk Editor for WooCommerce](https://wordpress.org/plugins/woo-bulk-editor/): WordPress plugin for managing and bulk edit WooCommerce Products data in robust and flexible way! Be professionals with managing data of your woocommerce e-shop!

&#9989;&nbsp;[WPCS - WordPress Currency Switcher](https://wordpress.org/plugins/currency-switcher/): is a WordPress plugin that allows to switch currencies and get their rates converted in the real time on your site!

&#9989;&nbsp;[MDTF - WordPress Meta Data Filter & Taxonomies Filter](https://wp-filter.com/): the plugin for filtering and searching WordPress content in posts and their custom types by taxonomies and meta data fields. The plugin has very high flexibility thanks to its rich filter elements and in-built meta fields constructor!




== Installation ==
* Download to your plugin directory or simply install via Wordpress admin interface.
* Activate.
* Use.


== Frequently Asked Questions ==

Q: Where can I see demo?
R: [http://demo.currency-switcher.com/](http://demo.currency-switcher.com/)

Q: API?
R: [https://currency-switcher.com/codex/](https://currency-switcher.com/codex/)

Q: Documentation?
R: [https://currency-switcher.com/documentation/](https://currency-switcher.com/documentation/)

Q: Videos?
R: [https://currency-switcher.com/video-tutorials/](https://currency-switcher.com/video-tutorials/)

Q: More FAQ answers?
R: [https://currency-switcher.com/category/faq/](https://currency-switcher.com/category/faq/)


== Screenshots ==
1. Currencies options
2. Tab Options top
3. Tab Options bottom
4. Tab Advanced options
5. Tab Side switcher options (woocommerce > v.3.3.1)
6. Tab Payment rules
7. Tab GeoLocation IP rules
8. Tab Statistic
9. Price based on user roles (woocommerce > v.3.3.1)
10. Fixed minimum amount for shipping for each currency (woocommerce > v.3.3.1)
11. Fixed prices for FREE delivery for each currency (woocommerce > v.3.3.1)
12. Fixed amount for coupons for each currency (woocommerce > v.3.3.1)
13. Fixed minimum and maximum coupon verification amount for each currency (woocommerce > v.3.3.1)

== Changelog ==

= 1.3.0 =
* some little fixes
* new currency agregator added: bnr.ro
* new currency agregator added: currencylayer
* new currency agregator added: open exchange rate

= 1.2.9.1 =
* 1 hot fix for hook raw_woocommerce_price

= 1.2.9 =
* [https://currency-switcher.com/update-v-2-2-9-v-1-2-9/](https://currency-switcher.com/update-v-2-2-9-v-1-2-9/)

= 1.2.8.2 =
* 1 fix for hook woocommerce_add_to_cart_hash

= 1.2.8.1 =
* compatibility with woocommerce 3.6.1
* some little fixes as an example https://wordpress.org/support/topic/option-woocommerce_currency-is-not-updated-after-changes/

= 1.2.8 =
* some small bugs fixed
* code improving
* google aggregator removed at all as it stopped to work normally
* added 3 new agregators: Fixer, MicroPyramid, The Free Currency Converter by European Central Bank
* added new field in settings which allows insert subscribed API key for Fixer and The Free Currency Converter

= 1.2.7.1 =
* fixes for WOOCS functionality for fixed amounts

= 1.2.7 =
* [https://currency-switcher.com/update-v-2-2-7-v-1-2-7/](https://currency-switcher.com/update-v-2-2-7-v-1-2-7/)

= 1.2.6 =
* removed currency agregators which stopped to work
* added back Google and Yahoo currency agregators
* new feature: No GET data in link - Switches currency without GET properties (?currency=USD) in the link

= 1.2.5 =
* minor fixes, removed some notices

= 1.2.4 =
* [https://currency-switcher.com/update-v-2-2-4-v-1-2-4/](https://currency-switcher.com/update-v-2-2-4-v-1-2-4/)

= 1.2.3 =
* adaptation for woocommerce 3.3.1 - better update WOOCS from v.1.2.2 to v.1.2.3

= 1.2.2 =
* [https://currency-switcher.com/update-v-2-2-2-v-1-2-2/](https://currency-switcher.com/update-v-2-2-2-v-1-2-2/)
* previous v.1.2.1 is here: [v.1.2.1](https://currency-switcher.com/wp-content/uploads/2018/01/woocommerce-currency-switcher-121.zip)

= 1.2.1 =
* [https://currency-switcher.com/update-v-2-2-1v-1-2-1/](https://currency-switcher.com/update-v-2-2-1v-1-2-1/)
* previous v.1.2.0 is here: [v.1.2.0](https://currency-switcher.com/wp-content/uploads/2017/11/woocommerce-currency-switcher-1.2.0.zip)

= 1.2.0 =
* [https://currency-switcher.com/update-v-2-2-0-v-1-2-0/](https://currency-switcher.com/update-v-2-2-0-v-1-2-0/)
* previous v.1.1.9 is here: [v.1.1.9](https://currency-switcher.com/wp-content/uploads/2017/09/woocommerce-currency-switcher-1.1.9.zip)

= 1.1.9 =
* Heap of small bugs fixed
* A lot of code was remade to make WooCommerce 3.0.0 and WOOCS compatible
* previous v.1.1.8 is here: [v.1.1.8](https://currency-switcher.com/wp-content/uploads/2017/04/woocommerce-currency-switcher-1.1.8.zip)

= 1.1.8 =
* [https://currency-switcher.com/update-v-2-1-8-and-v-1-1-8/](https://currency-switcher.com/update-v-2-1-8-and-v-1-1-8/)
* previous v.1.1.7 is here: https://currency-switcher.com/wp-content/uploads/2016/12/woocommerce-currency-switcher-117.zip

= 1.1.7 =
* Heap of small bugs fixed
* https://wordpress.org/support/topic/multi-currency-on-invoices?replies=8 - resolved
* new option in the currencies settings: Decimals
* new hook: woocs_drop_down_view
* advanced API doc: https://currency-switcher.com/codex/

= 1.1.6 =
* Heap of small bugs fixed
* New hook woocs_price_html_tail
* Approx. value on cart and chekout page in the currency of customer (in multiple mode only+geoip enabled)
* New hook woocs_get_approximate_amount_text
* Previous version of the plugin is here: https://currency-switcher.com/wp-content/uploads/2016/05/woocommerce-currency-switcher-1154.zip

= 1.1.5.4 =
* Improvements for security functionality
* GeoIp hot fix

= 1.1.5.3 =
* Closed XSS vulnerability. Thanks to Ben Khlifa Fahmi ; Founder & CEO of BenkhlifaExploit Founder & Pentester at Tunisian Whitehats Security

= 1.1.5.1 =
* 1 fix for woocommerce 2.5.1 variable products price

= 1.1.5 =
* New option: I am using cache plugin for my site - alloes using the plugin with cached sites
* New button in order to convert oder data to basic currency amounts
* New shortcode: [woocs_show_current_currency text="" currency="" flag=1 code=1]
* New shortcode: [woocs_show_custom_price value=20] -> price in selected currency for txt-adv-banners
* New option: Prices without cents
* New option: Hide switcher on checkout page
* Hint: wp-admin/admin.php?page=wc-settings&tab=woocs&woocs_reset=1 - reset currency options - be care
* Improved: cron periods added - weekly, monthly
* New filter: add_filter('woocs_price_format', 'my_woocs_price_format', 999, 2); - Any manipulation with price format, look it in the docs https://currency-switcher.com/documentation/#!/section_8
* previous v.1.1.4 is here: https://currency-switcher.com/wp-content/uploads/2016/01/woocommerce-currency-switcher-114.zip

= 1.1.4 =
* WordPress 4.3 small adaptation - using __construct in the widget, prev widget-API was deprecated
* GEO IP functionality is free from now
* price popup on the front near each price optionally
* in body implemented currency css class. Example: currency-eur
* added currency agregator for Russian Centrobank - asked by customers from Russia

= 1.1.3 =
* compatibility for woocommerce 2.4
* added storage optionally, transient for sites which can work with session normally because of server options
* added new drop-down wselect -> https://github.com/websanova/wSelect#wselectjs
* in shortcode [woocs] and currency switcher widget added new option txt_type which allows show currency description in drop-down instead of its code
* previous v.1.1.2 is here: https://currency-switcher.com/wp-content/uploads/2015/08/woocommerce-currency-switcher-112.zip

= 1.1.2 =
* some small bugs fixed
* dark chosen implemented

= 1.1.1 =
* some small bugs fixed
* done a lot to make compatibility higher

= 1.1.0 =
* 1 bad logic bug fixed, which broke recount prices in multiple mode
* AJAX refresh of mini cart fixed, now its ok

= 1.0.9 =
* Compatibility for 90% payment gates without any customizations
* 2 new widgets+shortcodes: currency converter, currency rates
* Attention for codecanyon customers - do not update to this version - it is the free one and have less functionality!!! Download your copy of the plugin you bought from codecanyon site only!
* If you are allows to your customers pay in their selected currency be attentive  to update to 1.0.9 and higher, you will get
little inconvenience in wp-admin/edit.php?post_type=shop_order with displayed orders amount + order amounts inside!
You can close orders using old version of the plugin and then update to the v.1.0.9 or greater. If you are happy with 1.0.4 version of the plugin - continue use it.
If you uses basic currency for payments - update the plugin with no doubt.
WOOCS 1.0.4 is here - https://currency-switcher.com/wp-content/uploads/2015/07/woocommerce-currency-switcher-104.zip

= 1.0.4 =
* Validation error: PayPal amounts do not match fixed
* WooCommerce native range slider - js price format improvement

= 1.0.3 =
* Currency can be changed automatically according to visitor’s IP using woo WC_Geolocation class
* Possibility set currency on front by flags images mode

= 1.0.2 =
Some features and bug fixes

= 1.0.1 =
Some features and bug fixes

= 1.0.0 =
Plugin release. Operate all the basic functions.



== License ==

This plugin is copyright pluginus.net &copy; 2012-2019 with [GNU General Public License][] by realmag777.

This program is free software; you can redistribute it and/or modify it under the terms of the [GNU General Public License][] as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY. See the GNU General Public License for more details.

[GNU General Public License]: http://www.gnu.org/copyleft/gpl.html



== Upgrade Notice ==
[Look here for ADVANCED version of the plugin](https://pluginus.net/affiliate/woocommerce-currency-switcher)

