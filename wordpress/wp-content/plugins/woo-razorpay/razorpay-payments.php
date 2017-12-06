<?php
/*
 * Plugin Name: Razorpay for WooCommerce
 * Plugin URI: https://razorpay.com
 * Description: Razorpay Payment Gateway Integration for WooCommerce
 * Version: 1.6.2
 * Stable tag: 1.6.2
 * Author: Team Razorpay
 * WC tested up to: 3.2.1
 * Author URI: https://razorpay.com
*/

if ( ! defined( 'ABSPATH' ) )
{
    exit; // Exit if accessed directly
}

require_once __DIR__.'/includes/razorpay-webhook.php';
require_once __DIR__.'/includes/Errors/ErrorCode.php';
require_once __DIR__.'/razorpay-sdk/Razorpay.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors;
use Razorpay\Woocommerce\Errors as WooErrors;

add_action('plugins_loaded', 'woocommerce_razorpay_init', 0);
add_action('admin_post_nopriv_rzp_wc_webhook', 'razorpay_webhook_init', 10);

function woocommerce_razorpay_init()
{
    if (!class_exists('WC_Payment_Gateway'))
    {
        return;
    }

    class WC_Razorpay extends WC_Payment_Gateway
    {
        // This one stores the WooCommerce Order Id
        const SESSION_KEY                    = 'razorpay_wc_order_id';
        const RAZORPAY_PAYMENT_ID            = 'razorpay_payment_id';
        const RAZORPAY_ORDER_ID              = 'razorpay_order_id';
        const RAZORPAY_SIGNATURE             = 'razorpay_signature';

        const INR                            = 'INR';
        const CAPTURE                        = 'capture';
        const AUTHORIZE                      = 'authorize';
        const WC_ORDER_ID                    = 'woocommerce_order_id';

        const DEFAULT_LABEL                  = 'Credit Card/Debit Card/NetBanking';
        const DEFAULT_DESCRIPTION            = 'Pay securely by Credit or Debit card or Internet Banking through Razorpay.';

        protected $visibleSettings = array(
            'enabled',
            'title',
            'description',
            'key_id',
            'key_secret',
            'payment_action',
            'enable_webhook',
            'webhook_secret',
        );

        public $form_fields = array();

        public $supports = array(
            'products',
            'refunds'
        );

        /**
         * Can be set to true if you want payment fields
         * to show on the checkout (if doing a direct integration).
         * @var boolean
         */
        public $has_fields = false;

        /**
         * Unique ID for the gateway
         * @var string
         */
        public $id = 'razorpay';

        /**
         * Title of the payment method shown on the admin page.
         * @var string
         */
        public $method_title = 'Razorpay';

        /**
         * Icon URL, set in constructor
         * @var string
         */
        public $icon;

        /**
         * TODO: Remove usage of $this->msg
         */
        protected $msg = array(
            'message'   =>  '',
            'class'     =>  '',
        );

        /**
         * Return Wordpress plugin settings
         * @param  string $key setting key
         * @return mixed setting value
         */
        public function getSetting($key)
        {
            return $this->settings[$key];
        }

        /**
         * @param boolean $hooks Whether or not to
         *                       setup the hooks on
         *                       calling the constructor
         */
        public function __construct($hooks = true)
        {
            $this->icon =  plugins_url('images/logo.png' , __FILE__);

            $this->init_form_fields();
            $this->init_settings();

            // TODO: This is hacky, find a better way to do this
            // See mergeSettingsWithParentPlugin() in subscriptions for more details.
            if ($hooks)
            {
                $this->initHooks();
            }

            $this->title = $this->getSetting('title');
        }

        protected function initHooks()
        {
            add_action('init', array(&$this, 'check_razorpay_response'));

            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

            add_action('woocommerce_api_' . $this->id, array($this, 'check_razorpay_response'));

            $cb = array($this, 'process_admin_options');

            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>='))
            {
                add_action("woocommerce_update_options_payment_gateways_{$this->id}", $cb);
            }
            else
            {
                add_action('woocommerce_update_options_payment_gateways', $cb);
            }
        }

        public function init_form_fields()
        {
            $webhookUrl = esc_url(admin_url('admin-post.php')) . '?action=rzp_wc_webhook';

            $defaultFormFields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', $this->id),
                    'type' => 'checkbox',
                    'label' => __('Enable this module?', $this->id),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', $this->id),
                    'type'=> 'text',
                    'description' => __('This controls the title which the user sees during checkout.', $this->id),
                    'default' => __(static::DEFAULT_LABEL, $this->id)
                ),
                'description' => array(
                    'title' => __('Description', $this->id),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', $this->id),
                    'default' => __(static::DEFAULT_DESCRIPTION, $this->id)
                ),
                'key_id' => array(
                    'title' => __('Key ID', $this->id),
                    'type' => 'text',
                    'description' => __('The key Id and key secret can be generated from "API Keys" section of Razorpay Dashboard. Use test or live for test or live mode.', $this->id)
                ),
                'key_secret' => array(
                    'title' => __('Key Secret', $this->id),
                    'type' => 'text',
                    'description' => __('The key Id and key secret can be generated from "API Keys" section of Razorpay Dashboard. Use test or live for test or live mode.', $this->id)
                ),
                'payment_action' => array(
                    'title' => __('Payment Action', $this->id),
                    'type' => 'select',
                    'description' =>  __('Payment action on order compelete', $this->id),
                    'default' => self::CAPTURE,
                    'options' => array(
                        self::AUTHORIZE => 'Authorize',
                        self::CAPTURE   => 'Authorize and Capture'
                    )
                ),
                'enable_webhook' => array(
                    'title' => __('Enable Webhook', $this->id),
                    'type' => 'checkbox',
                    'description' =>  "<span>$webhookUrl</span><br/><br/>Instructions and guide to <a href='https://github.com/razorpay/razorpay-woocommerce/wiki/Razorpay-Woocommerce-Webhooks'>Razorpay webhooks</a>",
                    'label' => __('Enable Razorpay Webhook <a href="https://dashboard.razorpay.com/#/app/webhooks">here</a> with the URL listed below.', $this->id),
                    'default' => 'no'
                ),
                'webhook_secret' => array(
                    'title' => __('Webhook Secret', $this->id),
                    'type' => 'text',
                    'description' => __('Webhook secret is used for webhook signature verification. This has to match the one added <a href="https://dashboard.razorpay.com/#/app/webhooks">here</a>', $this->id),
                    'default' => ''
                ),
            );

            foreach ($defaultFormFields as $key => $value)
            {
                if (in_array($key, $this->visibleSettings, true))
                {
                    $this->form_fields[$key] = $value;
                }
            }
        }

        public function admin_options()
        {
            echo '<h3>'.__('Razorpay Payment Gateway', $this->id) . '</h3>';
            echo '<p>'.__('Allows payments by Credit/Debit Cards, NetBanking, UPI, and multiple Wallets') . '</p>';
            echo '<table class="form-table">';

            // Generate the HTML For the settings form.
            $this->generate_settings_html();
            echo '</table>';
        }

        public function get_description()
        {
            return $this->getSetting('description');
        }

        /**
         * Receipt Page
         * @param string $orderId WC Order Id
         **/
        function receipt_page($orderId)
        {
            echo $this->generate_razorpay_form($orderId);
        }

        /**
         * Returns key to use in session for storing Razorpay order Id
         * @param  string $orderId Razorpay Order Id
         * @return string Session Key
         */
        protected function getOrderSessionKey($orderId)
        {
            return self::RAZORPAY_ORDER_ID . $orderId;
        }

        /**
         * Given a order Id, find the associated
         * Razorpay Order from the session and verify
         * that is is still correct. If not found
         * (or incorrect), create a new Razorpay Order
         *
         * @param  string $orderId Order Id
         * @return mixed Razorpay Order Id or Exception
         */
        protected function createOrGetRazorpayOrderId($orderId)
        {
            global $woocommerce;

            $sessionKey = $this->getOrderSessionKey($orderId);

            $create = false;

            try
            {
                $razorpayOrderId = $woocommerce->session->get($sessionKey);

                // If we don't have an Order
                // or the if the order is present in session but doesn't match what we have saved
                if (($razorpayOrderId === null) or
                    (($razorpayOrderId and ($this->verifyOrderAmount($razorpayOrderId, $orderId)) === false)))
                {
                    $create = true;
                }
                else
                {
                    return $razorpayOrderId;
                }
            }
            // Order doesn't exist or verification failed
            // So try creating one
            catch (Exception $e)
            {
                $create = true;
            }

            if ($create)
            {
                try
                {
                    return $this->createRazorpayOrderId($orderId, $sessionKey);
                }
                // For the bad request errors, it's safe to show the message to the customer.
                catch (Errors\BadRequestError $e)
                {
                    return $e;
                }
                // For any other exceptions, we make sure that the error message
                // does not propagate to the front-end.
                catch (Exception $e)
                {
                    return new Exception("Payment failed");
                }
            }
        }

        /**
         * Returns redirect URL post payment processing
         * @return string redirect URL
         */
        private function getRedirectUrl()
        {
            return get_site_url() . '/wc-api/' . $this->id;
        }

        /**
         * Specific payment parameters to be passed to checkout
         * for payment processing
         * @param  string $orderId WC Order Id
         * @return array payment params
         */
        protected function getRazorpayPaymentParams($orderId)
        {
            $razorpayOrderId = $this->createOrGetRazorpayOrderId($orderId);

            if ($razorpayOrderId === null)
            {
                throw new Exception('RAZORPAY ERROR: Razorpay API could not be reached');
            }
            else if ($razorpayOrderId instanceof Exception)
            {
                $message = $razorpayOrderId->getMessage();

                throw new Exception("RAZORPAY ERROR: Order creation failed with the message: '$message'.");
            }

            return [
                'order_id'  =>  $razorpayOrderId
            ];
        }

        /**
         * Generate razorpay button link
         * @param string $orderId WC Order Id
         **/
        public function generate_razorpay_form($orderId)
        {
            $order = new WC_Order($orderId);

            try
            {
                $params = $this->getRazorpayPaymentParams($orderId);
            }
            catch (Exception $e)
            {
                return $e->getMessage();
            }

            $checkoutArgs = $this->getCheckoutArguments($order, $params);

            $html = '<p>'.__('Thank you for your order, please click the button below to pay with Razorpay.', $this->id).'</p>';

            $html .= $this->generateOrderForm($checkoutArgs);

            return $html;
        }

        /**
         * default parameters passed to checkout
         * @param  WC_Order $order WC Order
         * @return array checkout params
         */
        private function getDefaultCheckoutArguments($order)
        {
            $callbackUrl = $this->getRedirectUrl();

            $orderId = $this->getOrderId($order);

            $productinfo = "Order $orderId";

            return array(
                'key'          => $this->getSetting('key_id'),
                'name'         => get_bloginfo('name'),
                'currency'     => self::INR,
                'description'  => $productinfo,
                'notes'        => array(
                    'woocommerce_order_id' => $orderId
                ),
                'callback_url' => $callbackUrl,
                'prefill'      => $this->getCustomerInfo($order),
            );
        }

        /**
         * @param  WC_Order $order
         * @return string currency
         */
        private function getOrderCurrency($order)
        {
            if (version_compare(WOOCOMMERCE_VERSION, '2.7.0', '>='))
            {
                return $order->get_currency();
            }

            return $order->get_order_currency();
        }

        /**
         * Returns display amount to use for non-INR payments
         * @param  WC_Order $order WC Order
         * @return int order total to be displayed
         */
        protected function getDisplayAmount($order)
        {
            return (int) round($order->get_total());
        }

        /**
         * Returns array of checkout params
         */
        private function getCheckoutArguments($order, $params)
        {
            $args = $this->getDefaultCheckoutArguments($order);

            $currency = $this->getOrderCurrency($order);

            if ($currency !== self::INR)
            {
                $args['display_currency'] = $currency;
                $args['display_amount']   = $this->getDisplayAmount($order);
            }

            $args = array_merge($args, $params);

            return $args;
        }

        public function getCustomerInfo($order)
        {
            if (version_compare(WOOCOMMERCE_VERSION, '2.7.0', '>='))
            {
                $args = array(
                    'name'    => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'email'   => $order->get_billing_email(),
                    'contact' => $order->get_billing_phone(),
                );
            }
            else
            {
                $args = array(
                    'name'    => $order->billing_first_name . ' ' . $order->billing_last_name,
                    'email'   => $order->billing_email,
                    'contact' => $order->billing_phone,
                );
            }

            return $args;
        }

        protected function createRazorpayOrderId($orderId, $sessionKey)
        {
            // Calls the helper function to create order data
            global $woocommerce;

            $api = $this->getRazorpayApiInstance();

            $data = $this->getOrderCreationData($orderId);

            try
            {
                $razorpayOrder = $api->order->create($data);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();

                return 'RAZORPAY ERROR: Order creation failed with the message \'' . $message . '\'';
            }

            $razorpayOrderId = $razorpayOrder['id'];

            $woocommerce->session->set($sessionKey, $razorpayOrderId);

            return $razorpayOrderId;
        }

        /**
         * Convert the currency to INR using rates fetched from Woocommerce Currency Switcher plugin
         *
         * @param array $data Input array to convert currency
         * @param string $data['currency'] Currency
         * @param amount $data['amount'] Input Amount
         * @return array, modified $data with $data['currency'] = 'INR'
         *
         **/
        protected function convertCurrency(& $data)
        {
            global $WOOCS;

            $currencies = $WOOCS->get_currencies();

            $currency = $data['currency'];

            if (array_key_exists(self::INR, $currencies) and array_key_exists($currency, $currencies))
            {
                // If the currenct currency is the same as the default currency set in WooCommerce,
                // Currency Switcher plugin sets the rate of currenct currency as 0, because of which
                // we need to set this to 1 here if it's value is 0
                $currencyConversionRate = ($currencies[$currency]['rate'] == 0 ? 1 : $currencies[$currency]['rate']);

                // Convert the currency to INR using the rates fetched from the Currency Switcher plugin
                $value = $data['amount'] * $currencies[self::INR]['rate'];

                $data['amount'] = intval(round($value / $currencyConversionRate));
                $data['currency'] = self::INR;
            }
            else
            {
                throw new Errors\BadRequestError(
                    WooErrors\ErrorCode::WOOCS_CURRENCY_MISSING_ERROR_MESSAGE,
                    WooErrors\ErrorCode::WOOCS_CURRENCY_MISSING_ERROR_CODE,
                    400
                );

            }
        }

        protected function verifyOrderAmount($razorpayOrderId, $orderId)
        {
            $order = new WC_Order($orderId);

            $api = $this->getRazorpayApiInstance();

            try
            {
                $razorpayOrder = $api->order->fetch($razorpayOrderId);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                return "RAZORPAY ERROR: Order fetch failed with the message '$message'";
            }

            $orderCreationData = $this->getOrderCreationData($orderId);

            $razorpayOrderArgs = array(
                'id'        => $razorpayOrderId,
                'amount'    => $orderCreationData['amount'],
                'currency'  => $orderCreationData['currency'],
                'receipt'   => (string) $orderId,
            );

            $orderKeys = array_keys($razorpayOrderArgs);

            foreach ($orderKeys as $key)
            {
                if ($razorpayOrderArgs[$key] !== $razorpayOrder[$key])
                {
                    return false;
                }
            }

            return true;
        }

        private function getOrderCreationData($orderId)
        {
            $order = new WC_Order($orderId);

            $data = array(
                'receipt'         => $orderId,
                'amount'          => (int) round($order->get_total() * 100),
                'currency'        => get_woocommerce_currency(),
                'payment_capture' => ($this->getSetting('payment_action') === self::AUTHORIZE) ? 0 : 1,
                'notes'           => array(
                    self::WC_ORDER_ID  => (string) $orderId,
                ),
            );

            if ($data['currency'] !== self::INR)
            {
                $this->handleCurrencyConversion($data);
            }

            return $data;
        }

        public function handleCurrencyConversion(& $data)
        {
            if (class_exists('WOOCS') === false)
            {
                throw new Errors\BadRequestError(
                    WooErrors\ErrorCode::WOOCS_MISSING_ERROR_MESSAGE,
                    WooErrors\ErrorCode::WOOCS_MISSING_ERROR_CODE,
                    400
                );
            }

            $this->convertCurrency($data);
        }

        private function enqueueCheckoutScripts($data)
        {
            wp_register_script('razorpay_checkout',
                'https://checkout.razorpay.com/v1/checkout.js',
                null, null);

            wp_register_script('razorpay_wc_script', plugin_dir_url(__FILE__)  . 'script.js',
                array('razorpay_checkout'));

            wp_localize_script('razorpay_wc_script',
                'razorpay_wc_checkout_vars',
                $data
            );

            wp_enqueue_script('razorpay_wc_script');
        }

        /**
         * Generates the order form
         **/
        function generateOrderForm($data)
        {
            $redirectUrl = $this->getRedirectUrl();
            $this->enqueueCheckoutScripts($data);

            return <<<EOT
<form name='razorpayform' action="$redirectUrl" method="POST">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
    <!-- This distinguishes all our various wordpress plugins -->
    <input type="hidden" name="razorpay_wc_form_submit" value="1">
</form>
<p id="msg-razorpay-success" class="woocommerce-info woocommerce-message" style="display:none">
Please wait while we are processing your payment.
</p>
<p>
    <button id="btn-razorpay">Pay Now</button>
    <button id="btn-razorpay-cancel" onclick="document.razorpayform.submit()">Cancel</button>
</p>
EOT;
        }

        /**
         * Gets the Order Key from the Order
         * for all WC versions that we suport
         */
        protected function getOrderKey($order)
        {
            $orderKey = null;

            if (version_compare(WOOCOMMERCE_VERSION, '3.0.0', '>='))
            {
                return $order->get_order_key();
            }

            return $order->order_key;
        }

        protected function getOrderId($order)
        {
            if (version_compare(WOOCOMMERCE_VERSION, '2.7.0', '>='))
            {
                return $order->get_id();
            }

            return $order->id;
        }

        public function process_refund($orderId, $amount = null, $reason = '')
        {
            $order = new WC_Order($orderId);

            if (! $order or ! $order->get_transaction_id())
            {
                return new WP_Error('error', __('Refund failed: No transaction ID', 'woocommerce'));
            }

            $client = $this->getRazorpayApiInstance();

            $paymentId = $order->get_transaction_id();

            $data = array(
                'amount'    =>  (int) round($amount * 100),
                'notes'     =>  array(
                    'reason'    =>  $reason,
                    'order_id'  =>  $orderId
                )
            );

            try
            {
                $refund = $client->payment
                                 ->fetch($paymentId)
                                 ->refund($data);

                $order->add_order_note(__( 'Refund Id: ' . $refund->id, 'woocommerce' ));

                return true;
            }
            catch(Exception $e)
            {
                return new WP_Error('error', __($e->getMessage(), 'woocommerce'));
            }
        }

        /**
         * Process the payment and return the result
         **/
        function process_payment($order_id)
        {
            global $woocommerce;
            $order = new WC_Order($order_id);
            $woocommerce->session->set(self::SESSION_KEY, $order_id);

            $orderKey = $this->getOrderKey($order);

            if (version_compare(WOOCOMMERCE_VERSION, '2.1', '>='))
            {
                return array(
                    'result' => 'success',
                    'redirect' => add_query_arg('key', $orderKey, $order->get_checkout_payment_url(true))
                );
            }
            else if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>='))
            {
                return array(
                    'result' => 'success',
                    'redirect' => add_query_arg('order', $order->get_id(),
                        add_query_arg('key', $orderKey, $order->get_checkout_payment_url(true)))
                );
            }
            else
            {
                return array(
                    'result' => 'success',
                    'redirect' => add_query_arg('order', $order->get_id(),
                        add_query_arg('key', $orderKey, get_permalink(get_option('woocommerce_pay_page_id'))))
                );
            }
        }

        public function getRazorpayApiInstance()
        {
            return new Api($this->getSetting('key_id'), $this->getSetting('key_secret'));
        }

        /**
         * Check for valid razorpay server callback
         **/
        function check_razorpay_response()
        {
            global $woocommerce;

            $orderId = $woocommerce->session->get(self::SESSION_KEY);
            $order = new WC_Order($orderId);

            //
            // If the order has already been paid for
            // redirect user to success page
            //
            if ($order->needs_payment() === false)
            {
                $this->redirectUser($order);
            }

            $razorpayPaymentId = null;

            if ($orderId  and !empty($_POST[self::RAZORPAY_PAYMENT_ID]))
            {
                $error = "";
                $success = false;

                try
                {
                    $this->verifySignature($orderId);
                    $success = true;
                    $razorpayPaymentId = sanitize_text_field($_POST[self::RAZORPAY_PAYMENT_ID]);
                }
                catch (Errors\SignatureVerificationError $e)
                {
                    $error = 'WOOCOMMERCE_ERROR: Payment to Razorpay Failed. ' . $e->getMessage();
                }
            }
            else
            {
                $success = false;
                $error = 'Customer cancelled the payment';
                $this->handleErrorCase($order);
            }

            $this->updateOrder($order, $success, $error, $razorpayPaymentId);

            $this->redirectUser($order);
        }

        protected function redirectUser($order)
        {
            $redirectUrl = $this->get_return_url($order);

            wp_redirect($redirectUrl);
            exit;
        }

        protected function verifySignature($orderId)
        {
            global $woocommerce;

            $api = $this->getRazorpayApiInstance();

            $attributes = array(
                self::RAZORPAY_PAYMENT_ID => $_POST[self::RAZORPAY_PAYMENT_ID],
                self::RAZORPAY_SIGNATURE  => $_POST[self::RAZORPAY_SIGNATURE],
            );

            $sessionKey = $this->getOrderSessionKey($orderId);
            $attributes[self::RAZORPAY_ORDER_ID] = $woocommerce->session->get($sessionKey);

            $api->utility->verifyPaymentSignature($attributes);
        }

        protected function getErrorMessage($orderId)
        {
            // We don't have a proper order id
            if ($orderId !== null)
            {
                $message = 'An error occured while processing this payment';
            }
            if (isset($_POST['error']) === true)
            {
                $error = $_POST['error'];

                $description = htmlentities($error['description']);
                $code = htmlentities($error['code']);

                $message = 'An error occured. Description : ' . $description . '. Code : ' . $code;

                if (isset($error['field']) === true)
                {
                    $fieldError = htmlentities($error['field']);
                    $message .= 'Field : ' . $fieldError;
                }
            }
            else
            {
                $message = 'An error occured. Please contact administrator for assistance';
            }

            return $message;
        }

        /**
         * Modifies existing order and handles success case
         *
         * @param $success, & $order
         */
        public function updateOrder(& $order, $success, $errorMessage, $razorpayPaymentId, $webhook = false)
        {
            global $woocommerce;

            $orderId = $this->getOrderId($order);

            if ($success === true)
            {
                $this->msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be processing your order soon. Order Id: $orderId";
                $this->msg['class'] = 'success';

                $order->payment_complete($razorpayPaymentId);
                $order->add_order_note("Razorpay payment successful <br/>Razorpay Id: $razorpayPaymentId");
                $order->add_order_note($this->msg['message']);

                if (isset($woocommerce->cart) === true)
                {
                    $woocommerce->cart->empty_cart();
                }
            }
            else
            {
                $this->msg['class'] = 'error';
                $this->msg['message'] = $errorMessage;

                if ($razorpayPaymentId)
                {
                    $order->add_order_note("Payment Failed. Please check Razorpay Dashboard. <br/> Razorpay Id: $razorpayPaymentId");
                }

                $order->add_order_note("Transaction Failed: $errorMessage<br/>");
                $order->update_status('failed');
            }

            if ($webhook === false)
            {
                $this->add_notice($this->msg['message'], $this->msg['class']);
            }
        }

        protected function handleErrorCase(& $order)
        {
            $orderId = $this->getOrderId($order);

            $this->msg['class'] = 'error';
            $this->msg['message'] = $this->getErrorMessage($orderId);
        }

        /**
         * Add a woocommerce notification message
         *
         * @param string $message Notification message
         * @param string $type Notification type, default = notice
         */
        protected function add_notice($message, $type = 'notice')
        {
            global $woocommerce;
            $type = in_array($type, array('notice','error','success'), true) ? $type : 'notice';
            // Check for existence of new notification api. Else use previous add_error
            if (function_exists('wc_add_notice'))
            {
                wc_add_notice($message, $type);
            }
            else
            {
                // Retrocompatibility WooCommerce < 2.1
                switch ($type)
                {
                    case "error" :
                        $woocommerce->add_error($message);
                        break;
                    default :
                        $woocommerce->add_message($message);
                        break;
                }
            }
        }
    }

    /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_razorpay_gateway($methods)
    {
        $methods[] = 'WC_Razorpay';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_razorpay_gateway' );
}

// This is set to a priority of 10
function razorpay_webhook_init()
{
    $rzpWebhook = new RZP_Webhook();

    $rzpWebhook->process();
}
