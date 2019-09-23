import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:fstore/common/constants.dart';

/// Server config
const serverConfig = {
  "type": "woo",
  "url": "http://mstore.local",
  "consumerKey": "ck_98f9ca71c82ec652ac27194eafef4a9cf2af300a",
  "consumerSecret": "cs_83d385c0711ace08304126f48618d7a9aa7ff663",
  "blog": "http://mstore.local",
  "forgetPassword": "http://mstore.local/wp-login.php?action=lostpassword"
};

const afterShip = {
  "api": "e2e9bae8-ee39-46a9-a084-781d0139274f",
  "tracking_url": "https://fluxstore.aftership.com"
};

const CategoriesListLayout = kCategoriesLayout.card;

const Payments = {
  "paypal": "assets/icons/payment/paypal.png",
  "stripe": "assets/icons/payment/stripe.png",
  "razorpay": "assets/icons/payment/razorpay.png",
};

/// The product variant config
const ProductVariantLayout = {
  "color": "color",
  "size": "box",
  "height": "option",
};

const kAdvanceConfig = {
  "DefaultCurrency": {
    "symbol": "\$",
    "decimalDigits": 2,
    "symbolBeforeTheNumber": true,
    "currency": "USD"
  },
  "IsRequiredLogin": false,
  "GuestCheckout": true,
  "EnableShipping": true,
  "GridCount": 3,
  "DetailedBlogLayout": kBlogLayout.halfSizeImageType,
  "EnablePointReward": false,
};

/// The Google API Key to support Pick up the Address automatically
/// We recommend to generate both ios and android to restrict by bundle app id
/// The download package is remove these keys, please use your own key
const kGoogleAPIKey = {
  "android": "your-google-api-key",
  "ios": "your-google-api-key"
};

/// use to config the product image height for the product detail
/// height=(percent * width-screen)
/// isHero: support hero animate
const kProductDetail = {
  "height": 0.5,
  "marginTop": 0,
  "isHero": false,
  "safeArea": false,
  "showVideo": true,
  "showThumbnailAtLeast": 3
};

/// config for the chat app
const smartChat = [
  {
    'app': 'whatsapp://send?phone=84327433006',
    'iconData': FontAwesomeIcons.whatsapp
  },
  {'app': 'tel:8499999999', 'iconData': FontAwesomeIcons.phone},
  {'app': 'sms://8499999999', 'iconData': FontAwesomeIcons.sms},
  {'app': 'firebase', 'iconData': FontAwesomeIcons.google},
  {'app': 'intercome', 'iconData': FontAwesomeIcons.intercom},
];
const String adminEmail = "admininspireui@gmail.com";
const kIntercomAPIKey = {
  'android': 'android_sdk-2c16c0e017a1e7b8d3b73b5a13a56b54cbf535c0',
  'ios': 'ios_sdk-33135e6653b055cec773b7903baff10efee94bc0',
  'appID': 'xro9xnfd'
};

/// the welcome screen data
List onBoardingData = [
  {
    "title": "Welcome to FluxStore",
    "image": "assets/images/fogg-delivery-1.png",
    "desc": "Fluxstore is on the way to serve you. "
  },
  {
    "title": "Connect Surrounding World",
    "image": "assets/images/fogg-uploading-1.png",
    "desc":
    "See all things happening around you just by a click in your phone. "
        "Fast, convenient and clean."
  },
  {
    "title": "Let's Get Started",
    "image": "fogg-order-completed.png",
    "desc": "Waiting no more, let's see what we get!"
  },
];

const PaypalConfig = {
  "clientId":
  "AXFWJm_35Cn29hM6KMJRAbPltKYQcoI7GjzKbTi_v5cV-BQLcL8SbruAUecsn5CA8ryAhri8ubg1nOwn",
  "secret":
  "EK-dzS13YxV4STnJp7NpyIS9eG2JacspgXgmCHtoexYeZfLTNCMxIqefH5H29pe_hJ4uwlW776IvjfVH",
  "returnUrl": "http://return.example.com",
  "cancelUrl": "http://cancel.example.com",
  "production": false,
  "paymentMethodId": "paypal",
  "enabled": true
};

const RazorpayConfig = {
  "keyId": "rzp_test_WHBBYP8YoqmqwB",
  "callbackUrl": "http://example.com",
  "paymentMethodId": "razorpay",
  "enabled": true
};

const TapConfig = {
  "SecretKey": "sk_test_XKokBfNWv6FIYuTMg5sLPjhJ",
  "RedirectUrl": "http://your_website.com/redirect_url",
  "paymentMethodId": "",
  "enabled": false
};

/// Example categories:
/// {23: Category { id: 23  name: Bags}, 24: Category { id: 24  name: Bags},
/// 25: Category { id: 25  name: Blazers}, 208: Category { id: 208  name: Clothing},
/// 26: Category { id: 26  name: Dresses}, 209: Category { id: 209  name: Hoodies},
/// 27: Category { id: 27  name: Jackets}, 28: Category { id: 28  name: Jackets},
/// 29: Category { id: 29  name: Jeans}, 30: Category { id: 30  name: Jeans},
/// 18: Category { id: 18  name: Men}, 210: Category { id: 210  name: Music},
/// 211: Category { id: 211  name: Posters}, 19: Category { id: 19  name: Shirts},
/// 20: Category { id: 20  name: Shoes}, 212: Category { id: 212  name: Singles},
/// 21: Category { id: 21  name: T-Shirts}, 22: Category { id: 22  name: Women}}
