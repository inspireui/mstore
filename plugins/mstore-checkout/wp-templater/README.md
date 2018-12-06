# :zap: Templater
> WordPress module to add custom ( page and post type ) templates with plugins.

[![Build Status](https://travis-ci.org/mohamdio/wp-templater.svg?branch=master)](https://travis-ci.org/mohamdio/wp-templater) [![Latest Stable Version](https://poser.pugx.org/jozoor/wp-templater/v/stable)](https://packagist.org/packages/jozoor/wp-templater) [![Total Downloads](https://poser.pugx.org/jozoor/wp-templater/downloads)](https://packagist.org/packages/jozoor/wp-templater) [![PHP from Packagist](https://img.shields.io/packagist/php-v/jozoor/wp-templater.svg)](https://packagist.org/packages/jozoor/wp-templater) [![License](https://poser.pugx.org/jozoor/wp-templater/license)](https://packagist.org/packages/jozoor/wp-templater)

## Installation

#### :heavy_exclamation_mark: Minimum requirements

**PHP:** 5.6

#### :heavy_minus_sign: Install with composer

Run the following in your terminal to install **wp-templater** with [Composer](https://getcomposer.org/).

```
composer require jozoor/wp-templater
```

As **wp-templater** uses [PSR-4](http://www.php-fig.org/psr/psr-4/) autoloading you will need to use Composers autoloader. Below is a basic example of getting started with the class, though your setup maybe different depending on how you are using composer, this example below show you simple usage of create new post type:

```php
require_once __DIR__ . '/vendor/autoload.php';

use JO\Module\Templater\Templater;
```

See Composers [basic usage](https://getcomposer.org/doc/01-basic-usage.md#autoloading) guide for details on working Composer and autoloading.

#### :heavy_minus_sign: Download the module and include it manually

If you don't using composer and want to to include the library manually, you can do that, this example below show you simple usage of create new post type:

```php
/**
 * load main file
 */

require_once __DIR__ . '/wp-templater/src/Templater.php';

use JO\Module\Templater\Templater;
```

## Features

In this module/package i improved the @source class [PageTemplater](https://github.com/wpexplorer/page-templater) created by [wpexplorer](https://github.com/wpexplorer), which i added this features:

- handle class methods and added new functionality.
- you can now define your 'Templater' `$settings` and `$templates` outside the class.
    - @see [Setup Templater](#usage)
- you can override final custom template file using filter outside the class
    - @see [Templater.php](/src/Templater.php#L386)
- create composer package.
- support any post type custom template.

## Usage

```php

use JO\Module\Templater\Templater;

/**
 * How to use Templater
 */

// we should add our new Templater inside action hook
add_action('plugins_loaded', 'load_templater');

function load_templater()
{

    // setup our templater
    $my_templater = new Templater(
        array(
            // YOUR_PLUGIN_DIR or plugin_dir_path(__FILE__)
            'plugin_directory'          => plugin_dir_path(__FILE__),
            // should end with _ > prefix_
            'plugin_prefix'             => 'plugin_prefix_',
            // templates directory inside your plugin
            'plugin_template_directory' => 'templates',
        )
    );


    // add our new custom templates
    $my_templater->add(

        // array of available templates
        array(

            /**
             * default usage:
             * 'post_type_name' => array(
             *      'template_file.php' => 'template_name',
             *      or
             *      'path/to/template_file.php' => 'template_name',
             * ),
             *
             * Note: all this files should be inside your 
             * 'plugin_template_directory' => 'templates',
             * so this is parent directory > 'templates/path/template_file.php'
             */
            
            // add 'post' type custom templates
            'post' => array(
                // just file without any sub folders
                'post-template.php' => 'Post Custom Template',
                // with sub folders
                'path/to/post-template.php' => 'Post Custom Template',
            ),

            // add 'page' type custom templates
            'page' => array(
                // just file without any sub folders
                'page-template.php' => 'Page Custom Template',
                // with sub folders
                'path/to/page-template.php' => 'Page Custom Template',
            ),

            // add 'custom_post_type' type custom templates, for ex: product
            'product' => array(
                // just file without any sub folders
                'product-template.php' => 'Product Custom Template',
                // with sub folders
                'path/to/product-template.php' => 'Product Custom Template',
            ),

            // ..etc

            /**
             * Note: you can name your template file anything you like
             * i mean you shouldn't add post type name in template name, like
             * 'post-template.php' < this just for show you examples
             */

            /**
             * Note: why we separated templates in the top by 'post types' ?
             * because we need this when working on any WP version 4.7 and later
             * which custom templates supported post types, but for WP version 
             * 4.6 and older, all this templates will be merged, because we
             * working on 'page' enough, not like WP version 4.7 and later,
             * which we add templates only for exact post type.
             */

        )

    // here we actually will add all this new templates.
    )->register();

}


/**
 * How to override final custom template file in themes or plugins
 */

add_filter('plugin_prefix_override_plugin_custom_template', 'override_plugin_custom_template');

function override_plugin_custom_template($template_file)
{

    // add another template file here
    $template_file = plugin_dir_path(__FILE__) . 'templates/my_override_template.php';

    // or do whatever .. 

    // return new updated template or default template 
    return $template_file;

}

```


## Author

**Mohamed Abd Elhalim**

- [site](https://mohamd.io/)
- [twitter](https://twitter.com/mohamdio)
