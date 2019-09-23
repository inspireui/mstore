<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WOOCS_STATISTIC {

    private $table = 'woocs_statistic';

    public function __construct() {

        if (!$this->can_collect()) {
            return;
        }

        //***

        global $wpdb;
        $this->table = $wpdb->prefix . $this->table;
        
        add_action('admin_print_scripts', function() {
            if (isset($_GET['page']) AND isset($_GET['tab'])) {
                if ($_GET['page'] == 'wc-settings' AND $_GET['tab'] == 'woocs') {
                    wp_dequeue_script('iris');
                    wp_enqueue_script('woocs-stat-google-chart-lib', WOOCS_LINK . 'js/chart.min.js', array('jquery'), WOOCS_VERSION);
                    wp_enqueue_script('woocs-stat-google-charts', WOOCS_LINK . 'js/statistic.js', array('woocs-stat-google-chart-lib'), WOOCS_VERSION);
                }
            }
        }, 9);
        
        add_action('admin_head', function() {
            if (isset($_GET['page']) AND isset($_GET['tab'])) {
                if ($_GET['page'] == 'wc-settings' AND $_GET['tab'] == 'woocs') {

                    wp_enqueue_script('jquery');
                    wp_enqueue_script('jquery-ui-datepicker', array('jquery'), WOOCS_VERSION);

                    wp_enqueue_style('jquery-ui-190', WOOCS_LINK . 'css/jquery-ui.css', false, "1.9.0", false);

                    //***

                    wp_dequeue_script('iris'); //as it in conflict with chart.min.js

                }
            }
        }, 999);

        //***

        add_action('wp_ajax_woocs_stat_redraw', function() {
            $scenario = intval($_REQUEST['scenario']);
            $tmp = $this->get(sanitize_key($_REQUEST['type']), $scenario, intval($_REQUEST['time_from']), intval($_REQUEST['time_to']));

            //***

            $res = [
                'stat_label' => $this->get_label($scenario),
                'stat_labels' => array_keys($tmp),
                'stat_data' => array_values($tmp)
            ];

            die(json_encode($res));
        });
    }

    public function can_collect() {
        return get_option('woocs_collect_statistic', 0);
    }

    public function register_switch($currency, $country) {

        if (!$this->can_collect()) {
            return;
        }

        //***

        if (empty($country)) {
            return;
        }

        //***

        static $lock = false;

        if ($lock) {
            return;
        }

        $lock = true;
        global $wpdb;
        $wpdb->insert($this->table, array(
            'currency' => $currency,
            'country' => $country,
            'intime' => current_time('timestamp')
        ));
    }

    //Currencies popularity
    public function get($type, $scenario, $time_from = 0, $time_to = 0) {
        global $wpdb;
        $res = [];

        switch ($type) {
            case 'order':

                $args = array(
                    'post_type' => 'shop_order',
                    'post_status' => 'wc-completed',
                    'fields' => 'ids',
                    'posts_per_page' => -1
                );

                //***

                if ($time_from > 0) {
                    $date_query = [
                        'column' => 'post_date',
                        'after' => date('Y-m-d', $time_from)
                    ];
                }

                if ($time_to > 0) {
                    if (empty($date_query)) {
                        $date_query = [
                            'column' => 'post_date',
                            'before' => date('Y-m-d', $time_to)
                        ];
                    } else {
                        $date_query['before'] - date('Y-m-d', $time_to);
                    }
                }

                if (!empty($date_query)) {
                    $args['date_query'] = $date_query;
                }

                //***

                $query = new WP_Query($args);

                if (!empty($query->posts)) {
                    $ids = implode(',', $query->posts);
                    switch ($scenario) {
                        case 1:
                            $sql = "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key='_order_currency' AND post_id IN($ids)";
                            $rows = $wpdb->get_results($sql, ARRAY_A);


                            if (!empty($rows)) {
                                foreach ($rows as $value) {
                                    if (!isset($res[$value['meta_value']])) {
                                        $res[$value['meta_value']] = 0;
                                    }
                                    $res[$value['meta_value']] += 1;
                                }
                            }
                            break;

                        case 2:
                            $countries = [];

                            if (class_exists('WC_Geolocation')) {
                                $cc = new WC_Countries();
                                $countries = $cc->get_countries();
                            }

                            $sql = "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key='_billing_country' AND post_id IN($ids)";
                            $rows = $wpdb->get_results($sql, ARRAY_A);

                            if (!empty($rows)) {
                                foreach ($rows as $value) {

                                    if (empty($value['meta_value'])) {
                                        continue;
                                    }

                                    $country = $value['meta_value'];

                                    if (isset($countries[$country])) {
                                        $country = $countries[$country];
                                    }

                                    if (!isset($res[$country])) {
                                        $res[$country] = 0;
                                    }

                                    $res[$country] += 1;
                                }
                            }

                            break;
                    }
                }

                break;

            default:

                $sql = "SELECT * FROM {$this->table} WHERE 1=1";
                if ($time_from > 0) {
                    $sql .= " AND intime >= {$time_from}";
                }

                if ($time_to > 0) {
                    $sql .= " AND intime <= {$time_to}";
                }

                //***

                $rows = $wpdb->get_results($sql, ARRAY_A);

                //***
                if (!empty($rows)) {
                    switch ($scenario) {
                        case 1:

                            foreach ($rows as $value) {
                                if (!isset($res[$value['currency']])) {
                                    $res[$value['currency']] = 0;
                                }
                                $res[$value['currency']] += 1;
                            }

                            break;

                        case 2:
                            $countries = [];

                            if (class_exists('WC_Geolocation')) {
                                $cc = new WC_Countries();
                                $countries = $cc->get_countries();
                            }

                            foreach ($rows as $value) {

                                if (empty($value['country'])) {
                                    continue;
                                }

                                $country = $value['country'];

                                if (isset($countries[$country])) {
                                    $country = $countries[$country];
                                }

                                if (!isset($res[$country])) {
                                    $res[$country] = 0;
                                }

                                $res[$country] += 1;
                            }

                            break;
                    }
                }


                break;
        }

        //*** sort it from max to min
        asort($res);
        $res= array_reverse($res);

        return $res;
    }

    public function get_label($scenario) {
        $label = '';

        switch ($scenario) {
            case 1:
                $label = __('Currencies popularity', 'woocommerce-currency-switcher');
                break;
            case 2:
                $label = __('Countries popularity', 'woocommerce-currency-switcher');
                break;
        }

        return $label;
    }

    public function get_min_date() {
        global $wpdb;
        $res = $wpdb->get_results("SELECT intime FROM {$this->table} WHERE id=1", ARRAY_A);

        if (isset($res[0]['intime'])) {
            return intval($res[0]['intime']);
        }

        return 0;
    }

    public function install_table() {
        global $wpdb;
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table}'") != $this->table) {
            $sql1 = "CREATE TABLE `{$this->table}` (
            `id` int(11) NOT NULL,
            `currency` varchar(8) NOT NULL,
            `country` varchar(8) DEFAULT NULL,
            `intime` int(12) DEFAULT NULL);";
            $wpdb->query($sql1);

            //***

            $sql2 = "ALTER TABLE `{$this->table}` ADD PRIMARY KEY (`id`), ADD KEY `currency` (`currency`);";
            $wpdb->query($sql2);

            //***

            $sql3 = "ALTER TABLE `{$this->table}` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
            $wpdb->query($sql3);
        }
    }

}
