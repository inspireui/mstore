<?php
/**
 * WooCommerce Braintree Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@woocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Braintree Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Braintree Gateway for your
 * needs please refer to http://docs.woocommerce.com/document/braintree/
 *
 * @package   WC-Braintree/Gateway/PayPal
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree PayPal Gateway Class
 *
 * @since 3.0.0
 */
class WC_Gateway_Braintree_PayPal extends WC_Gateway_Braintree {


	/** PayPal payment type */
	const PAYMENT_TYPE_PAYPAL = 'paypal';


	/** @var bool whether cart checkout is enabled */
	protected $enable_cart_checkout;

	/** @var bool whether paypal credit is enabled */
	protected $enable_paypal_credit;

	/** @var string button color */
	protected $button_color;

	/** @var string button size */
	protected $button_size;

	/** @var string button shape */
	protected $button_shape;


	/**
	 * Initialize the gateway
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		parent::__construct(
			WC_Braintree::PAYPAL_GATEWAY_ID,
			wc_braintree(),
			array(
				'method_title'       => __( 'Braintree (PayPal)', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'method_description' => __( 'Allow customers to securely pay using their PayPal account via Braintree.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'supports'           => array(
					self::FEATURE_PRODUCTS,
					self::FEATURE_CARD_TYPES,
					self::FEATURE_PAYMENT_FORM,
					self::FEATURE_TOKENIZATION,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_CHARGE_VIRTUAL,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
					self::FEATURE_REFUNDS,
					self::FEATURE_VOIDS,
					self::FEATURE_CUSTOMER_ID,
					self::FEATURE_ADD_PAYMENT_METHOD,
					self::FEATURE_TOKEN_EDITOR,
				),
				'payment_type'       => self::PAYMENT_TYPE_PAYPAL,
				'environments'       => $this->get_braintree_environments(),
				'shared_settings'    => $this->shared_settings_names,
			)
		);

		// tweak some frontend text so it matches PayPal
		add_filter( 'gettext', array( $this, 'tweak_payment_methods_text' ), 10, 3 );

		// tweak the "Delete" link text on the My Payment Methods table to "Unlink"
		add_filter( 'wc_braintree_my_payment_methods_table_method_actions', array( $this, 'tweak_my_payment_methods_delete_text' ), 10, 2 );

		// tweak the admin token editor to support PayPal accounts
		add_filter( 'wc_payment_gateway_braintree_paypal_token_editor_fields', array( $this, 'adjust_token_editor_fields' ) );

		// sanitize admin options before saving
		add_filter( 'woocommerce_settings_api_sanitized_fields_braintree_paypal', array( $this, 'filter_admin_options' ) );

		// get the client token via AJAX
		add_filter( 'wp_ajax_wc_' . $this->get_id() . '_get_client_token',        array( $this, 'ajax_get_client_token' ) );
		add_filter( 'wp_ajax_nopriv_wc_' . $this->get_id() . '_get_client_token', array( $this, 'ajax_get_client_token' ) );
	}


	/**
	 * Enqueues the PayPal JS scripts
	 *
	 * @since 2.1.0
	 * @see SV_WC_Payment_Gateway::enqueue_gateway_assets()
	 */
	public function enqueue_gateway_assets() {

		if ( $this->is_available() && $this->is_payment_form_page() ) {

			parent::enqueue_gateway_assets();

			wp_enqueue_script( 'braintree-js-paypal', 'https://www.paypalobjects.com/api/checkout.js', array(), WC_Braintree::VERSION, true );
			wp_enqueue_script( 'braintree-js-paypal-checkout', 'https://js.braintreegateway.com/web/' . WC_Braintree::BRAINTREE_JS_SDK_VERSION . '/js/paypal-checkout.min.js', array(), WC_Braintree::VERSION, true );
		}
	}


	/**
	 * Determines if the current page contains a payment form.
	 *
	 * @since 2.1.0
	 * @return bool
	 */
	public function is_payment_form_page() {

		return parent::is_payment_form_page() || is_cart();
	}


