/**
 * Created by InspireUI on 20/12/2016.
 */

import {Dimensions} from 'react-native';
// const {height, width} = Dimensions.get('screen');
const {width, height} = Dimensions.get("window");

const Constants = {
  RTL : false,
  fontFamily: 'opensan',
  fontHeader: 'baloo',
  fontHeaderAndroid: 'baloo',
  WordPress: {
    // url: 'http://mstore.io',
    url: 'http://mstore.local',
    defaultDateFormat: 'YYYY-MM-DD HH:mm:ss',
  },
  WooCommerce: {
    // consumerKey: 'ck_b7594bc4391db4b56c635fe6da1072a53ca4535a',
    // consumerSecret: 'cs_980b9edb120e15bd2a8b668cacc734f7eca0ba40',
    consumerKey: 'ck_0d206d20dc7eb7fc3a30c875800abc68c3443ac8',
    consumerSecret: 'cs_60cad0a9178913ad6ba045d84e1c32c421c05d1d'
  },
  SplashScreen: {
    Duration: 2000,
  },
  AsyncCode: {
    Intro: 'async.intro',
  },
  EmitCode: {
    SideMenuOpen: 'emit.side.open',
    SideMenuClose: 'emit.side.close',
    Toast: 'toast',
  },
  Dimension: {
    ScreenWidth(percent = 1) {
      return Dimensions.get('window').width * percent
    },
    ScreenHeight(percent = 1) {
      return Dimensions.get('window').height * percent
    },
  },
  LimitAddToCart:10,
  TagIdForProductsInMainCategory:263,
  Window: {
    width: width,
    height: height,
    headerHeight: 65 * height / 100,
    headerBannerAndroid: 55 * height / 100,
    profileHeight: 45 * height / 100
  },

  PostImage: {
    small: 'small',
    medium: 'medium',
    medium_large: 'medium_large',
    large: 'large',
  },
  tagIdBanner: 18, // 273, // cat ID for Sticky Products
  stickyPost: true, // default is true (else false)
  PostList: {  // Custom get All Products in Home Screen
    order: 'desc', // or asc - default is "desc" column
    orderby: 'date', // date, id, title and slug - default is "date" column
  },
  Layout: {
    card: 1,
    twoColumn: 2,
    simple: 3,
    list: 4,
    advance: 5,
    threeColumn: 6
  },
  pagingLimit: 20,
  appFacebookId: '422035778152242',
  fontText: {
    size: 16
  },
  PayPal: {
      clientID: 'ATeT4ckTzYyxo8IQ9n-d4JOmJX9c-gJqqW9CKKKhN45lHow40SdGtKNpQKg2ASnkGsYTxh83GK6wAlBh',
      secretKey: 'EHLLoxewn3KhndDE3SzgdgJ6KGCIcGJzGEWgZJDQ7r8Qt4OmneaT5Dq6lyfPhxGDVRZNCubPsAsdbOml',
      sandBoxMode: true,
  },
  Stripe: {
    publishableKey: 'pk_test_MOl5vYzj1GiFnRsqpAIHxZJl',
    name: "MStore Payment",
    // you need to reply by your server side URL node URL
    serverURL: "http://localhost:8080"
  },
  CustomPages: {
    aboutus_id: 10939,
    contact_id: 11003,
  },


};

export default Constants;
