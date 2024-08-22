<?php
/**
 * Add custom ( page and post type ) templates with plugins.
 *
 * Here i improved the @source class 'PageTemplater':
 * - handle class methods and added new functionality.
 * - you can now define your 'Templater' $settings and $templates outside the class.
 * - you can override final custom template file using filter outside the class
 *   @see  Templater.php#L386 or see details in README.md
 * - create composer package.
 * - support any post type cusom template.
 *
 * - new updated copyright:
 * @package   Templater
 * @author    mohamdio [jozoor.com]
 * @link      https://github.com/mohamdio/wp-templater
 * @copyright 2017 mohamdio [jozoor.com]
 * @license   GPL-2.0+
 * @version   1.0.0
 *
 * - source copyright:
 * @package   page-templater
 * @author    wpexplorer
 * @link      https://github.com/wpexplorer/page-templater
 * @copyright 2017 wpexplorer
 * @license   GPL-2.0+
 * @version   1.1.0
 *
 * @since  1.0.0
 */
class Templater
{

    /**
     * Reference to the root directory path of this plugin.
     *
     * for example: YOUR_PLUGIN_DIR or plugin_dir_path(__FILE__).
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected $plugin_directory;
    /**
     * Plugin prefix for filter names.
     *
     * for example: 'my_plugin_'
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected $plugin_prefix;
    /**
     * Directory name where templates are found in the plugin.
     *
     * for example: 'templates' or 'includes/templates'.
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected $p_template_directory;
    /**
     * The array of templates that this plugin tracks.
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $templates;

    /**
     * Setup templater.
     *
     * $settings = array(
     *     'plugin_directory'          => plugin_dir_path(__FILE__),
     *     'plugin_prefix'             => 'plugin_prefix_',
     *     'p_template_directory' => 'templates', // or 'templates/sub-folder'
     * );
     *
     * @since 1.0.0
     * @access public
     * @param array $settings
     */
    public function __construct($settings = array())
    {

        // set templater settings
        if (!empty($settings) || is_array($settings)) {

            // set plugin directory
            if (isset($settings['plugin_directory'])) {
                $this->plugin_directory = $settings['plugin_directory'];
            }

            // set plugin prefix
            if (isset($settings['plugin_prefix'])) {
                $this->plugin_prefix = $settings['plugin_prefix'];
            }

            // set plugin template directory
            if (isset($settings['p_template_directory'])) {
                $this->p_template_directory = $settings['p_template_directory'];
            }

        }

    }

    /**
     * Add and set our templates.
     *
     * $templates = array(
     *     'post_type' => array(
     *         'template_file.php' => 'template_name',
     *         'sub-folder/template_file.php' => 'template_name',
     *     ),
     * );
     *
     * @since 1.0.0
     * @access public
     * @param array $templates
     * @return self
     */
    public function add($templates = array())
    {

        // get current WP version
        global $wp_version;

        // return the object if something wrong
        if (!is_array($templates)) {
            return $this;
        }

        /**
         * Handle our new templates.
         */

        // save new templates
        $new_templates = array();

        // handle templates for WP version 4.6 and older
        if (version_compare($wp_version, '4.7', '<')) {

            foreach ($templates as $custom_templates) {

                if (!empty($custom_templates) && is_array($custom_templates)) {

                    // merge all post types templates
                    foreach ($custom_templates as $template_file => $template_name) {
                        $new_templates[$template_file] = $template_name;
                    }

                }

            } // end foreach $templates

        } else {
            // handle templates for WP version 4.7 and later

            // pass array as normal
            $new_templates = $templates;

        } // end check WP version

        // set our new templates
        $this->templates = $new_templates;

        // return the object
        return $this;

    }

    /**
     * Register all our new templates.
     *
     * now we actually will add all this new templates.
     *
     * @since 1.0.0
     * @access public
     */
    public function register()
    {

        // get current WP version
        global $wp_version;

        /**
         * Add a filter to the attributes metabox to inject template into the cache.
         */

        // for WP version 4.6 and older
        if (version_compare($wp_version, '4.7', '<')) {

            add_filter(
                'page_attributes_dropdown_pages_args', array($this, 'register_templates')
            );

        } else {
            // for WP version 4.7 and later

            // add filter per post type
            foreach (array_keys($this->templates) as $post_type) {
                add_filter(
                    'theme_' . $post_type . '_templates', array($this, 'add_new_template')
                );

            }

        } // end check WP version

        /**
         * Add a filter to the save post to inject out template into the page cache.
         */
        add_filter(
            'wp_insert_post_data', array($this, 'register_templates')
        );

        /**
         * Add a filter to the template include to determine if the page
         * has our template assigned and return it's path.
         */
        add_filter(
            'template_include', array($this, 'view_template')
        );

    }