	/**
	 * Add PayPal-specific fields to the admin payment token editor
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public function adjust_token_editor_fields() {

		$fields = array(
			'id' => array(
				'label'    => __( 'Token ID', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'editable' => false,
				'required' => true,
			),
			'payer_email' => array(
				'label'   => __( 'Email', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'editable' => false,
			),
		);

		return $fields;
	}


	/**
	 * Initializes the payment form handler.
	 *
	 * @since 2.2.1
	 */
	public function init_payment_form_handler() {

		$this->payment_form_handler = new \WC_Braintree_PayPal_Payment_Form( $this );
	}


	/**
	 * Tweak two frontend strings so they match PayPal lingo instead of "Bank". This is
	 * the least hacky approach that doesn't require fairly significant refactoring
	 * of the framework code responsible for these strings, or results in an approach
	 * that won't work when the strings are translated
	 *
	 * @since 3.0.0
	 * @param string $translated_text translated text
	 * @param string $raw_text pre-translated text
	 * @param string $text_domain text domain
	 * @return string
	 */
	public function tweak_payment_methods_text( $translated_text, $raw_text, $text_domain ) {

		if ( 'woocommerce-gateway-paypal-powered-by-braintree' === $text_domain ) {

			if ( 'Use a new bank account' === $raw_text ) {

				$translated_text = __( 'Use a new PayPal account', 'woocommerce-gateway-paypal-powered-by-braintree' );

			} elseif ( 'Bank Accounts' === $raw_text ) {

				$translated_text = __( 'PayPal Accounts', 'woocommerce-gateway-paypal-powered-by-braintree' );
			}
		}

		return $translated_text;
	}


	/**
	 * Tweak the "Delete" link on the My Payment Methods actions list to "Unlink"
	 * which is more semantically correct (and less likely to cause customers
	 * to think they are deleting their actual PayPal account)
	 *
	 * @since 3.0.0
	 * @param array $actions payment method actions
	 * @param \WC_Braintree_Payment_Method $token
	 * @return array
	 */
	public function tweak_my_payment_methods_delete_text( $actions, $token ) {

		if ( $token->is_paypal_account() ) {
			$actions['delete']['name'] = __( 'Unlink', 'woocommerce-gateway-paypal-powered-by-braintree' );
		}

		return $actions;
	}


