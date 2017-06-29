=== JSON API User ===

Donate link:

Tags: json api, RESTful user registration, authentication, RESTful Facebook Login, RESTful User Meta and BuddyPress xProfile

Contributors: parorrey

Stable tag: 2.5

Requires at least: 3.0.1

Tested up to: 4.6.1

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extends the JSON API Plugin to allow RESTful user registration, authentication and many other User Meta, BuddyPress functions. A Pro version is also available.


==Description==


JSON API User extends the JSON API Plugin with a new Controller to allow RESTful user registration, authentication, password reset, RESTful Facebook Login, RESTful User Meta and BuddyPress xProfile get and update methods. This plugin is for WordPress/Mobile app developers who want to use WordPress as mobile app data backend. 


Features include:

* Generate Auth Cookie for user authentication
* Validate Auth Cookie
* RESTful User Registration
* RESTful Facebook Login/Registration with valid access_token
* RESTful BuddyPress xProfile fields update
* Get User Meta and xProfile fields
* Update User Meta and xProfile fields
* Delete User Meta
* Password Reset
* Get Avatar
* Get User Info
* Post Comment

The plugin was created for mobile apps integration with the web app using WordPress as backend for all the data. WordPress helped in putting together the web app quickly and then Mobile iOS and Android apps were integrated via this plugin. There were some app specific customized methods which are not included but rest have been made generic for community usage. 

My other JSON API Auth plugin has also been integrated with this from version 1.1. since most methods required user authentication via cookie for data update. You can also write your own methods by copying the existing methods if you need any user related features.

Hope this will help some.

Pro Version - JSON API User Plus

A pro version of this plugin, JSON API User Plus, is available here http://www.parorrey.com/solutions/json-api-user-plus/ that supports BuddyPress Messages component, BuddyPress avatar upload and other BuddyPress related functions to integrate BuddyPress features in your mobile app via REST.

'JSON API User Plus' includes API key which protects and restricts the endpoint calls. This key can be updated from Settings > User Plus options page. Your app must include this key with every call to get the data from REST API. Please see documentation for calling endpoints examples for 'JSON API User Plus'.

JSON API User Plus features include:

* Generate Auth Cookie for user authentication
* Validate Auth Cookie
* RESTful User Registration
* RESTful Facebook Login/Registration with valid access_token
* RESTful BuddyPress xProfile fields update
* Get User Meta and xProfile fields
* Update User Meta and xProfile fields
* Delete User Meta
* Password Reset
* Get/Upload Avatar
* Get User Info
* Post Comment
* Add Post, Update Post, Delete Post
* Add/Edit/Delete Custom Post Type, Custom Fields
* Search User
* BuddyPress Activities
* BuddyPress Members
* BuddyPress Friends
* BuddyPress Notifications
* BuddyPress Settings
* & many more


==Installation==

