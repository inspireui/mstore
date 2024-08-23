// ignore_for_file: prefer_single_quotes, lines_longer_than_80_chars final
Map<String, dynamic> environment = {
  "appConfig": "lib/config/config_en.json",
  "serverConfig": {
    "type": "woo",
    "url": "http://mstore.local",
    "consumerKey": "ck_98f9ca71c82ec652ac27194eafef4a9cf2af300a",
    "consumerSecret": "cs_83d385c0711ace08304126f48618d7a9aa7ff663",
    "blog": "http://mstore.local",
    "forgetPassword": "http://mstore.local/wp-login.php?action=lostpassword"
  },

  /// ➡️ lib/common/config/general.dart
  "defaultDarkTheme": false,
  "enableRemoteConfigFirebase": false,
  "enableFirebaseAnalytics": false,
  "enableFacebookAppEvents": false,

  /// Web Proxy: use only for web FluxStore
  "webProxy": "",

  /// If maxTextScale is null or maxTextScale <= 0,
  /// the application will automatically get the system's textscaleFactor.
  /// Otherwise, the maxTextScale value will be taken as
  /// the maximum value for textScale.
  "maxTextScale": null,

  "loginSMSConstants": {
    "countryCodeDefault": "US",
    "dialCodeDefault": "+1",
    "nameDefault": "United States",
  },
  "phoneNumberConfig": {
    "enable": false,
    "countryCodeDefault": "US",
    "dialCodeDefault": "+1",
    "useInternationalFormat": true,
    "selectorFlagAsPrefixIcon": true,
    "showCountryFlag": true,
    "customCountryList": [], // List alpha_2_code. E.g: ["VN", "AU"]
    "selectorType": "BOTTOM_SHEET", // [DROPDOWN, BOTTOM_SHEET, DIALOG]
  },
  "appRatingConfig": {
    'showOnOpen': false,
    'android': 'com.inspireui.fluxstore',
    'ios': '1469772800',
    'minDays': 7,
    'minLaunches': 10,
    'remindDays': 7,
    'remindLaunches': 10,
  },
  "advanceConfig": {
    "DefaultLanguage": "en",
    "DetailedBlogLayout": "halfSizeImageType",
    "EnablePointReward": false,
    "hideOutOfStock": false,
    "HideEmptyTags": true,
    "HideEmptyCategories": true,
    "EnableRating": true,

    /// If rating is 0, it will be hidden. Apply for whole app (include product
    /// detail, store, listing, review, testimonial; exclude product card).
    "hideEmptyRating": true,

    "EnableCart": true,
    "ShowBottomCornerCart": true,

    /// Enable search by SKU in search screen
    "EnableSkuSearch": true,

    /// Show stock Status on product List & Product Detail
    "showStockStatus": true,

    /// Gird count setting on Category screen
    "GridCount": 3,

    /// set isCaching to true if you have upload the config file to mstore-api
    /// set kIsResizeImage to true if you have finished running Re-generate image plugin
    /// ref: https://support.inspireui.com/help-center/articles/3/8/19/app-performance
    "isCaching": false,
    "kIsResizeImage": false,
    "httpCache": true,

    "DefaultCurrency": {
      "symbol": "\$",
      "decimalDigits": 2,
      "symbolBeforeTheNumber": true,
      "currency": "USD",
      "currencyCode": "usd",
    },
    "Currencies": [
      {
        "symbol": "\$",
        "decimalDigits": 2,
        "symbolBeforeTheNumber": true,
        "currency": "USD",
        "currencyCode": "USD",
      },
      {
        "symbol": "₹",
        "decimalDigits": 0,
        "symbolBeforeTheNumber": true,
        "currency": "INR",
        "currencyCode": "INR",
      },
      {
        "symbol": "đ",
        "decimalDigits": 2,
        "symbolBeforeTheNumber": false,
        "currency": "VND",
        "currencyCode": "VND",
      },
      {
        "symbol": "€",
        "decimalDigits": 2,
        "symbolBeforeTheNumber": true,
        "currency": "EUR",
        "currencyCode": "EUR",
      },
      {
        "symbol": "£",
        "decimalDigits": 2,
        "symbolBeforeTheNumber": true,
        "currency": "Pound sterling",
        "currencyCode": "GBP",
      },
      {
        'symbol': 'AR\$',
        'decimalDigits': 2,
        'symbolBeforeTheNumber': true,
        'currency': 'ARS',
        'currencyCode': 'ARS',
      },
      {
        'symbol': 'R',
        'decimalDigits': 2,
        'symbolBeforeTheNumber': true,
        'currency': 'ZAR',
        'currencyCode': 'ZAR',
      },
      {
        'symbol': '₱',
        'decimalDigits': 2,
        'symbolBeforeTheNumber': true,
        'currency': 'PHP',
        'currencyCode': 'PHP',
      },
      {
        'symbol': 'Rp',
        'decimalDigits': 2,
        'symbolBeforeTheNumber': true,
        'currency': 'IDR',
        'currencyCode': 'IDR',
      },
      {
        'symbol': 'SAR',
        'decimalDigits': 2,
        'symbolBeforeTheNumber': true,
        'currency': 'Saudi Riyal',
        'currencyCode': 'SAR',
      },
      {
        'symbol': 'OMR',
        'decimalDigits': 2,
        'symbolBeforeTheNumber': false,
        'currency': 'Omani Rial',
        'currencyCode': 'OMR',
      }
    ],

    /// Below config is used for Magento store
    "DefaultStoreViewCode": "",
    "EnableAttributesConfigurableProduct": ["color", "size"],

    /// if the woo commerce website supports multi languages
    /// set false if the website only have one language
    "isMultiLanguages": true,

    /// Review gets approved automatically on woocommerce admin without
    /// requiring administrator to approve.
    "EnableApprovedReview": false,

    /// Sync Cart from website and mobile
    "EnableSyncCartFromWebsite": false,
    "EnableSyncCartToWebsite": false,

    /// Enable firebase to support FCM, realtime chat for Fluxstore MV
    "EnableFirebase": true,

    /// ratio Product Image, default value is 1.2 = height / width
    "RatioProductImage": 1.2,

    /// Enable Coupon Code When checkout
    "EnableCouponCode": true,

    /// Enable to Show Coupon list.
    "ShowCouponList": true,

    /// Enable this will show all coupons in Coupon list.
    /// Disable will show only coupons which is restricted to the current user"s email.
    "ShowAllCoupons": true,

    /// Show expired coupons in Coupon list.
    "ShowExpiredCoupons": true,
    "AlwaysShowTabBar": false,

    /// Privacy Policies page ID. If page ID is null, use the URL instead.
    /// Accessible in the app via Settings > Privacy menu.
    "PrivacyPoliciesPageUrlOrId": "https://inspireui.com/privacy/",

    "SupportPageUrl": "https://support.inspireui.com/",

    "DownloadPageUrl": 'https://fluxstore.app/',

    "AboutUSPageUrl": "https://codecanyon.net/user/inspireui",

    "NewsPageUrl": "https://products.inspireui.com/",

    "FAQPageUrl": "https://products.inspireui.com/have-a-question/",

    "SocialConnectUrl": [
      {
        "name": "Youtube",
        "icon": "assets/icons/brands/youtube.svg",
        "url": "https://www.youtube.com/inspireui?sub_confirmation=1"
      },
      {
        "name": "Facebook",
        "icon": "assets/icons/brands/facebook.svg",
        "url": "https://www.facebook.com/inspireUI/"
      },
      {
        "name": "Twitter",
        "icon": "assets/icons/brands/twitter.svg",
        "url": "https://twitter.com/InspireUI"
      },
    ],

    "AutoDetectLanguage": false,
    "QueryRadiusDistance": 10,
    "MinQueryRadiusDistance": 1,

    /// Distance in kilometers
    "MaxQueryRadiusDistance": 10,

    /// Time to display toast message (milliseconds)
    "TimeShowToastMessage": 1500,

    /// Enable Membership Pro Ultimate WP
    "EnableMembershipUltimate": false,

    /// Enable Paid Membership Pro
    "EnablePaidMembershipPro": false,

    /// Enable Delivery Date when doing checkout
    "EnableDeliveryDateOnCheckout": false,

    /// Enable new SMS Login
    "EnableNewSMSLogin": true,

    /// Enable bottom add to cart from product card view
    "EnableBottomAddToCart": true,

    /// Disable inAppWebView to use webview_flutter
    /// so webview can navigate to external app.
    /// Useful for webview checkout which need to handle payment in another app.
    "inAppWebView": false,
    'AlwaysClearWebViewCache': false,
    'AlwaysClearWebViewCookie': false,
    "WebViewScript": "",

    'AlwaysRefreshBlog': false,

    ///support multi currency via WOOCS – Currency Switcher for WooCommerce plugin (https://wordpress.org/plugins/woocommerce-currency-switcher/)
    "EnableWOOCSCurrencySwitcher": true,

    /// Enable product backdrop layout - https://tppr.me/L5Pnf
    "enableProductBackdrop": false,

    /// false: show category menu as Text https://tppr.me/v3bLI
    /// true: show as Category Image
    "categoryImageMenu": true,

    /// Support Digits : WordPress Mobile Number Signup and Login.
    /// Plugin (https://codecanyon.net/item/digits-wordpress-mobile-number-signup-and-login/19801105)
    "EnableDigitsMobileLogin": false,
    "EnableDigitsMobileFirebase": false,
    "EnableDigitsMobileWhatsApp": false,

    /// Enable Ajax Search Pro, https://your-domain/wp-json/ajax-search-pro/v0/woo_search?s=
    "AjaxSearchURL": "",

    "gdpr": {
      "showPrivacyPolicyFirstTime": false,
      "showDeleteAccount": true,
      "confirmCaptcha": "PERMANENTLY DELETE",
    },

    /// show order notes in order detail with private notes
    "OrderNotesWithPrivateNote": true,

    "OrderNotesLinkSupport": false,

    /// Just accept select the country on this list
    /// example: {"vn", "ae"}
    "supportCountriesShipping": null,

    // Enable the request Notify permission from onboarding
    "showRequestNotification": true,

    "versionCheck": {
      "enable": false,
      "iOSAppStoreCountry": "US",
    },
    "inAppUpdateForAndroid": {
      "enable": false,
      // "flexible, immediate"
      "typeUpdate": "flexible",
    },
    "categoryConfig": {
      // Enable this option when the store has more than 100 category items
      "enableLargeCategories": false,
      "deepLevel": 3,
    },

    /// Example: "pinnedProductTags": ["feature", "new"],
    /// will show the tag before product title in the product list.
    "pinnedProductTags": [],

    /// Enable WooCommerce Wholesale Prices
    "EnableWooCommerceWholesalePrices": false,

    //Require to select site when open app for multi sites
    "IsRequiredSiteSelection": true,

    /// Only for Fluxstore Listing
    "showOpeningStatus": true,

    "b2bKingConfig": {
      "enabled": false,
      "guestAccessRestriction":
          "replace_prices_quote", //none, replace_prices_quote
    },

    /// PW WooCommerce Gift Cards (https://wordpress.org/plugins/pw-woocommerce-gift-cards/)
    "enablePWGiftCard": true,

    /// TeraWallet Withdrawal (https://standalonetech.com/product/wallet-withdrawal/)
    "EnableTeraWalletWithdrawal": false,

    //set param is_all_data=true to get full product data for WooCommerce
    "EnableIsAllData": false,
  },
  "defaultDrawer": {
    "logo": "assets/images/logo.png",
    "background": null,
    "items": [
      {"type": "home", "show": true},
      {"type": "blog", "show": true},
      {"type": "categories", "show": true},
      {"type": "cart", "show": true},
      {"type": "profile", "show": true},
      {"type": "login", "show": true},
      {"type": "category", "show": true}
    ]
  },
  "defaultSettings": [
    "biometrics",
    "products",
    "wallet",
    "chat",
    "wishlist",
    "notifications",
    "language",
    "currencies",
    "darkTheme",
    "order",
    "point",
    "rating",
    "privacy",
    "about",
  ],
  "loginSetting": {
    /// Set to false to disable both login and registration options
    "enable": true,

    /// Set to false to disable only registration option
    "enableRegister": true,
    "IsRequiredLogin": false,
    "showAppleLogin": true,
    "showFacebook": false,
    "showSMSLogin": true,
    "showGoogleLogin": true,
    "showPhoneNumberWhenRegister": false,
    "requirePhoneNumberWhenRegister": false,

    /// Lets users freely input a Username instead of the default from email
    "requireUsernameWhenRegister": false,
    "isResetPasswordSupported": true,

    /// Set true value to show only the SMS Login screen, and set the false
    /// value to show default login screen with other login buttons.
    "smsLoginAsDefault": false,

    /// For Facebook login.
    /// These configs are only used for FluxBuilder's Auto build feature.
    /// To update manually, follow this below doc:
    /// https://support.inspireui.com/help-center/articles/42/44/32/social-login#login
    "facebookAppId": "430258564493822",
    "facebookLoginProtocolScheme": "fb430258564493822",

    // This config is used to apple for Wordpress site
    "appleLoginSetting": {
      "iOSBundleId": "com.inspireui.mstore.flutter",
      "appleAccountTeamID": "S9RPAM8224"
    }
  },
  "oneSignalKey": {"enable": false, "appID": ""},

  "onBoardingConfig": {
    'enableOnBoarding': true,
    'version': 1,
    'autoCropImageByDesign': true,
    'isOnlyShowOnFirstTime': true,
    "showLanguage": true,
    'data': [
      {
        'title': 'Welcome to FluxStore',
        'image': 'assets/images/fogg-delivery-1.png',
        'desc': 'Fluxstore is on the way to serve you. '
      },
      {
        'title': 'Connect Surrounding World',
        'image': 'assets/images/fogg-uploading-1.png',
        'desc':
            'See all things happening around you just by a click in your phone. Fast, convenient and clean.'
      },
      {
        'title': "Let's Get Started",
        'image': 'assets/images/fogg-order-completed.png',
        'desc': "Waiting no more, let's see what we get!"
      }
    ],
  },

  "vendorOnBoardingData": [
    {
      'title': 'Welcome aboard',
      'image': 'assets/images/searching.png',
      'desc': 'Just a few more steps to become our vendor'
    },
    {
      'title': 'Let\'s Get Started',
      'image': 'assets/images/manage.png',
      'desc': 'Good Luck for great beginnings.'
    }
  ],

  /// ➡️ lib/common/advertise.dart
  "adConfig": {
    "enable": false,
    "facebookTestingId": "",
    "googleTestingId": [],
    "ads": [
      {
        "type": "banner",
        "provider": "google",
        "iosId": "ca-app-pub-3940256099942544/2934735716",
        "androidId": "ca-app-pub-3940256099942544/6300978111",
        "showOnScreens": ["home", "search"],
        "waitingTimeToDisplay": 2,
      },
      {
        "type": "banner",
        "provider": "google",
        "iosId": "ca-app-pub-2101182411274198/5418791562",
        "androidId": "ca-app-pub-2101182411274198/4052745095",

        /// "showOnScreens": ["home", "category", "product-detail"],
      },
      {
        "type": "interstitial",
        "provider": "google",
        "iosId": "ca-app-pub-3940256099942544/4411468910",
        "androidId": "ca-app-pub-3940256099942544/4411468910",
        "showOnScreens": ["profile"],
        "waitingTimeToDisplay": 5,
      },
      {
        "type": "reward",
        "provider": "google",
        "iosId": "ca-app-pub-3940256099942544/1712485313",
        "androidId": "ca-app-pub-3940256099942544/4411468910",
        "showOnScreens": ["cart"],

        /// "waitingTimeToDisplay": 8,
      },
      {
        "type": "banner",
        "provider": "facebook",
        "iosId": "IMG_16_9_APP_INSTALL#430258564493822_876131259906548",
        "androidId": "IMG_16_9_APP_INSTALL#430258564493822_489007588618919",
        "showOnScreens": ["home"],

        /// "waitingTimeToDisplay": 8,
      },
      {
        "type": "interstitial",
        "provider": "facebook",
        "iosId": "430258564493822_489092398610438",
        "androidId": "IMG_16_9_APP_INSTALL#430258564493822_489092398610438",

        /// "showOnScreens": ["profile"],
        /// "waitingTimeToDisplay": 8,
      },
    ],

    /// "adMobAppId" is only used for FluxBuilder's Auto build feature.
    /// To update manually, follow this below doc:
    /// https://support.inspireui.com/help-center/articles/42/44/17/admob-and-facebook-ads#2-setup-google-admob-for-flutter
    "adMobAppIdIos": "ca-app-pub-7432665165146018~2664444130",
    "adMobAppIdAndroid": "ca-app-pub-7432665165146018~2664444130",
  },

  /// ➡️ lib/common/dynamic_link.dart
  "firebaseDynamicLinkConfig": {
    "isEnabled": true,
    "shortDynamicLinkEnable": true,

    /// Domain is the domain name for your product.
    /// Let’s assume here that your product domain is “example.com”.
    /// Then you have to mention the domain name as : https://example.page.link.
    "uriPrefix": "https://fluxstoreinspireui.page.link",
    //The link your app will open
    "link": "https://mstore.io/",
    //----------* Android Setting *----------//
    "androidPackageName": "com.inspireui.fluxstore",
    "androidAppMinimumVersion": 1,
    //----------* iOS Setting *----------//
    "iOSBundleId": "com.inspireui.mstore.flutter",
    "iOSAppMinimumVersion": "1.0.1",
    "iOSAppStoreId": "1469772800"
  },

  "dynamicLinkConfig": {
    "enable": true,
    "type": "branchIO",
    "branchIO": {
      "liveMode": false,
    }
  },

  /// ➡️ lib/common/languages.dart
  "languagesInfo": [
    // 1 English - intl_en.arb
    {
      "name": "English",
      "icon": "assets/images/country/gb.png",
      "code": "en",
      "text": "English",
      "storeViewCode": ""
    },
    // 2 Hindi - intl_hi.arb
    {
      "name": "Hindi",
      "icon": "assets/images/country/in.png",
      "code": "hi",
      "text": "हिन्दी",
      "storeViewCode": "hi"
    },
    // 3 Spanish - intl_es.arb
    {
      "name": "Spanish",
      "icon": "assets/images/country/es.png",
      "code": "es",
      "text": "Español",
      "storeViewCode": ""
    },
    // 4 French - intl_fr.arb
    {
      "name": "French",
      "icon": "assets/images/country/fr.png",
      "code": "fr",
      "text": "Français",
      "storeViewCode": "fr"
    },
    // 5 Arabic - intl_ar.arb
    {
      "name": "Arabic",
      "icon": "assets/images/country/ar.png",
      "code": "ar",
      "text": "العربية",
      "storeViewCode": "ar"
    },
    // 6 Russian - intl_ru.arb
    {
      "name": "Russian",
      "icon": "assets/images/country/ru.png",
      "code": "ru",
      "text": "Русский",
      "storeViewCode": "ru"
    },
    // 7 Indonesian - intl_id.arb
    {
      "name": "Indonesian",
      "icon": "assets/images/country/id.png",
      "code": "id",
      "text": "Bahasa Indonesia",
      "storeViewCode": "id"
    },
    // 8 Japanese - intl_ja.arb
    {
      "name": "Japanese",
      "icon": "assets/images/country/ja.png",
      "code": "ja",
      "text": "日本語",
      "storeViewCode": ""
    },
    // 9 Korean - intl_ko.arb
    {
      "name": "Korean",
      "icon": "assets/images/country/ko.png",
      "code": "ko",
      "text": "한국어/조선말",
      "storeViewCode": "ko"
    },
    // 10 Vietnamese - intl_vi.arb
    {
      "name": "Vietnamese",
      "icon": "assets/images/country/vn.png",
      "code": "vi",
      "text": "Tiếng Việt",
      "storeViewCode": ""
    },
    // 11 Romanian - intl_ro.arb
    {
      "name": "Romanian",
      "icon": "assets/images/country/ro.png",
      "code": "ro",
      "text": "Românește",
      "storeViewCode": "ro"
    },
    // 12 Turkish - intl_tr.arb
    {
      "name": "Turkish",
      "icon": "assets/images/country/tr.png",
      "code": "tr",
      "text": "Türkçe",
      "storeViewCode": "tr"
    },
    // 13 Italian - intl_it.arb
    {
      "name": "Italian",
      "icon": "assets/images/country/it.png",
      "code": "it",
      "text": "Italiano",
      "storeViewCode": "it"
    },
    // 14 German - intl_de.arb
    {
      "name": "German",
      "icon": "assets/images/country/de.png",
      "code": "de",
      "text": "Deutsch",
      "storeViewCode": "de"
    },
    // 15 Brazilian Portuguese - intl_pt_BR.arb
    {
      "name": "Brazilian Portuguese",
      "icon": "assets/images/country/br.png",
      "code": "pt_BR",
      "text": "Português do Brasil",
      "storeViewCode": ""
    },
    // 16 Portuguese from Portugal - intl_pt_PT.arb
    {
      "name": "Portuguese from Portugal",
      "icon": "assets/images/country/pt.png",
      "code": "pt_PT",
      "text": "Português de Portugal",
      "storeViewCode": ""
    },
    // 17 Hungarian - intl_hu.arb
    {
      "name": "Hungarian",
      "icon": "assets/images/country/hu.png",
      "code": "hu",
      "text": "Magyar nyelv",
      "storeViewCode": "hu"
    },
    // 18 Hebrew - intl_he.arb
    {
      "name": "Hebrew",
      "icon": "assets/images/country/he.png",
      "code": "he",
      "text": "עִבְרִית",
      "storeViewCode": "he"
    },
    // 19 Thai - intl_th.arb
    {
      "name": "Thai",
      "icon": "assets/images/country/th.png",
      "code": "th",
      "text": "ภาษาไทย",
      "storeViewCode": "th"
    },
    // 20 Dutch - intl_nl.arb
    {
      "name": "Dutch",
      "icon": "assets/images/country/nl.png",
      "code": "nl",
      "text": "Nederlands",
      "storeViewCode": "nl"
    },
    // 21 Serbian - intl_sr.arb
    {
      "name": "Serbian",
      "icon": "assets/images/country/rs.png", // Remove `sr.jpeg` later
      "code": "sr",
      "text": "српски",
      "storeViewCode": "sr"
    },
    // 22 Polish - intl_pl.arb
    {
      "name": "Polish",
      "icon": "assets/images/country/pl.png",
      "code": "pl",
      "text": "Język polski",
      "storeViewCode": "pl"
    },
    // 23 Persian - intl_fa.arb
    {
      "name": "Persian",
      "icon": "assets/images/country/fa.png",
      "code": "fa",
      "text": "زبان فارسی",
      "storeViewCode": ""
    },
    // 24 Ukrainian - intl_uk.arb
    {
      "name": "Ukrainian",
      "icon": "assets/images/country/uk.png",
      "code": "uk",
      "text": "Українська мова",
      "storeViewCode": ""
    },
    // 25 Bengali - intl_bn.arb
    {
      "name": "Bengali",
      "icon": "assets/images/country/bn.png",
      "code": "bn",
      "text": "বাংলা",
      "storeViewCode": ""
    },
    // 26 Tamil - intl_ta.arb
    {
      "name": "Tamil",
      "icon": "assets/images/country/ta.png",
      "code": "ta",
      "text": "தமிழ்",
      "storeViewCode": ""
    },
    // 27 Kurdish - intl_ku.arb
    {
      "name": "Kurdish",
      "icon": "assets/images/country/ku.png",
      "code": "ku",
      "text": "Kurdî / کوردی",
      "storeViewCode": ""
    },
    // 28 Czech - intl_cs.arb
    {
      "name": "Czech",
      "icon": "assets/images/country/cs.png",
      "code": "cs",
      "text": "Čeština",
      "storeViewCode": "cs"
    },
    // 29 Swedish - intl_sv.arb
    {
      "name": "Swedish",
      "icon": "assets/images/country/sv.png",
      "code": "sv",
      "text": "Svenska",
      "storeViewCode": ""
    },
    // 30 Finland - intl_fi.arb
    {
      "name": "Finland",
      "icon": "assets/images/country/fi.png",
      "code": "fi",
      "text": "Suomi",
      "storeViewCode": ""
    },
    // 31 Greek - intl_el.arb
    {
      "name": "Greek",
      "icon": "assets/images/country/el.png",
      "code": "el",
      "text": "Ελληνικά",
      "storeViewCode": ""
    },
    // 32 Khmer - intl_km.arb
    {
      "name": "Khmer",
      "icon": "assets/images/country/km.png",
      "code": "km",
      "text": "ភាសាខ្មែរ",
      "storeViewCode": ""
    },
    // 33 Kannada - intl_kn.arb
    {
      "name": "Kannada",
      "icon": "assets/images/country/kn.png",
      "code": "kn",
      "text": "ಕನ್ನಡ",
      "storeViewCode": ""
    },
    // 34 Marathi - intl_mr.arb
    {
      "name": "Marathi",
      "icon": "assets/images/country/in.png", // Remove `mr.jpeg` later
      "code": "mr",
      "text": "मराठी भाषा",
      "storeViewCode": ""
    },
    // 35 Malay - intl_ms.arb
    {
      "name": "Malay",
      "icon": "assets/images/country/ms.png", // Remove `ms.jpeg` later
      "code": "ms",
      "text": "بهاس ملايو",
      "storeViewCode": ""
    },
    // 36 Bosnian - intl_bs.arb
    {
      "name": "Bosnian",
      "icon": "assets/images/country/bs.png",
      "code": "bs",
      "text": "босански",
      "storeViewCode": ""
    },
    // 37 Lao - intl_lo.arb
    {
      "name": "Lao",
      "icon": "assets/images/country/lo.png",
      "code": "lo",
      "text": "ພາສາລາວ",
      "storeViewCode": ""
    },
    // 38 Slovak - intl_sk.arb
    {
      "name": "Slovak",
      "icon": "assets/images/country/sk.png",
      "code": "sk",
      "text": "Slovaščina",
      "storeViewCode": ""
    },
    // 39 Swahili - intl_sw.arb
    {
      "name": "Swahili",
      "icon": "assets/images/country/sw.png",
      "code": "sw",
      "text": "كِيْسَوَاحِيْلِيْ",
      "storeViewCode": ""
    },
    // 40 Chinese - intl_zh.arb
    {
      "name": "Chinese",
      "icon": "assets/images/country/zh.png",
      "code": "zh",
      "text": "中文",
      "storeViewCode": ""
    },
    // 41 Chinese Traditional - intl_zh_TW.arb
    {
      "name": "Chinese (traditional)",
      "icon": "assets/images/country/zh.png",
      "code": "zh_TW",
      "text": "漢語",
      "storeViewCode": ""
    },
    // 42 Chinese Simplified - intl_zh_CN.arb
    {
      "name": "Chinese (simplified)",
      "icon": "assets/images/country/zh.png",
      "code": "zh_CN",
      "text": "汉语",
      "storeViewCode": ""
    },
    // 43 Burmese - intl_my.arb
    {
      "name": "Burmese",
      "icon": "assets/images/country/my.png",
      "code": "my",
      "text": "မြန်မာဘာသာစကား",
      "storeViewCode": ""
    },
    // 44 Albanian - intl_sq.arb
    {
      "name": "Albanian",
      "icon": "assets/images/country/sq.png",
      "code": "sq",
      "text": "Shqip",
      "storeViewCode": ""
    },
    // 45 Danish - intl_da.arb
    {
      "name": "Danish",
      "icon": "assets/images/country/da.png", // Remove `da.svg` later
      "code": "da",
      "text": "Dansk",
      "storeViewCode": ""
    },
    // 46 Tigrinya - intl_ti.arb
    {
      "name": "Tigrinya",
      "icon": "assets/images/country/er.png",
      "code": "ti",
      "text": "ትግርኛ",
      "storeViewCode": "ti"
    },
    // 47 Urdu - intl_ur.arb
    {
      "name": "Urdu",
      "icon": "assets/images/country/pk.png",
      "code": "ur",
      "text": "اُردُو",
      "storeViewCode": ""
    },
    // 48 Azerbaijani- intl_az.arb
    {
      "name": "Azerbaijani",
      "icon": "assets/images/country/az.png", // Remove `az.jpg` later
      "code": "az",
      "text": "Azərbaycan dili",
      "storeViewCode": ""
    },
    // 49 Kazakhstan - intl_kk.arb
    {
      "name": "Kazakhstan",
      "icon": "assets/images/country/kz.png",
      "code": "kk",
      "text": "Қазақ тілі",
      "storeViewCode": ""
    },
    // 50 Uzbek - intl_uz.arb
    {
      "name": "Uzbek",
      "icon": "assets/images/country/uz.png",
      "code": "uz",
      "text": "O'zbek",
      "storeViewCode": ""
    },
    // 51 Estonian - intl_et.arb
    {
      "name": "Estonian",
      "icon": "assets/images/country/et.png",
      "code": "et",
      "text": "Eesti",
      "storeViewCode": ""
    },
    // 52 Catalan - intl_ca.arb
    {
      "name": "Catalan",
      "icon": "assets/images/country/ca.png",
      "code": "ca",
      "text": "Català",
      "storeViewCode": ""
    },
    // 53 Georgia - intl_ka.arb
    {
      "name": "Georgia",
      "icon": "assets/images/country/ka.png",
      "code": "ka",
      "text": "ქართული ენა",
      "storeViewCode": ""
    },
    // 54 Bulgaria - intl_bg.arb
    {
      "name": "Bulgaria",
      "icon": "assets/images/country/bg.png",
      "code": "bg",
      "text": "Български език",
      "storeViewCode": ""
    },
    // 55 Sinhala - intl_si.arb
    {
      "name": "Sinhala",
      "icon": "assets/images/country/si.png",
      "code": "si",
      "text": "සිංහල",
      "storeViewCode": ""
    },
    // 56 Lithuanian - intl_lt.arb
    {
      "name": "Lithuanian",
      "icon": "assets/images/country/lt.png",
      "code": "lt",
      "text": "Lietuva",
      "storeViewCode": ""
    },
    // 57 Norwegian - intl_no.arb
    {
      "name": "Norwegian",
      "icon": "assets/images/country/no.png",
      "code": "no",
      "text": "Nordmenn",
      "storeViewCode": ""
    },
  ],

  /// ➡️  lib/common/config/payments.dart
  "paymentConfig": {
    "DefaultCountryISOCode": "US",

    "DefaultStateISOCode": "LA",

    /// Enable the Shipping option from Checkout, support for the Digital Download
    "EnableShipping": true,

    /// Enable the address shipping.
    /// Set false if use for the app like Download Digial Asset which is not required the shipping feature.
    "EnableAddress": true,

    /// Allow customers to add note when order
    "EnableCustomerNote": true,

    /// Allow customers to add address location link to order note
    "EnableAddressLocationNote": false,

    /// Allow both alphabetical and numerical characters in ZIP code
    "EnableAlphanumericZipCode": false,

    /// Enable the product review option
    "EnableReview": true,

    /// Enable the Google Maps picker from Billing Address.
    "allowSearchingAddress": true,

    "GuestCheckout": true,

    /// Enable Payment option
    "EnableOnePageCheckout": false,
    "NativeOnePageCheckout": false,

    "ShowWebviewCheckoutSuccessScreen": true,

    /// This config is same with checkout page slug in the website
    "CheckoutPageSlug": {"en": "checkout"},

    /// Enable Credit card payment (only available for Fluxstore Shopipfy)
    "EnableCreditCard": false,

    /// Enable update order status to processing after checkout by COD on woo commerce
    "UpdateOrderStatus": false,

    /// Show order notes in order history detail.
    "ShowOrderNotes": true,

    /// Show Refund and Cancel button on Order Detail
    "EnableRefundCancel": true,

    /// If the order completed date is after this period (days), the refund button will be hidden.
    "RefundPeriod": 7,

    /// If you wish to display the Cancel and Refund button for a specific payment method on Order Detail screen, please enter the payment method ID. For example: "PaymentListAllowsCancelAndRefund": ["paypal","stripe"],

    /// Alternatively, if you want to show the Cancel and Refund button for all payment methods, leave it blank.
    "PaymentListAllowsCancelAndRefund": [],

    /// Apply the extra fee for the COD method
    /// amountStop: Amount to stop charge the extra fee
    "SmartCOD": {"enabled": true, "extraFee": 10, "amountStop": 200},

    /// List ids to hide some unnecessary payment methods
    "excludedPaymentIds": [],

    /// Show Transaction Details in Order History Screen
    "ShowTransactionDetails": true,

    /// List of payment method ids used for web
    "webPaymentIds": ["cod", "bacs"],
  },
  "payments": {
    "stripe_v2_apple_pay": "assets/icons/payment/apple-pay-mark.svg",
    "stripe_v2_google_pay": "assets/icons/payment/google-pay-mark.png",
    "paypal": "assets/icons/payment/paypal.svg",
    "stripe": "assets/icons/payment/stripe.svg",
    "razorpay": "assets/icons/payment/razorpay.svg",
    "tap": "assets/icons/payment/tap.png",
    "paystack": "assets/icons/payment/paystack.png",
    "myfatoorah_v2": "assets/icons/payment/myfatoorah.png",
    "midtrans": "assets/icons/payment/midtrans.png",
    "xendit_cc": "assets/icons/payment/xendit.png",
    "expresspay_apple_pay": "assets/icons/payment/apple-pay-mark.svg",
    "thai-promptpay-easy": "assets/icons/payment/prompt-pay.png",
    "ppcp-gateway": "assets/icons/payment/paypal.svg",
    "thawani_gw": "assets/icons/payment/thawani.png",
  },
  "shopifyPaymentConfig": {
    "shopName": "FluxStore",
    "countryCode": "US",
    "productionMode": false,
    "paymentCardConfig": {
      "enable": true,
      "serverEndpoint": "https://test-stripe-nine.vercel.app",
    },
    "applePayConfig": {
      "enable": true,
      "merchantId": "merchant.com.inspireui.fluxstore",
    },
    "googlePayConfig": {
      "enable": true,
      "stripePublishableKey": "pk_test_O3awus9i5mA2wIX9a7pU3MSi00gZPcpJWX",
      "merchantId": "merchant.com.inspireui.fluxstore"
    },
  },
  "stripeConfig": {
    "serverEndpoint": "https://stripe-server-node.vercel.app",
    "publishableKey": "pk_test_syl720IY4iwLkNzmOeL7nz3J",
    "paymentMethodIds": ["stripe"],
    "enabled": true,
    "enableApplePay": true,
    "enableGooglePay": true,
    "merchantDisplayName": "FluxStore",
    "merchantIdentifier": "merchant.com.inspireui.mstore.flutter",
    "merchantCountryCode": "US",
    "returnUrl": "fluxstore://inspireui.com",

    /// Enable this automatically captures funds when the customer authorizes the payment.
    /// Disable will Place a hold on the funds when the customer authorizes the payment,
    /// but don’t capture the funds until later. (Not all payment methods support this.)
    /// https://stripe.com/docs/payments/capture-later
    /// Default: false
    "enableManualCapture": false,
    "saveCardAfterCheckout": false,
    "stripeApiVersion": 3,
  },
  "paypalConfig": {
    "clientId":
        "ASlpjFreiGp3gggRKo6YzXMyGM6-NwndBAQ707k6z3-WkSSMTPDfEFmNmky6dBX00lik8wKdToWiJj5w",
    "secret":
        "ECbFREri7NFj64FI_9WzS6A0Az2DqNLrVokBo0ZBu4enHZKMKOvX45v9Y1NBPKFr6QJv2KaSp5vk5A1G",
    "returnUrl":
        "com.inspireui.fluxstore://paypalpay", // Example: "your.android.package.name:://paypalpay"
    "production": false,
    "paymentMethodId": "paypal", //ppcp-gateway
    "enabled": true,
    "nativeMode": false,
  },
  "paypalExpressConfig": {
    "username": "sb-wea3q30917031_api1.business.example.com",
    "password": "9MN73T4JHTBDY5W7",
    "signature": "A-X91d6dvj07IIDTUn5hM8p8w8LxA-5D.cnvNUgufzpxxf1NNZBYh3kq",
    "paymentAction": "Sale", //Sale, Order, Authorization.
    "production": false,
    "paymentMethodId": "paypal_express",
    "enabled": false,
  },
  "razorpayConfig": {
    "keyId": "rzp_test_SDo2WKBNQXDk5Y",
    "keySecret": "RrgfT3oxbJdaeHSzvuzaJRZf",
    "paymentMethodId": "razorpay",
    "enabled": true
  },
  "tapConfig": {
    "SecretKey": "sk_test_XKokBfNWv6FIYuTMg5sLPjhJ",
    "paymentMethodId": "tap",
    "enabled": true
  },
  "mercadoPagoConfig": {
    "accessToken":
        "TEST-5726912977510261-102413-65873095dc5b0a877969b7f6ffcceee4-613803978",
    "production": false,
    "paymentMethodId": "woo-mercado-pago-basic",
    "enabled": true
  },
  "payTmConfig": {
    "paymentMethodId": "paytm",
    "merchantId": "your-merchant-id",
    "production": false,
    "enabled": true
  },
  "payStackConfig": {
    'paymentMethodId': 'paystack',
    'publicKey': 'pk_test_a1a37615c9ca90dead5dd84dedbb5e476b640a6f',
    'secretKey': 'sk_test_d833fcaa6c02a61a9431d2026046c0517888a4a7',
    'supportedCurrencies': ['ZAR'],
    'enableMobileMoney': true,
    'production': false,
    'enabled': true
  },
  "flutterwaveConfig": {
    'paymentMethodId': 'rave',
    'publicKey': 'FLWPUBK_TEST-72b90e0734da8c9e43916adf63cd711e-X',
    'production': false,
    'enabled': true
  },
  "myFatoorahConfig": {
    "paymentMethodId": "myfatoorah_v2",
    "apiToken":
        "rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL",
    'accountCountry': 'KW',
    // KW (KUWAIT), SA (SAUDI_ARABIA), BH (BAHRAIN), AR (UNITED_ARAB_EMIRATES), QA (QATAR), OM (OMAN), JO (JORDAN), EG (EGYPT)
    "production": false,
    "enabled": true
  },
  "midtransConfig": {
    'paymentMethodId': 'midtrans',
    'clientKey': 'SB-Mid-client-he8W_FIlvugfA2RD',
    'enabled': true
  },
  "inAppPurchaseConfig": {
    'consumableProductIDs': [
      'com.inspireui.fluxstore.test',
    ],
    'nonConsumableProductIDs': [],
    'subscriptionProductIDs': [
      'com.inspireui.fluxstore.subscription.test',
    ],
    "enabled": false
  },
  "xenditConfig": {
    'paymentMethodId': 'xendit',
    'secretApiKey':
        'xnd_development_4E9ql5zFiC1BBmhK2r7wr9mNYyyvjLs0fIal00tGuHEj1iEYCu7B7tCUudv3Xe',
    'enabled': true
  },
  "expressPayConfig": {
    'paymentMethodId': 'shahbandrpay',
    'merchantKey': 'b2be2ffc-c8b9-11ed-82a9-42eb4e39c8ae',
    'merchantPassword': '4a00a5fd3c63dd2b743c75746af6ffe2',
    "merchantId": "merchant.com.inspireui.mstore.flutter",
    "production": false,
    'enabled': true
  },
  "thaiPromptPayConfig": {
    'paymentMethodId': 'thai-promptpay-easy',
    'enabled': true
  },
  "fibConfig": {
    'paymentMethodId': 'fib',
    'clientId': 'narin-beauty',
    'clientSecret': '7ffcd642-87b7-4cc0-b75d-c25d5276cffe',
    'enabled': false
  },
  "thawaniConfig": {
    'paymentMethodId': 'thawani_gw',
    'secretKey': 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et',
    'publishableKey': 'HGvTMLDssJghr9tlN9gr4DVYt0qyBy',
    'production': false,
    'enabled': true
  },

  /// Ref: https://support.inspireui.com/help-center/articles/35/37/120/multi-shipping-countries-and-states
  "defaultCountryShipping": [],

  /// Ref: https://support.inspireui.com/help-center/articles/35/37/169/aftership
  "afterShip": {
    "api": "e2e9bae8-ee39-46a9-a084-781d0139274f",
    "tracking_url": "https://fluxstore.aftership.com"
  },

  /// Ref: https://support.inspireui.com/help-center/articles/3/25/16/google-map-address
  "googleApiKey": {
    'android': 'AIzaSyDSNYVC-8DU9BTcyqkeN9c5pgVhwOBAvGg',
    'ios': 'AIzaSyDSNYVC-8DU9BTcyqkeN9c5pgVhwOBAvGg',
    'web': 'AIzaSyDSNYVC-8DU9BTcyqkeN9c5pgVhwOBAvGg'
  },

  "productCard": {"defaultImage": 'assets/images/no_product_image.png'},

  /// ➡️ lib/common/products.dart
  "productDetail": {
    "height": 0.6,
    "marginTop": 0,
    "safeArea": false,
    "showVideo": true,
    "showBrand": true,
    "showThumbnailAtLeast": 1,
    "borderRadius": 3.0,

    /// current support "simpleType", "fullSizeImageType", "halfSizeImageType" &  "flatStyle"
    /// Note:
    /// - With "flatStyle", the only buyButtonStyle supported is autoHideShow.
    /// In contrast, buyButtonStyle's autoHideShow only supports "flatStyle"
    /// - flatStyle is only support product is variant and simple
    "layout": "flatStyle",

    /// Support "fixedBottom", "autoHideShow", "normal";
    /// Note: With "layout" is "flatStyle", the only "buyButtonStyle" supported is "autoHideShow".
    /// In contrast, buyButtonStyle's autoHideShow only supports "flatStyle"
    "buyButtonStyle": "normal",

    /// Support "normal" and "inline"
    "attributeLayout": "inline",

    /// Enable this to show selected image variant in the top banner.
    "ShowSelectedImageVariant": true,

    "autoPlayGallery": false,
    "SliderShowGoBackButton": true,
    "ShowImageGallery": true,

    /// "SliderIndicatorType" can be "number", "dot". Default: "number".
    "SliderIndicatorType": 'number',

    /// Enable this to add a white background to top banner for transparent product image.
    "ForceWhiteBackground": false,

    /// Auto select first attribute of variable product if there is no default attribute.
    "AutoSelectFirstAttribute": true,

    /// Enable this to show review in product description.
    "enableReview": true,
    "attributeImagesSize": 50.0,
    "showSku": true,
    "showStockQuantity": true,
    "showRating": true,
    "showProductCategories": true,
    "showProductTags": true,
    "hideInvalidAttributes": false,

    /// Enable this to show a quantity selector in product list.
    "showQuantityInList": false,

    /// Enable this to show Add to cart icon in search result list.
    "showAddToCartInSearchResult": true,

    /// Increase this number if you have yellow layout overflow error in product list.
    /// Should check "RatioProductImage" before changing this number.
    "productListItemHeight": 125,

    /// Limit the time a user can make an appointment. Units are in days.
    /// If the value is not set, there will be no limit on the appointment date.
    /// For example:
    ///  Today is October 11, 2020 and limitDayBooking is 7 days.
    /// --> So users can only book appointments from October 11, 2020 to October 18, 2020
    "limitDayBooking": 14,

    // Hide or show related products in product detail screen.
    "showRelatedProductFromSameStore": true,
    "showRelatedProduct": true,
    "showRecentProduct": true,

    // Product image layout
    "productImageLayout": "page",

    "expandBrands": true,
    "expandSizeGuide": true,
    "expandDescription": true,
    "expandInfors": true,
    "expandCategories": true,
    "expandTags": true,
    "expandReviews": true,
    "expandTaxonomies": true,
    "expandListingMenu": true,
    "expandMap": true,

    /// Buy now button will be fixed at the bottom of the screen if true
    "fixedBuyButtonToBottom": false,

    /// Set true by default, the new UX always displays the `Buy now` and `Add
    /// to cart` button on the product detail page. In case the product is out
    /// of stock or not available, it will still be displayed but will be
    /// disabled. If set false, there is only a `Unavailable` or `Out of stock`
    /// button on the product detail page as old UX does.
    "alwaysShowBuyButton": true,

    /// Only for Fluxstore Listing
    "showListCategoriesInTitle": true,
    "showSocialLinks": true,
    "expandOpeningHours": true,
  },
  "blogDetail": {
    'showComment': true,
    'showHeart': true,
    'showSharing': true,
    'showTextAdjustment': true,
    'enableAudioSupport': false,
    'showRelatedBlog': true,
    'showAuthorInfo': true
  },
  "productVariantLayout": {
    "color": "color",
    "size": "box",
    "height": "option",
    "color-image": "image"
  },
  "productAddons": {
    /// Set the allowed file type for file upload.
    /// On iOS will open Photos app.
    "allowImageType": true,
    "allowVideoType": true,

    /// Enable to allow upload files other than image/video.
    /// On iOS will open Files app.
    "allowCustomType": true,

    /// Set allowed file extensions for custom type.
    /// Leave empty ("allowedCustomType": []) to support all extensions.
    "allowedCustomType": ["png", "pdf", "docx"],

    /// NOTE: WordPress might restrict some file types for security purpose.
    /// To allow it, you can add this line to wp-config.php:
    /// define('ALLOW_UNFILTERED_UPLOADS', true);
    /// - which is NOT recommended.
    /// Instead, try to use a plugin like https://wordpress.org/plugins/wp-extra-file-types
    /// to allow custom file types.
    /// Allow selecting multiple files for upload. Default: false.
    "allowMultiple": false,

    /// Set the file size limit (in MB) for upload. Recommended: <15MB.
    "fileUploadSizeLimit": 5.0
  },
  "cartDetail": {
    "minAllowTotalCartValue": 0,
    "maxAllowQuantity": 10,

    /// Cart Style: normal, style01
    "style": "style01"
  },

  /// Translate the product variant by languages
  /// As it could be limited with the REST API when request variant
  "productVariantLanguage": {
    "en": {
      "color": "Color",
      "size": "Size",
      "height": "Height",
      "color-image": "Color"
    },
    "ar": {
      "color": "اللون",
      "size": "بحجم",
      "height": "ارتفاع",
      "color-image": "اللون"
    },
    "vi": {
      "color": "Màu",
      "size": "Kích thước",
      "height": "Chiều Cao",
      "color-image": "Màu"
    }
  },

  /// Exclude these category IDs from the list (e.g., "311,23,208").
  /// Note: Products in these categories will still appear. To hide specific products, use "excludedProductIDs".
  "excludedCategoryIDs": "",

  /// Exclude these product IDs from the list, e.g., "36920,35508,31893"
  "excludedProductIDs": "",

  "saleOffProduct": {
    /// Show Count Down for product type SaleOff
    "ShowCountDown": true,
    "HideEmptySaleOffLayout": false,
    "Color": "#C7222B"
  },

  /// This is strict mode option to check the `visible` option from product variant
  /// https://tppr.me/4DJJs - default value is false
  "notStrictVisibleVariant": true,

  /// ➡️ lib/common/smartchat.dart
  "configChat": {
    "EnableSmartChat": true,
    "enableVendorChat": true,
    "showOnScreens": ["profile"],
    "hideOnScreens": [],
    "version": "2",
    "realtimeChatConfig": {
      "enable": true,
      "adminEmail": "admininspireui@gmail.com",
      "adminName": "InspireUI",
      "userCanDeleteChat": false,
      "userCanBlockAnotherUser": false,
      "adminCanAccessAllChatRooms": false,
    },
  },
  "openAIConfig": {
    'enableChat': true,
    'supabaseUrl': 'https://rtkrqvtslujdzjxhjocu.supabase.co',
    'supabaseAnonKey':
        'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJ0a3JxdnRzbHVqZHpqeGhqb2N1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzU5OTI5MzMsImV4cCI6MTk5MTU2ODkzM30.qKtfNHhL6AKqGsmDfjMq90bIWIWlnN3UVgnwcLF_vGY',
    'revenueAppleApiKey': 'appl_XNtOUZPHwUzelbvwdSezFsMrNeT',
    'revenueGoogleApiKey': 'goog_kpDTQdItiHkSrdjDdvLIwAdjOzG',
    'revenueProductsIos': [
      'gpt_3999_1y_1w0',
      'gpt_399_1m_1w0',
    ],
    'revenueProductsAndroid': [
      'gpt_pro_v1',
    ],
    'enableSubscription': false,
    'enableInputKey': false,
  },

  /// config for the chat app
  /// config Whatapp: https://faq.whatsapp.com/en/iphone/23559013
  "smartChat": [
    {
      "app": "firebase",
      "imageData":
          "https://trello.com/1/cards/611a38c89ebde41ec7cf10e2/attachments/611a392cceb1b534aa92a83e/previews/611a392dceb1b534aa92a84d/download",
      "description": "Realtime Chat",
    },
    {
      "app": "chatGPT",
      "imageData": "https://i.imgur.com/pp1qlPd.png",
      "description": "Chat GPT"
    },
    {
      "app": "https://wa.me/849908854",
      "iconData": "whatsapp",
      "description": "WhatsApp"
    },
    {"app": "tel:8499999999", "iconData": "phone", "description": "Call Us"},
    {"app": "sms://8499999999", "iconData": "sms", "description": "Send SMS"},
    {
      "app": "https://tawk.to/chat/5d830419c22bdd393bb69888/default",
      "iconData": "whatsapp",
      "description": "Tawk Chat"
    },
    {
      "app": "http://m.me/inspireui",
      "iconData": "facebookMessenger",
      "description": "Facebook Chat"
    },
    {
      "app":
          "https://twitter.com/messages/compose?recipient_id=821597032011931648",
      "imageData":
          "https://trello.com/1/cards/611a38c89ebde41ec7cf10e2/attachments/611a38d026894f10dc1091c8/previews/611a38d126894f10dc1091d6/download",
      "description": "Twitter Chat"
    }
  ],

  /// ➡️ lib/common/vendor.dart
  "vendorConfig": {
    /// Show Register by Vendor
    "VendorRegister": true,

    /// Disable show shipping methods by vendor
    "DisableVendorShipping": false,

    /// Enable/Disable showing all vendor markers on Map screen
    "ShowAllVendorMarkers": true,

    /// Enable/Disable native store management
    "DisableNativeStoreManagement": true,

    /// Dokan Vendor Dashboard
    "dokan": "my-account?vendor_admin=true",
    "wcfm": "store-manager?vendor_admin=true",

    /// Disable multivendor checkout
    "DisableMultiVendorCheckout": false,

    /// If this is false, then when creating/modifying products in FluxStore Manager
    /// The publish status will be removed.
    "DisablePendingProduct": false,

    /// Default status when Add New Product from MV app.
    /// Support 'draft', 'pending', 'publish'.
    "NewProductStatus": "draft",

    /// Default Vendor image.
    "DefaultStoreImage": "assets/images/default-store-banner.png",

    /// Set this to true to automatically approve the vendor application.
    /// When it is set to false, these are the cases:
    /// - For WCFM - It will set the registered role to subscribe with the meta "wcfm_membership_application_status": "pending".
    /// - For Dokan - It still keeps the registered role as "seller" but the selling capability will be set to false. The meta for it is "dokan_enable_selling": "no"
    "EnableAutoApplicationApproval": false,

    "BannerFit": "cover",
    "ExpandStoreLocationByDefault": true,

    /// Enable/Disable native delivery boy management
    "DisableDeliveryManagement": true,

    /// Show/Hide store contact info in Vendor detail screen
    "HideStoreContactInfo": false
  },

  /// Enable Delivery Boy Management in FluxStore Manager(WCFM)
  "deliveryConfig": {
    'appLogo': 'assets/images/app_icon_transparent.png',
    'appName': 'FluxStore Delivery',
    'dashboardName1': 'FluxStore',
    'dashboardName2': 'Delivery',
    'enableSystemNotes': false,
  },

  /// Enable Vendor Admin in FluxStore manager
  "managerConfig": {
    'appLogo': 'assets/images/app_icon_transparent.png',
    'appName': 'FluxStore Admin',
    'enableDeliveryFeature': false,
  },

  /// ➡️ lib/common/loading.dart
  "loadingIcon": {"size": 30.0, "type": "fadingCube"},
  "splashScreen": {
    "enable": true,

    /// duration in milliseconds, used for all types except "rive" and "flare"
    "duration": 2000,

    ///  Type should be: 'fade-in', 'zoom-in', 'zoom-out', 'top-down', 'rive', 'flare', ''static'
    "type": "flare",
    "image": "assets/images/splashscreen.flr",

    /// AnimationName's is used for 'rive' and 'flare' type
    "animationName": "fluxstore",

    "boxFit": "contain",
    "backgroundColor": "#ffffff",
    "paddingTop": 0,
    "paddingBottom": 0,
    "paddingLeft": 0,
    "paddingRight": 0,
  },
  "reviewConfig": {
    "service": "native",
    "enableReview": true,
    "enableReviewImage": true,
    "maxImage": 5,
    "judgeConfig": {
      "domain": "https://inspireui-mstore.myshopify.com",
      "apiKey":
          "8b0d5f99732ec01d6f6b64891166e4fe4ba9634a83fe57e14edda11489da0f7e",
    }
  },
  "orderConfig": {
    "version": 1,
  }
};