	/**
	 * Adds any credit card authorization/charge admin fields, allowing the
	 * administrator to choose between performing authorizations or charges.
	 *
	 * Overridden to add the Cart Checkout setting in an appropriate spot.
	 *
	 * @since 2.1.0
	 *
	 * @param array $form_fields gateway form fields
	 * @return array
	 */
	protected function add_authorization_charge_form_fields( $form_fields ) {

		$form_fields['button_appearance_title'] = [
			'type'  => 'title',
			'title' => __( 'Button Appearance', 'woocommerce-gateway-paypal-powered-by-braintree' ),
		];

		$form_fields['button_color'] = [
			'type'  => 'select',
			'title' => __( 'Button Color', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'options' => [
				'gold'   => __( 'Gold', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'blue'   => __( 'Blue', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'silver' => __( 'Silver', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'white'  => __( 'White', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'black'  => __( 'Black', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			],
			'default' => 'gold',
		];

		$form_fields['button_size'] = [
			'type'  => 'select',
			'title' => __( 'Button Size', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'options' => [
				'medium'     => __( 'Medium', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'large'      => __( 'Large', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'responsive' => __( 'Responsive', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			],
			'default' => 'responsive',
		];

		$form_fields['button_shape'] = [
			'type'  => 'select',
			'title' => __( 'Button Shape', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'options' => [
				'pill' => _x( 'Pill', 'button shape option', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'rect' => _x( 'Rectangle', 'button shape option', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			],
			'default' => 'pill',
		];

		$form_fields['enable_paypal_credit'] = array(
			'title'       => __( 'PayPal Credit', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'type'        => 'checkbox',
			'disabled'    => ! $this->is_paypal_credit_supported(),
			'label'       => __( 'Show the PayPal credit button beneath the standard PayPal button', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'description' => ! $this->is_paypal_credit_supported() ? __( 'Currently disabled because PayPal Credit is only available for US merchants', 'woocommerce-gateway-paypal-powered-by-braintree'  ) : '',
			'default'     => 'no',
		);

		$form_fields['button_preview'] = [
			'type'  => 'button_preview',
		];

		$form_fields['enable_cart_checkout'] = array(
			'title'   => __( 'Enable Cart Checkout', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'type'    => 'checkbox',
			'label'   => __( 'Allow customers to check out with PayPal from the Cart page', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'default' => 'yes',
		);

		return parent::add_authorization_charge_form_fields( $form_fields );
	}


	/**
	 * Generates HTML for the PayPal button preview.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	protected function generate_button_preview_html() {

		wp_enqueue_script( 'braintree-js-paypal', 'https://www.paypalobjects.com/api/checkout.js', array(), WC_Braintree::VERSION, true );

		$button_js = '

			var funding = {};

			if ( offer_credit ) {
				funding.allowed = [ paypal.FUNDING.CREDIT ];
			} else {
				funding.disallowed = [ paypal.FUNDING.CREDIT ];
			}

			paypal.Button.render( {
				env: "sandbox",
				style: {
					label: "pay",
					color: color,
					size: size,
					shape: shape,
					layout: "vertical",
					tagline: false,
				},
				funding: funding,
				client: {
					sandbox: "sandbox",
				},
				payment: function( data, actions ) {
					return actions.payment.create( {
						payment: {
							transactions: [
								{
									amount: { total: "0.01", currency: "USD" }
								}
							]
						}
					} );
				},
				onAuthorize: function( data, actions ) {}
			}, "#wc_braintree_paypal_button_preview" );
		';

		wc_enqueue_js( '

			$( "#woocommerce_braintree_paypal_button_color, #woocommerce_braintree_paypal_button_size, #woocommerce_braintree_paypal_button_shape, #woocommerce_braintree_paypal_enable_paypal_credit" ).on( "change", function() {

				$( "#wc_braintree_paypal_button_preview" ).empty();

				var color        = $( "#woocommerce_braintree_paypal_button_color" ).val();
				var size         = $( "#woocommerce_braintree_paypal_button_size" ).val();
				var shape        = $( "#woocommerce_braintree_paypal_button_shape" ).val();
				var offer_credit = $( "#woocommerce_braintree_paypal_enable_paypal_credit" ).is( ":checked" );

				' . $button_js . '

			} ).change();

		' );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Preview', 'woocommerce-gateway-paypal-powered-by-braintree' ); ?>
			</th>
			<td class="forminp">
				<div id="wc_braintree_paypal_button_preview" style="max-width:400px; pointer-events:none;"></div>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}


	/**
	 * Add PayPal method specific form fields, currently:
	 *
	 * + remove phone/URL dynamic descriptor (does not apply to PayPal)
	 *
	 * @since 3.0.0
	 * @see WC_Gateway_Braintree::get_method_form_fields()
	 * @return array
	 */
	protected function get_method_form_fields() {

		$fields = parent::get_method_form_fields();

		unset( $fields['phone_dynamic_descriptor'] );
		unset( $fields['url_dynamic_descriptor'] );

		return $fields;
	}


	/**
	 * Verify that a payment method nonce is present before processing the
	 * transaction
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	protected function validate_paypal_fields( $is_valid ) {

		return $this->validate_payment_nonce( $is_valid );
	}


	/**
	 * Gets the PayPal checkout locale based on the WordPress locale
	 *
	 * @link http://wpcentral.io/internationalization/
	 * @link https://developers.braintreepayments.com/guides/paypal/vault/javascript/v2#country-and-language-support
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_safe_locale() {

		$locale = strtolower( get_locale() );

		$safe_locales = array(
				'en_au',
				'de_at',
				'en_be',
				'en_ca',
				'da_dk',
				'en_us',
				'fr_fr',
				'de_de',
				'en_gb',
				'zh_hk',
				'it_it',
				'nl_nl',
				'no_no',
				'pl_pl',
				'es_es',
				'sv_se',
				'en_ch',
				'tr_tr',
				'es_xc',
				'fr_ca',
				'ru_ru',
				'en_nz',
				'pt_pt',
		);

		if ( ! in_array( $locale, $safe_locales ) ) {
			$locale = 'en_us';
		}

		/**
		 * Braintree PayPal Locale Filter.
		 *
		 * Allow actors to filter the locale used for the Braintree SDK
		 *
		 * @since 3.0.0
		 * @param string $lang The button locale.
		 * @return string
		 */
		return apply_filters( 'wc_braintree_paypal_locale', $locale );
	}


	/**
	 * Performs a payment transaction for the given order and returns the
	 * result
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::do_transaction()
	 * @param \WC_Order $order the order object
	 * @return \SV_WC_Payment_Gateway_API_Response the response
	 */
	protected function do_paypal_transaction( WC_Order $order ) {

		if ( $this->perform_credit_card_charge( $order ) ) {
			$response = $this->get_api()->credit_card_charge( $order );
		} else {
			$response = $this->get_api()->credit_card_authorization( $order );
		}

		// success! update order record
		if ( $response->transaction_approved() ) {

			// order note, e.g. Braintree (PayPal) Sandbox Payment Approved (Transaction ID ABC)
			/* translators: Placeholders: %1$s - payment method title (e.g. PayPal), %2$s - transaction environment (either Sandbox or blank string), %3$s - type of transaction (either Authorization or Payment) */
			$message = sprintf(
				__( '%1$s %2$s %3$s Approved', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				$this->get_method_title(),
				$this->is_test_environment() ? __( 'Sandbox', 'woocommerce-gateway-paypal-powered-by-braintree' ) : '',
				$this->perform_credit_card_authorization( $order ) ? __( 'Authorization', 'woocommerce-gateway-paypal-powered-by-braintree' ) : __( 'Payment', 'woocommerce-gateway-paypal-powered-by-braintree' )
			);

			// adds the transaction id (if any) to the order note
			if ( $response->get_transaction_id() ) {
				/* translators: Placeholders: %s - transaction ID */
				$message .= ' ' . sprintf( __( '(Transaction ID %s)', 'woocommerce-gateway-paypal-powered-by-braintree' ), $response->get_transaction_id() );
			}

			$order->add_order_note( $message );
		}

		return $response;
	}


	/**
	 * Get the order note message when a customer saves their PayPal account
	 * to their WC account
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::get_saved_payment_method_token_order_note()
	 * @param \WC_Braintree_Payment_Method $token the payment token being saved
	 * @return string
	 */
	protected function get_saved_payment_token_order_note( $token ) {

		return sprintf( __( 'PayPal Account Saved: %s', 'woocommerce-gateway-paypal-powered-by-braintree' ), $token->get_payer_email() );
	}


	/**
	 * Adds any gateway-specific transaction data to the order
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::add_transaction_data()
	 * @param \WC_Order $order the order object
	 * @param \WC_Braintree_API_PayPal_Transaction_Response $response the transaction response
	 */
	public function add_payment_gateway_transaction_data( $order, $response ) {

		// authorization code, called "Authorization Unique Transaction ID" by PayPal
		if ( $response->get_authorization_code() ) {
			$this->update_order_meta( $order, 'authorization_code', $response->get_authorization_code() );
		}

		// charge captured
		if ( $order->payment_total > 0 ) {
			// mark as captured
			if ( $this->perform_credit_card_charge( $order ) ) {
				$captured = 'yes';
			} else {
				$captured = 'no';
			}
			$this->update_order_meta( $order, 'charge_captured', $captured );
		}

		// payer email
		if ( $response->get_payer_email() ) {
			$this->update_order_meta( $order, 'payer_email', $response->get_payer_email() );
		}

		// payment ID
		if ( $response->get_payment_id() ) {
			$this->update_order_meta( $order, 'payment_id', $response->get_payment_id() );
		}

		// debug ID, if logging is enabled
		if ( $this->debug_log() && $response->get_debug_id() ) {
			$this->update_order_meta( $order, 'debug_id', $response->get_debug_id() );
		}
	}


	/** Refund feature ********************************************************/


	/**
	 * Adds PayPal-specific data to the order after a refund is performed
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order the order object
	 * @param \WC_Braintree_API_PayPal_Transaction_Response $response the transaction response
	 */
	protected function add_payment_gateway_refund_data( WC_Order $order, $response ) {

		if ( $response->get_refund_id() ) {
			// add_order_meta() to account for multiple refunds on a single order
			$this->add_order_meta( $order, 'refund_id', $response->get_refund_id() );
		}
	}


	/** Getters ***************************************************************/


	/**
	 * Get the default payment method title, which is configurable within the
	 * admin and displayed on checkout
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::get_default_title()
	 * @return string payment method title to show on checkout
	 */
	protected function get_default_title() {

		return __( 'PayPal', 'woocommerce-gateway-paypal-powered-by-braintree' );
	}


	/**
	 * Get the default payment method description, which is configurable
	 * within the admin and displayed on checkout
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::get_default_description()
	 * @return string payment method description to show on checkout
	 */
	protected function get_default_description() {

		return __( 'Click the PayPal icon below to sign into your PayPal account and pay securely.', 'woocommerce-gateway-paypal-powered-by-braintree' );
	}


	/**
	 * Override the default icon to set a PayPal-specific one
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_icon() {

		// from https://www.paypal.com/webapps/mpp/logos-buttons
		$icon_html = '<img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png" alt="PayPal" />';

		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->get_id() );
	}


	/**
	 * Return the PayPal payment method image URL
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::get_payment_method_image_url()
	 * @param string $type unused
	 * @return string the image URL
	 */
	public function get_payment_method_image_url( $type ) {

		return parent::get_payment_method_image_url( 'paypal' );
	}


	/**
	 * Braintree PayPal acts like a direct gateway
	 *
	 * @since 3.0.0
	 * @return boolean true if the gateway supports authorization
	 */
	public function supports_credit_card_authorization() {
		return $this->supports( self::FEATURE_CREDIT_CARD_AUTHORIZATION );
	}


	/**
	 * Braintree PayPal acts like a direct gateway
	 *
	 * @since 3.0.0
	 * @return boolean true if the gateway supports charges
	 */
	public function supports_credit_card_charge() {
		return $this->supports( self::FEATURE_CREDIT_CARD_CHARGE );
	}


	/**
	 * Determines if cart checkout is enabled.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function cart_checkout_enabled() {

		/**
		 * Filters whether cart checkout is enabled.
		 *
		 * @since 2.1.0
		 *
		 * @param bool $enabled whether cart checkout is enabled in the settings
		 * @param \WC_Gateway_Braintree_PayPal $gateway gateway object
		 */
		return (bool) apply_filters( 'wc_braintree_paypal_cart_checkout_enabled', 'no' !== $this->enable_cart_checkout, $this );
	}


	/**
	 * Determines if PayPal Credit is enabled.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public function is_paypal_credit_enabled() {

		return $this->is_paypal_credit_supported() && 'yes' === $this->enable_paypal_credit;
	}


	/**
	 * Determines if PayPal Credit is supported.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public function is_paypal_credit_supported() {

		return 'US' === WC()->countries->get_base_country();
	}


	/**
	 * Gets the configured button color.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_button_color() {

		return $this->get_option( 'button_color' );
	}


	/**
	 * Gets the configured button size.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_button_size() {

		return $this->get_option( 'button_size' );
	}


	/**
	 * Gets the configured button shape.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_button_shape() {

		return $this->get_option( 'button_shape' );
	}


}