First you have to install the JSON API for WordPress Plugin (http://wordpress.org/extend/plugins/json-api/installation/).

To install JSON API User just follow these steps:

* Upload the folder "json-api-user" to your WordPress plugin folder (/wp-content/plugins)
* Activate the plugin through the 'Plugins' menu in WordPress or by using the link provided by the plugin installer
* Activate the controller through the JSON API menu found in the WordPress admin center (Settings -> JSON API)

==Screenshots==


==Changelog==

= 2.5 =
* Updated 'register' endpoint to follow WordPress Settings to enable/disable user registration.

= 2.4 =
* Changing error message for 'generate_auth_cookie' endpoint.

= 2.3 =
* Updated 'generate_auth_cookie' endpoint to make it secure for https protocol and support POST method for sending username and password, you can still bypass SSL requirement by passing insecure=cool.
* Updated 'generate_auth_cookie' to allow cookie generation using both user_name or email account with password.
* Added documentation 'register' endpoint for registering with user provided password.

= 2.2 =
* Updated 'retrieve_password' endpoint to fix the bug finally, no more invalid key error.

= 2.1 =
* Updated 'retrieve_password' endpoint to fix the bug. WordPress 4.3.1 made it stopped working.
* Updated Documentation.

= 2.0 =
* Updated 'register' endpoint. WordPress 4.3.1 changed wp_new_user_notification function and it stopped email notification for new user. Custom password is not available anymore. Only system generated password is available via email.

= 1.9.1 =
* removed debugging code.

= 1.9 =

* Added `update_user_meta_vars` to allow updating more than 1 meta_key variable at a time to cut down on multiple http requests.

= 1.8 =

* Updated `register` to allow setting up cookie for any required duration. A cookie is sent in response on registration. Just provide the `seconds` var with others to get required cookie. Default time is 14 days.

= 1.7 =

* added 'info' endpoint for plugin version.
* added default user role for user register endpoint.

= 1.6 =

* generate_auth_cookie does not require nonce any more to generate cookie.
* generate_auth_cookie now also returns 'cookie_name'.

= 1.5.1 =

* Fixed documentation error for generate_auth_cookie.

= 1.5 =

* Added the function to authenticate, allow the user (with edit rights) to use JSON API core controllers as well. Thanks `necro_txilok` for the suggestion.
* Updated `generate_auth_cookie` to allow setting up auth cookie for any required duration. Just provide the `seconds` var with `nonce`, `username` and `password` to get required cookie. Default time is 14 days.
* Updated `register` to allow disabling notification email to user after registration. Provide `notify` var with value 'no' while registering and it won't send email. You must provide `user_pass` var (which is optional otherwise) to use this because password is sent in the email. Without `user_pass` var, this won't make sense disablig notification so it won't. 
* Fixed typos in documentation and aded new documentation. 

= 1.4 =

* Updated update_user_meta method to allow multiple values for any meta_key in array format as well as single value. Earlier, it allowed only single value per meta_key. 
* Updated get_user_meta method to remove blank value fields and also removed the first index of array for each value. Earlier, it showed all fields and every returned value was at the first index of array.
* A pro version of this plugin, JSON API User Plus, is available now here http://www.parorrey.com/solutions/json-api-user-plus/. Apart from additional features of BuddyPress Messages component, Pro version includes API key which protects and restricts the endpoint calls. This key can be updated from Settings > User Plus options page. Your app must include this key with every call to get the data from REST API.  

= 1.3 =

* Updated get_user_meta method. It requires 'cookie' var now to protect it. 'meta_key' var is optional.
* Updated get_userinfo method. Removed email, user_login values from response 
* Added post_comment method. It needs 'cookie', 'post_id', 'content' and 'comment_status' vars.

= 1.2.2 =

* removed extra fields from xprofile end point for 'default' value

= 1.2.1 =

* removed debugging code from generate_auth_cookie 

= 1.2 =

* Updated register method to allow all available fields with user registration. These include 'user_login', 'user_email', 'user_pass', 'display_name', 'user_nicename', 'user_url', 'nickname', 'first_name', 'last_name', 'description', 'rich_editing', 'user_registered', 'role', 'jabber', 'aim', 'yim', 'comment_shortcuts', 'admin_color', 'use_ssl', 'show_admin_bar_front'. 

* Updated xprofile_update method to correctly update multiple values for any field in array format. Earlier, it was updating all values as array. You can also update more than one field simultaneously.

* Fixed some documentation typos

= 1.1 =

* Added fb_connect method. It needs valid 'access_token' var.

* Added validate_auth_cookie method. It needs 'cookie' var.

* Added generate_auth_cookie method. It needs 'nonce' var.

* Added delete_user_meta method. It needs 'cookie' and 'meta_key' var and 'meta_value' to delete.

* Added update_user_meta method. It needs 'cookie' and 'meta_key' var and 'meta_value' to update.

* Added get_user_meta method. It needs 'cookie'. 'meta_key' var is optional.

* Added xprofile method. It needs 'user_id' and any profile 'field' var.

* Added xprofile_update method. It needs 'cookie' and any profile 'field' var and 'value'.


= 1.0 =

* Added retrieve_password method. It needs user_login var.

* Added get_avatar method. It needs user_id var.

* Added get_userinfo method. It needs user_id var.



= 0.1 =

* Initial release.

==Upgrade Notice==

==Documentation==

* There are following methods available: register, get_avatar, get_userinfo, retrieve_password, validate_auth_cookie, generate_auth_cookie, get_currentuserinfo, get_user_meta, update_user_meta, delete_user_meta, xprofile, xprofile_update, fb_connect

* nonce can be created by calling if you are registering user. http://localhost/api/get_nonce/?controller=user&method=register

* You can then use 'nonce' value to register user.

= Method: info =

http://localhost/api/user/info/

This returns plugin version.

= Method: register =

http://localhost/api/user/register/?username=john&email=john@domain.com&nonce=8bdfeb4e16&display_name=John&notify=both

To register user & get valid cookie for 100 seconds:
http://localhost/api/user/register/?username=john&email=john@domain.com&display_name=John&notify=both&seconds=100

Optional fields: 'user_pass', 'user_nicename', 'user_url', 'nickname', 'first_name', 'last_name', 'description', 'rich_editing', 'user_registered', 'jabber', 'aim', 'yim', 'comment_shortcuts', 'admin_color', 'use_ssl', 'show_admin_bar_front'. 

Please make sure you provide valid values that these fields expect in correct format.

To disbale registration email notification to user:

http://localhost/api/user/register/?username=john&email=john@domain.com&nonce=8bdfeb4e16&display_name=John&notify=no

To register with user provided password:

http://localhost/api/user/register/?username=john&email=john@domain.com&nonce=8bdfeb4e16&display_name=John&notify=both&user_pass=YOUR-PASSWORD

= Method: fb_connect =

It needs valid 'access_token' var.

http://localhost/api/user/fb_connect/?access_token=CAACEdEose0cBADLKmcHWOZCnW4RGU8emG

Provide valid access_token with email extended permission. To generate test access_token, try this tool https://developers.facebook.com/tools/explorer/ and select the app from above drop down that you want to get access_token (You must have joined that app already with email permission to generate access_token) for and then select email from the fields. By default, only 'id' and 'name' are added but you need to include 'email' for user identification.

You will have to first allow extended permission for email in the app joining screen. Please note that above tool is only for testing, you generate valid access_token using the Graph API in your app. You should know Facebook Graph API to use this endpoint.

= Method: validate_auth_cookie =

It needs 'cookie' var.

http://localhost/api/user/validate_auth_cookie/?cookie=admin|43089754375034fjwfn39u8

= Method: generate_auth_cookie =

It needs `username`, `password` vars. `seconds` is optional.

First get the nonce: http://localhost/api/get_nonce/?controller=user&method=generate_auth_cookie

Then generate cookie: http://localhost/api/user/generate_auth_cookie/?username=john&password=PASSWORD-HERE

Optional 'seconds' var. It provided, generated cookie will be valid for that many seconds, otherwise default is for 14 days.

generate cookie for 1 minute: http://localhost/api/user/generate_auth_cookie/?username=john&password=PASSWORD-HERE&seconds=60

60 means 1 minute.

= Method: delete_user_meta =

It needs 'cookie' and 'meta_key' var and 'meta_value' to delete.

http://localhost/api/user/delete_user_meta/?cookie=COOKIE-HERE&meta_key=KEY-HERE&meta_value=VALUE-HERE

= Method: update_user_meta =

It needs 'cookie' and 'meta_key' var and 'meta_value' to update. You may send multiple values separated by comma.  

http://localhost/api/user/update_user_meta/?cookie=COOKIE-HERE&meta_key=KEY-HERE&meta_value=VALUE-HERE

http://localhost/api/user/update_user_meta/?cookie=COOKIE-HERE&&meta_key=KEY-HERE&meta_value=value1,value2,value3

= Method: update_user_meta_vars =

It needs 'cookie' and any user meta variables. This endpoint allows you cut http requests if you have to add/update more than one user_meta field at a time.

http://localhost/api/user/update_user_meta_vars/?cookie=COOKIE-HERE&website=user-website.com&city=Chicago&country=USA&skills=php,css,js,web design

in the above call, website, city, country and skills are meta_key for WordPress user_meta. It is different from BuddyPress xProfile fields.

Please make sure you provide ending comma for all those fields which have multiple values. e.g. If 'skills' field has multiple values, pass them like 
http://localhost/api/user/update_user_meta_vars/?cookie=COOKIE-HERE&skills=PHP,MySQL, or &skills=PHP, make sure you always pass ending comma for multi-select fields to be added in array format.

= Method: get_user_meta =

It needs 'user_id'. 'meta_key' var is optional.

http://localhost/api/user/get_user_meta/?cookie=COOKIE-HERE&meta_key=KEY-HERE


= Method: xprofile =

It needs 'user_id' and any profile 'field' var.

http://localhost/api/user/xprofile/?user_id=USERID-HERE&field=FIELD-LABEL-HERE

= Method: xprofile_update =

It needs 'cookie' and any profile 'field' var and 'value'.

http://localhost/api/user/xprofile_update/?cookie=COOKIE-HERE&exact-xprofile-field-label=value

http://localhost/api/user/xprofile_update/?cookie=COOKIE-HERE&field=value&field2=value&multi-value-field=value1,value2,value3

Please make sure you provide ending comma for all those fields which have multiple values. e.g. If 'skills' xProfile field has multiple values, pass them like 
http://localhost/api/user/xprofile_update/?cookie=COOKIE-HERE&skills=PHP,MySQL, or &skills=PHP, make sure you always pass ending comma for multi-select fields to be added in array format.

= Method: retrieve_password =

It needs user_login var.

http://localhost/api/user/retrieve_password/?user_login=john

= Method: get_avatar =

It needs user_id var.

http://localhost/api/user/get_avatar/?user_id=1

= Method: get_userinfo =

It needs user_id var.

http://localhost/api/user/get_userinfo/?user_id=1

= Method: post_comment =

It needs 'cookie', 'post_id', 'content', 'comment_status' vars.

http://localhost/api/user/post_comment/?cookie=COOKIE-HERE&post_id=ID&content=Comment contents here&comment_status=1

For additional features, pro version plugin details check here http://www.parorrey.com/solutions/json-api-user-plus/