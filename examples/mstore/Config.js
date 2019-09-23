import Images from "./Images"
import Constants from './Constants'

export default {
  /**
   Step 1: change to your website URL and the wooCommerce API consumeKey
   */
  WooCommerce: {
    url: 'http://mstore.local',
    consumerKey: 'ck_98f9ca71c82ec652ac27194eafef4a9cf2af300a',
    consumerSecret: 'cs_83d385c0711ace08304126f48618d7a9aa7ff663',
  },

  /**
   Step 2: Setting Product Images
   - ProductSize: Explode the guide from: update the product display size: https://mstore.gitbooks.io/mstore-manual/content/chapter5.html
   - HorizonLayout: Change the HomePage horizontal layout - https://mstore.gitbooks.io/mstore-manual/content/chapter6.html
   */
  ProductSize: {
    CatalogImages: {width: 348, height: 445},
    SingleProductImage: {width: 568, height: 725},
    ProductThumbnails: {width: 78, height: 99},
  },
  HorizonLayout: [
    {tag: 67, paging: true, layout: Constants.Layout.miniBanner},
    {name: "Feature Products", tag: 18, image: Images.Banner.Feature, layout: Constants.Layout.threeColumn},
    {name: "Bags Collections", category: 57, image: Images.Banner.Bag, layout: Constants.Layout.twoColumn},
    {name: "Woman Best seller", category: 56, image: Images.Banner.Woman, layout: Constants.Layout.twoColumnHigh},
    {name: "Man Collections", category: 52, image: Images.Banner.Man, layout: Constants.Layout.card},
  ],

  /**
   step 3: Config image for the Payment Gateway
   Notes:
   - Only the image list here will be shown on the app but it should match with the key id from the WooCommerce Website config
   - It's flexible way to control list of your payment as well
   Ex. if you would like to show only cod then just put one cod image in the list
   **/
  Payments: {
    cod: require('@images/payment_logo/cash_on_delivery.png'),
    bacs: require('@images/payment_logo/bacs.png'),
    paypal: require('@images/payment_logo/PayPal.png'),
    stripe: require('@images/payment_logo/stripe.png'),
    authorize: require('@images/payment_logo/authorize.png'),
    gourlpayments: require('@images/payment_logo/gourl.png'),
  },

  /**
   Step 4: Advance config:
   - showShipping: option to show the list of shipping method
   - showStatusBar: option to show the status bar, it always show iPhoneX
   - LogoImage: The header logo
   - LogoWithText: The Logo use for sign up form
   - LogoLoading: The loading icon logo
   - appFacebookId: The app facebook ID, use for Facebook login
   - CustomPages: Update the custom page which can be shown from the left side bar (Components/Drawer/index.js)
   - WebPages: This could be the id of your blog post or the full URL which point to any Webpage (responsive mobile is required on the web page)
   **/
  showShipping: true,
  showStatusBar: false,
  LogoImage: require('@images/new_logo.png'),
  LogoWithText: require('@images/logo_with_text.png'),
  LogoLoading: require('@images/logo.png'),
  appFacebookId: '422035778152242',
  CustomPages: {contact_id: 10941},
  WebPages: {marketing: 'http://mstore.io/email-marketing'},
};

