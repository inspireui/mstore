<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package booking
 */

class FlutterBooking extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_booking';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_booking_routes'));
    }

    public function register_flutter_booking_routes()
    {
        register_rest_route($this->namespace, '/get_staffs', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_staffs'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/get_slots', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_slots'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/checkout', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'checkout'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function checkout()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        if (!is_plugin_active('woocommerce-appointments/woocommerce-appointments.php')) {
            return parent::send_invalid_plugin_error("You need to install WooCommerce Appointments plugin to use this api");
        }

        //get order info
        $order = wc_get_order($params["order_id"]);
        if ($order) {
            $order_items = $order->get_items();
            $orderItemId = 0;
            foreach ($order_items as $order_item_id => $order_item) {
                if ($order_item->get_product()->get_id() == $params["product_id"]) {
                    $orderItemId = $order_item_id;
                }
            }
            //create appointment
            $params["add-to-cart"] = $params["product_id"];
            $params["customer_id"] = $order->get_customer_id();
            $params["order_item_id"] = $orderItemId;
            return $this->add_cart_item_data($params, $params["product_id"]);
        } else {
            return parent::sendError("invalid_order", "The order is not found", 400);
        }

    }

    /**
     * Add posted data to the cart item
     *
     * @param mixed $cart_item_meta
     * @param mixed $product_id
     * @return array $cart_item_meta
     */
    private function add_cart_item_data($params, $product_id)
    {
        $cart_item_meta = [];

        $product = wc_get_product($product_id);

        if (!is_wc_appointment_product($product)) {
            return $cart_item_meta;
        }

        $cart_item_meta['appointment'] = wc_appointments_get_posted_data($params, $product);
        $cart_item_meta['appointment']['_cost'] = WC_Appointments_Cost_Calculation::calculate_appointment_cost($params, $product);

        if ($cart_item_meta['appointment']['_cost'] instanceof WP_Error) {
            return parent::sendError("invalid_data", $cart_item_meta['appointment']['_cost']->get_error_message(), 400);
        }

        $cart_item_meta['appointment']["_customer_id"] = $params["customer_id"];
        $cart_item_meta['appointment']["_order_id"] = $params["order_id"];
        $cart_item_meta['appointment']["_order_item_id"] = $params["order_item_id"];
        if ($params["staff_ids"]) {
            if (count($params["staff_ids"]) == 1) {
                $cart_item_meta['appointment']["_staff_id"] = $params["staff_ids"][0];
            }
            if (count($params["staff_ids"]) > 1) {
                $cart_item_meta['appointment']["_staff_ids"] = $params["staff_ids"];
            }
        }

        // Create the new appointment
        $new_appointment = $this->add_appointment_from_cart_data($cart_item_meta, $product_id);

        // Store in cart
        $cart_item_meta['appointment']['_appointment_id'] = $new_appointment->get_id();

        return $cart_item_meta;
    }

    /**
     * Create appointment from cart data
     *
     * @param        $cart_item_meta
     * @param        $product_id
     * @param string $status
     *
     * @return object
     */
    private function add_appointment_from_cart_data($cart_item_meta, $product_id, $status = 'unpaid')
    {
        // Create the new appointment
        $new_appointment_data = array(
            'product_id' => $product_id, // Appointment ID
            'cost' => $cart_item_meta['appointment']['_cost'], // Cost of this appointment
            'start_date' => $cart_item_meta['appointment']['_start_date'],
            'end_date' => $cart_item_meta['appointment']['_end_date'],
            'all_day' => $cart_item_meta['appointment']['_all_day'],
            'qty' => $cart_item_meta['appointment']['_qty'],
            'timezone' => $cart_item_meta['appointment']['_timezone'],
            'customer_id' => $cart_item_meta['appointment']['_customer_id'],
            'order_id' => $cart_item_meta['appointment']['_order_id'],
            'order_item_id' => $cart_item_meta['appointment']['_order_item_id'],
        );

        // Check if the appointment has staff
        if (isset($cart_item_meta['appointment']['_staff_id'])) {
            $new_appointment_data['staff_id'] = $cart_item_meta['appointment']['_staff_id']; // ID of the staff
        }

        // Pass all staff selected
        if (isset($cart_item_meta['appointment']['_staff_ids'])) {
            $new_appointment_data['staff_ids'] = $cart_item_meta['appointment']['_staff_ids']; // IDs of the staff
        }

        $new_appointment = get_wc_appointment($new_appointment_data);
        $new_appointment->create($status);

        return $new_appointment;
    }

    private function findStaffValue($array, $staff_id) {
        foreach ( $array as $element ) {
            if ($element[$staff_id] != null) {
                return $element[$staff_id];
            }
        }
    }

    public function get_staffs($request)
    {
        $product_id = $request["product_id"];
        if (!isset($product_id)) {
            return parent::sendError("invalid_data", "product_id is required", 400);
        }

        $product_id = sanitize_text_field($product_id);
        $results = [];
        global $wpdb;
        $table_name = $wpdb->prefix . "wc_appointment_relationships";
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE product_id = %s",$product_id);
        $items = $wpdb->get_results($sql);
        $qtys = get_post_meta($product_id, '_staff_qtys');
        $costs = get_post_meta($product_id, '_staff_base_costs');
        foreach ($items as $item) {
            $user = get_user_by("ID", $item->staff_id);
            $quantity = null;
            $cost = null;
            if (is_array($qtys)) {
                $quantity = $this->findStaffValue($qtys, $item->staff_id);
            }
            if (is_array($costs)) {
                $cost = $this->findStaffValue($costs, $item->staff_id);
            }
            $results[] = array(
                "id" => $user->ID,
                "username" => $user->user_login,
                "nicename" => $user->user_nicename,
                "email" => $user->user_email,
                "url" => $user->user_url,
                "registered" => $user->user_registered,
                "displayname" => $user->display_name,
                "firstname" => $user->user_firstname,
                "lastname" => $user->last_name,
                "nickname" => $user->nickname,
                "description" => $user->user_description,
                "capabilities" => $user->wp_capabilities,
                "role" => $user->roles,
                "avatar" => get_avatar_url($user->ID),
                "quantity" => $quantity,
                "cost" => $cost,
            );
        }

        return $results;
    }

    public function get_slots($request)
    {
        if (!is_plugin_active('woocommerce-appointments/woocommerce-appointments.php')) {
            return parent::send_invalid_plugin_error("You need to install WooCommerce Appointments plugin to use this api");
        }

        $product_id = $request["product_id"];
        if (!isset($product_id)) {
            return parent::sendError("invalid_data", "product_id is required", 400);
        }
        $params = ["product_ids" => $product_id];
        if (!empty($request['staff_ids'])) {
            $params["staff_ids"] = $request['staff_ids'];
        }
        if (isset($request["date"])) {
            $timezone = new DateTimeZone(wc_appointment_get_timezone_string());
            $params["min_date"] = $request['date'];
            $params["max_date"] = date("Y-m-d", strtotime($request['date'] . " +1 day"));
        }
        $request->set_query_params($params);
        $controller = new WC_Appointments_REST_Slots_Controller();

        $slots = $controller->get_items($request);
        $slots = array_values(array_filter($slots["records"], function ($item) {
            return $item["scheduled"] == 0;
        }));
        return array_values(array_unique(array_map(function ($item) {
            return $item["date"];
        }, $slots)));
    }
}

new FlutterBooking;