    /**
     * Adds our template to the page dropdown for v4.7+.
     *
     * @since 1.0.0
     * @access public
     * @param array $posts_templates
     * @return array
     */
    public function add_new_template($posts_templates)
    {

        // get new templates per post type
        $new_templates = array();
        foreach ($this->templates as $post_type => $custom_templates) {

            // we are in exact post type?
            if ($post_type === get_post_type()) {
                $new_templates = $custom_templates;
            }

        }

        // merge our new templates with default templates
        $posts_templates = array_merge($posts_templates, $new_templates);

        // return default with new templates
        return $posts_templates;

    }

    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     *
     * @since 1.0.0
     * @access public
     * @param  aray $atts attributes metabox
     * @return array
     */
    public function register_templates($atts)
    {

        // create the key used for the themes cache
        $cache_key = 'page_templates-' . hash('sha256', get_theme_root() . '/' . get_stylesheet());

        /**
         * Retrieve the cache list.
         * If it doesn't exist, or it's empty prepare an array
         */
        $templates = wp_get_theme()->get_page_templates();
        if (empty($templates)) {
            $templates = array();
        }

        // new cache, therefore remove the old one
        wp_cache_delete($cache_key, 'themes');

        /**
         * Now add our template to the list of templates by merging our templates
         * with the existing templates array from the cache.
         */
        $templates = array_merge($templates, $this->templates);

        /**
         * Add the modified cache to allow WordPress to pick it up for listing
         * available templates
         */
        wp_cache_add($cache_key, $templates, 'themes', 1800);

        return $atts;

    }

    /**
     * Checks if the template is assigned to the page.
     *
     * @since 1.0.0
     * @access public
     * @param  string $template current default template
     * @return string
     */
    public function view_template($template)
    {

        // get current WP version
        global $wp_version;

        /**
         * return the search template if we're searching
         * (instead of the template for the first result)
         */
        if (is_search()) {
            return $template;
        }

        // get global post
        global $post;

        // return template if post is empty
        if (!$post) {
            return $template;
        }

        /**
         * Handle templates array upon wp version.
         */

        // save our new templates
        $new_templates = array();

        // for WP version 4.6 and older
        if (version_compare($wp_version, '4.7', '<')) {

            // pass array as normal
            $new_templates = $this->templates;

        } else {
            // for WP version 4.7 and later

            // add new templates per post type
            foreach ($this->templates as $post_type => $custom_templates) {

                // we are in exact post type?
                if ($post_type === get_post_type()) {
                    $new_templates = $custom_templates;
                }

            }

        } // end check WP version

        // return default template if we don't have a custom one defined
        if (!isset(
            $new_templates[get_post_meta($post->ID, '_wp_page_template', true)])
        ) {
            return $template;
        }

        /**
         * Set our new custom template.
         *
         * we apply_filters() here, so we can override this plugin template in themes,
         * or plugins also by using this filter:
         * add_filter('{plugin_prefix_}override_plugin_custom_template', 'override_plugin_custom_template');
         * and inside this function 'override_plugin_custom_template'
         * you can change any or update any custom template
         *
         * function override_plugin_custom_template($template_file) {}
         *
         * whatever this filter is (optional).
         */
        $plugin_template = apply_filters(
            // filter tag name: {plugin_prefix_}override_plugin_custom_template'
            $this->plugin_prefix . 'override_plugin_custom_template',
            // full path of custom template file: $template_file
            $this->plugin_directory . $this->p_template_directory . '/' . get_post_meta($post->ID, '_wp_page_template', true)
        );

        // our new plugin template exists? use it
        if (WP_Filesystem_Base()->is_file($plugin_template)) {
            return $plugin_template;
        }

        // return default template
        return $template;

    }

}
