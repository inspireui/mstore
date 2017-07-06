# Demo Wordpress website for MStore and BeoNews app
[![N|Solid](http://news.inspireui.com/wp-content/uploads/2017/06/powerbuy-1.png)](http://inspireUI.com)

### Installation
-   Extract the database file and change the wp-config.php to your database connection.
-   Setup your virtual host and change the app config to this Wordpress site.
-   Account login: root / 12345678 or user / 12345678

- ##### BeoNews app
    - Update file *App/Common/Config.js* to change Wordpress URL.

-   ##### MStore app:
    -   Update file *src/common/Constants.js* to change new Wordpress URL or use the config on this respo: https://github.com/inspireui/mstore/blob/master/Constants.js
    -   Edit the from Omni.js file to config the image size for product:
    - ```sh
        const ThumbnailSizes = {
            CatalogImages: {
                width: 300,
                height: 300,
            },
            SingleProductImage: {
                width: 600,
                height: 600,
            },
            ProductThumbnails: {
                width: 180,
                height: 180,
            },
        };
        ```
### Support
Please post the issue ticket with your Envato username and purchase code to ask for installing help or troubleshooting from InspireUI support team.
        
Thank you very much!


