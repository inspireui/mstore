<?php
require_once(__DIR__ . '/flutter-base.php');
require_once(__DIR__ . '/helpers/flutter-stripe-helper.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Paid Memberships Pro
 */

class FlutterPaidMembershipsPro extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_paid_memberships_pro';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_paid_memberships_pro_routes'));
    }

    public function register_flutter_paid_memberships_pro_routes()
    {
        register_rest_route($this->namespace, '/plans', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_plans'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/register', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'membership_register'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function get_plans()
    {
        if (!is_plugin_active('paid-memberships-pro/paid-memberships-pro.php')) {
            return parent::send_invalid_plugin_error("Please install Paid Memberships Pro");
        }

        $gateway = pmpro_getOption( "gateway" );
        if($gateway != 'stripe'){
            return parent::sendError("gateway_unsupport", "The app supports only Stripe payment gateway now.  Please change payment gateway for Paid Membership Pro plugin in admin panel", 400);
        }

        $pmpro_levels = pmpro_sort_levels_by_order( pmpro_getAllLevels(false, true) );
        $pmpro_levels = apply_filters( 'pmpro_levels_array', $pmpro_levels );

        $plans = [];
        foreach($pmpro_levels as $level)
        {
            $cost_text = pmpro_getLevelCost($level, true, true); 
			$expiration_text = pmpro_getLevelExpiration($level);

			if(!empty($cost_text) && !empty($expiration_text))
                $cost_text = $cost_text . "<br />" . $expiration_text;
			elseif(!empty($cost_text))
                $cost_text = $cost_text;
		    elseif(!empty($expiration_text))
                $cost_text = $expiration_text;

                $plans[] = [
                    'id'=>$level->id,
                    'name' => $level->name,
                    'description' => $level->description,
                    'confirmation' => $level->confirmation,
                    'initial_payment' => $level->initial_payment,
                    'billing_amount' => $level->billing_amount,
                    'cycle_number' => $level->cycle_number,
                    'cycle_period' => $level->cycle_period,
                    'billing_limit' => $level->billing_limit,
                    'trial_amount' => $level->trial_amount,
                    'trial_limit' => $level->trial_limit,
                    'allow_signups' => $level->allow_signups,
                    'expiration_number' => $level->expiration_number,
                    'expiration_period' => $level->expiration_period,
                    'cost_text' => $cost_text,
                    'is_free_level' => pmpro_isLevelFree( $level )
                ];
        }
        return $plans;
    }

    private function init_params(){
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $username = sanitize_text_field($params['username']);
        $password = sanitize_text_field($params['password']);
        $bemail = sanitize_text_field($params['email']);
        $bfirstname = sanitize_text_field($params['firstname']);
        $blastname = sanitize_text_field($params['lastname']);
        $fullname = $bfirstname.' '.$blastname;
        $planId = sanitize_text_field($params['plan']);

        $_REQUEST['level'] = $planId;
        $_REQUEST['username'] = $username;
        $_REQUEST['password'] = $password;
        $_REQUEST['password2'] = $password;
        $_REQUEST['bemail'] = $bemail;
        $_REQUEST['bconfirmemail'] = $bemail;
        $_REQUEST['fullname'] = $fullname;
        $_REQUEST['bfirstname'] = $bfirstname;
        $_REQUEST['blastname'] = $blastname;

        global $gateway, $username, $password, $password2, $bfirstname, $blastname, $baddress1, $baddress2, $bcity, $bstate, $bzipcode, $bcountry, $bphone, $bemail, $bconfirmemail, $CardType, $AccountNumber, $ExpirationMonth, $ExpirationYear;

        if ( isset( $_REQUEST['order_id'] ) ) {
            $order_id = intval( $_REQUEST['order_id'] );
        } else {
            $order_id = "";
        }
        if ( isset( $_REQUEST['bfirstname'] ) ) {
            $bfirstname = sanitize_text_field( stripslashes( $_REQUEST['bfirstname'] ) );
        } else {
            $bfirstname = "";
        }
        if ( isset( $_REQUEST['blastname'] ) ) {
            $blastname = sanitize_text_field( stripslashes( $_REQUEST['blastname'] ) );
        } else {
            $blastname = "";
        }
        if ( isset( $_REQUEST['fullname'] ) ) {
            $fullname = $_REQUEST['fullname'];
        }        //honeypot for spammers
        if ( isset( $_REQUEST['baddress1'] ) ) {
            $baddress1 = sanitize_text_field( stripslashes( $_REQUEST['baddress1'] ) );
        } else {
            $baddress1 = "";
        }
        if ( isset( $_REQUEST['baddress2'] ) ) {
            $baddress2 = sanitize_text_field( stripslashes( $_REQUEST['baddress2'] ) );
        } else {
            $baddress2 = "";
        }
        if ( isset( $_REQUEST['bcity'] ) ) {
            $bcity = sanitize_text_field( stripslashes( $_REQUEST['bcity'] ) );
        } else {
            $bcity = "";
        }
        
        if ( isset( $_REQUEST['bstate'] ) ) {
            $bstate = sanitize_text_field( stripslashes( $_REQUEST['bstate'] ) );
        } else {
            $bstate = "";
        }
        
        //convert long state names to abbreviations
        if ( ! empty( $bstate ) ) {
            global $pmpro_states;
            foreach ( $pmpro_states as $abbr => $state ) {
                if ( $bstate == $state ) {
                    $bstate = $abbr;
                    break;
                }
            }
        }
        
        if ( isset( $_REQUEST['bzipcode'] ) ) {
            $bzipcode = sanitize_text_field( stripslashes( $_REQUEST['bzipcode'] ) );
        } else {
            $bzipcode = "";
        }
        if ( isset( $_REQUEST['bcountry'] ) ) {
            $bcountry = sanitize_text_field( stripslashes( $_REQUEST['bcountry'] ) );
        } else {
            $bcountry = "";
        }
        if ( isset( $_REQUEST['bphone'] ) ) {
            $bphone = sanitize_text_field( stripslashes( $_REQUEST['bphone'] ) );
        } else {
            $bphone = "";
        }
        if ( isset ( $_REQUEST['bemail'] ) ) {
            $bemail = sanitize_email( stripslashes( $_REQUEST['bemail'] ) );
        }
        if ( isset( $_REQUEST['bconfirmemail'] ) ) {
            $bconfirmemail = sanitize_email( stripslashes( $_REQUEST['bconfirmemail'] ) );
        }
        
        if ( isset( $_REQUEST['discount_code'] ) ) {
            $discount_code = preg_replace( "/[^A-Za-z0-9\-]/", "", $_REQUEST['discount_code'] );
        } else {
            $discount_code = "";
        }
        if ( isset( $_REQUEST['username'] ) ) {
            $username = sanitize_user( $_REQUEST['username'] , true);
        } else {
            $username = "";
        }
        if ( isset( $_REQUEST['password'] ) ) {
            $password = $_REQUEST['password'];
        } else {
            $password = "";
        }
        if ( isset( $_REQUEST['password2'] ) ) {
            $password2 = $_REQUEST['password2'];
        }
        if ( isset( $_REQUEST['tos'] ) ) {
            $tos = intval( $_REQUEST['tos'] );
        } else {
            $tos = "";
        }
        if ( ! empty( $_REQUEST['gateway'] ) ) {
            $gateway = sanitize_text_field($_REQUEST['gateway']);
        } elseif ( ! empty( $_REQUEST['review'] ) ) {
            $gateway = "paypalexpress";
        } else {
            $gateway = pmpro_getOption( "gateway" );
        }

        $pmpro_level = pmpro_getLevelAtCheckout($planId);
        if($gateway == 'stripe' && !pmpro_isLevelFree( $pmpro_level )){
            $card = $params['card'];
            $key = '';
            if (PMProGateway_stripe::using_legacy_keys()) {
                $key = get_option('pmpro_stripe_secretkey');
            } else {
                $key = get_option('pmpro_gateway_environment') === 'live'
                ? get_option('pmpro_live_stripe_connect_secretkey')
                : get_option('pmpro_sandbox_stripe_connect_secretkey');
            }
            $s = new FlutterStripeHelper($key);
            $s->url .= 'payment_methods';
            $s->method = "POST";
            $s->fields['card'] = array(
                'number'=>sanitize_text_field($card['number']),
                'exp_month'=>sanitize_text_field($card['exp_month']),
                'exp_year'=>sanitize_text_field($card['exp_year']),
                'cvc'=>sanitize_text_field($card['cvc']),
            );
            $s->fields['type'] = 'card';
            $payment_method = $s->call();
    
            if(isset($payment_method['error'])){
                return $payment_method['error']['message'];
            }
            $_REQUEST['CardType'] = $payment_method['card']['brand'];
            $_REQUEST['payment_method_id'] = $payment_method['id'];
            $_REQUEST['AccountNumber'] = 'XXXXXXXXXXXX'.$payment_method['card']['last4'];
            $_REQUEST['ExpirationMonth'] = $payment_method['card']['exp_month'];
            $_REQUEST['ExpirationYear'] = $payment_method['card']['exp_year'];
        }

        if ( isset( $_REQUEST['CardType'] ) && ! empty( $_REQUEST['AccountNumber'] ) ) {
            $CardType = sanitize_text_field( $_REQUEST['CardType'] );
        } else {
            $CardType = "";
        }
        if ( isset( $_REQUEST['AccountNumber'] ) ) {
            $AccountNumber = sanitize_text_field( $_REQUEST['AccountNumber'] );
        } else {
            $AccountNumber = "";
        }
        
        if ( isset( $_REQUEST['ExpirationMonth'] ) ) {
            $ExpirationMonth = sanitize_text_field( $_REQUEST['ExpirationMonth'] );
        } else {
            $ExpirationMonth = "";
        }
        if ( isset( $_REQUEST['ExpirationYear'] ) ) {
            $ExpirationYear = sanitize_text_field( $_REQUEST['ExpirationYear'] );
        } else {
            $ExpirationYear = "";
        }
        if ( isset( $_REQUEST['CVV'] ) ) {
            $CVV = sanitize_text_field( $_REQUEST['CVV'] );
        } else {
            $CVV = "";
        }
    }

    public function membership_register()
    {
        if (!is_plugin_active('paid-memberships-pro/paid-memberships-pro.php')) {
            return parent::send_invalid_plugin_error("Please install Paid Memberships Pro");
        }

        global $wpdb, $gateway, $username, $password, $password2, $bfirstname, $blastname, $baddress1, $baddress2, $bcity, $bstate, $bzipcode, $bcountry, $bphone, $bemail, $bconfirmemail, $CardType, $AccountNumber, $ExpirationMonth, $ExpirationYear, $pmpro_requirebilling;
        $errMsg = $this->init_params();
        if(isset($errMsg)){
            return parent::sendError('invalid_card', $errMsg, 400);	
        }
        $pmpro_level = pmpro_getLevelAtCheckout();
        if ( ! pmpro_isLevelFree( $pmpro_level ) ) {
            $pmpro_requirebilling = true;
        } else {
            $pmpro_requirebilling = false;
        }
        $pmpro_requirebilling = apply_filters( 'pmpro_require_billing', $pmpro_requirebilling, $pmpro_level );

        if($pmpro_requirebilling){
            $morder = pmpro_build_order_for_checkout();
            $pmpro_processed = $morder->process();
            if ( ! empty( $pmpro_processed ) ) {
                $pmpro_msg       = __( "Payment accepted.", 'paid-memberships-pro' );
                $pmpro_msgt      = "pmpro_success";
                $pmpro_confirmed = true;
            } else {
                $pmpro_msg = !empty( $morder->error ) ? $morder->error : null;
                if ( empty( $pmpro_msg ) ) {
                    $pmpro_msg = __( "Unknown error generating account. Please contact us to set up your membership.", 'paid-memberships-pro' );
                }
                
                if ( ! empty( $morder->error_type ) ) {
                    $pmpro_msgt = $morder->error_type;
                } else {
                    $pmpro_msgt = "pmpro_error";
                }	
                return parent::sendError($pmpro_msgt, $pmpro_msg, 400);			
            }
        }
        
        //insert user
		$new_user_array = apply_filters( 'pmpro_checkout_new_user_array', array(
                "user_login" => $username,
                "user_pass"  => $password,
                "user_email" => $bemail,
                "first_name" => $first_name,
                "last_name"  => $last_name
            )
        );

        $user_id = apply_filters( 'pmpro_new_user', '', $new_user_array );
        if ( empty( $user_id ) ) {
            $user_id = wp_insert_user( $new_user_array );
        }

        if ( empty( $user_id ) || is_wp_error( $user_id ) ) {
            $e_msg = '';

            if ( is_wp_error( $user_id ) ) {
                $e_msg = $user_id->get_error_message();
            }

            $pmpro_msg  = __( "Your payment was accepted, but there was an error setting up your account. Please contact us.", 'paid-memberships-pro' ) . sprintf( " %s", $e_msg ); // Dirty 'don't break translation hack.
            $pmpro_msgt = "pmpro_error";
            return parent::sendError($pmpro_msgt, $pmpro_msg, 400);
        } elseif ( apply_filters( 'pmpro_setup_new_user', true, $user_id, $new_user_array, $pmpro_level ) ) {

            //check pmpro_wp_new_user_notification filter before sending the default WP email
            if ( apply_filters( "pmpro_wp_new_user_notification", true, $user_id, $pmpro_level->id ) ) {
                if ( version_compare( $wp_version, "4.3.0" ) >= 0 ) {
                    wp_new_user_notification( $user_id, null, 'both' );
                } else {
                    wp_new_user_notification( $user_id, $new_user_array['user_pass'] );
                }
            }

            $wpuser = get_userdata( $user_id );

            //make the user a subscriber
            $wpuser->set_role( get_option( 'default_role', 'subscriber' ) );

            /**
             * Allow hooking before the user authentication process when setting up new user.
             *
             * @since 2.5.10
             *
             * @param int $user_id The user ID that is being setting up.
             */
            do_action( 'pmpro_checkout_before_user_auth', $user_id );


            //okay, log them in to WP
            $creds                  = array();
            $creds['user_login']    = $new_user_array['user_login'];
            $creds['user_password'] = $new_user_array['user_pass'];
            $creds['remember']      = true;
            $user                   = wp_signon( $creds, false );
            //setting some cookies
            wp_set_current_user( $user_id, $username );
            wp_set_auth_cookie( $user_id, true, apply_filters( 'pmpro_checkout_signon_secure', force_ssl_admin() ) );
        }

        if ( ! empty( $user_id ) && ! is_wp_error( $user_id ) ) {
            do_action( 'pmpro_checkout_before_change_membership_level', $user_id, $morder );
    
            //start date is NOW() but filterable below
            $startdate = current_time( "mysql" );
    
            /**
             * Filter the start date for the membership/subscription.
             *
             * @since 1.8.9
             *
             * @param string $startdate , datetime formatsted for MySQL (NOW() or YYYY-MM-DD)
             * @param int $user_id , ID of the user checking out
             * @param object $pmpro_level , object of level being checked out for
             */
            $startdate = apply_filters( "pmpro_checkout_start_date", $startdate, $user_id, $pmpro_level );
    
            //calculate the end date
            if ( ! empty( $pmpro_level->expiration_number ) ) {
                if( $pmpro_level->expiration_period == 'Hour' ){
                    $enddate =  date( "Y-m-d H:i:s", strtotime( "+ " . $pmpro_level->expiration_number . " " . $pmpro_level->expiration_period, current_time( "timestamp" ) ) );
                } else {
                    $enddate =  date( "Y-m-d 23:59:59", strtotime( "+ " . $pmpro_level->expiration_number . " " . $pmpro_level->expiration_period, current_time( "timestamp" ) ) );
                }
            } else {
                $enddate = "NULL";
            }
    
            /**
             * Filter the end date for the membership/subscription.
             *
             * @since 1.8.9
             *
             * @param string $enddate , datetime formatsted for MySQL (YYYY-MM-DD)
             * @param int $user_id , ID of the user checking out
             * @param object $pmpro_level , object of level being checked out for
             * @param string $startdate , startdate calculated above
             */
            $enddate = apply_filters( "pmpro_checkout_end_date", $enddate, $user_id, $pmpro_level, $startdate );
    
            //check code before adding it to the order
            global $pmpro_checkout_level_ids; // Set by MMPU.
            if ( isset( $pmpro_checkout_level_ids ) ) {
                $code_check = pmpro_checkDiscountCode( $discount_code, $pmpro_checkout_level_ids, true );
            } else {
                $code_check = pmpro_checkDiscountCode( $discount_code, $pmpro_level->id, true );
            }
            
            if ( $code_check[0] == false ) {
                //error
                $pmpro_msg  = $code_check[1];
                $pmpro_msgt = "pmpro_error";
    
                //don't use this code
                $use_discount_code = false;
            } else {
                //all okay
                $use_discount_code = true;
            }
            
            //update membership_user table.		
            if ( ! empty( $discount_code ) && ! empty( $use_discount_code ) ) {
                $sql = $wpdb->prepare("SELECT id FROM $wpdb->pmpro_discount_codes WHERE code = %s LIMIT 1", esc_sql( $discount_code ));
                $discount_code_id = $wpdb->get_var( $sql );
            } else {
                $discount_code_id = "";
            }
    
            $custom_level = array(
                'user_id'         => $user_id,
                'membership_id'   => $pmpro_level->id,
                'code_id'         => $discount_code_id,
                'initial_payment' => pmpro_round_price( $pmpro_level->initial_payment ),
                'billing_amount'  => pmpro_round_price( $pmpro_level->billing_amount ),
                'cycle_number'    => $pmpro_level->cycle_number,
                'cycle_period'    => $pmpro_level->cycle_period,
                'billing_limit'   => $pmpro_level->billing_limit,
                'trial_amount'    => pmpro_round_price( $pmpro_level->trial_amount ),
                'trial_limit'     => $pmpro_level->trial_limit,
                'startdate'       => $startdate,
                'enddate'         => $enddate
            );
    
            if ( pmpro_changeMembershipLevel( $custom_level, $user_id, 'changed' ) ) {
                //we're good
                //blank order for free levels
                if ( empty( $morder ) ) {
                    $morder                 = new MemberOrder();
                    $morder->InitialPayment = 0;
                    $morder->Email          = $bemail;
                    $morder->gateway        = 'free';
                    $morder->status			= 'success';
                    $morder = apply_filters( "pmpro_checkout_order_free", $morder );
                }
    
                //add an item to the history table, cancel old subscriptions
                if ( ! empty( $morder ) ) {
                    $morder->user_id       = $user_id;
                    $morder->membership_id = $pmpro_level->id;
                    $morder->saveOrder();
                }
    
                //update the current user
                global $current_user;
                if ( ! $current_user->ID && $user->ID ) {
                    $current_user = $user;
                } //in case the user just signed up
                pmpro_set_current_user();
    
                //add discount code use
                if ( $discount_code && $use_discount_code ) {
                    if ( ! empty( $morder->id ) ) {
                        $code_order_id = $morder->id;
                    } else {
                        $code_order_id = "";
                    }
    
                    $sql = "INSERT INTO $wpdb->pmpro_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES(%s, %s, %d, %s)";
                    $sql = $wpdb->prepare($sql,$discount_code_id, $user_id, intval( $code_order_id ), current_time( "mysql" ));
                    $wpdb->query( $sql );
                    
                    do_action( 'pmpro_discount_code_used', $discount_code_id, $user_id, $code_order_id );
                }
    
                //save billing info ect, as user meta
                $meta_keys   = array(
                    "pmpro_CardType",
                    "pmpro_AccountNumber",
                    "pmpro_ExpirationMonth",
                    "pmpro_ExpirationYear",
                );
                $meta_values = array(
                    $CardType,
                    hideCardNumber( $AccountNumber ),
                    $ExpirationMonth,
                    $ExpirationYear,
                );
    
                // Check if firstname and last name fields are set.
                if ( ! empty( $bfirstname ) || ! empty( $blastname ) ) {
                    $meta_keys = array_merge( $meta_keys, array(
                        "pmpro_bfirstname",
                        "pmpro_blastname",
                    ) );
    
                    $meta_values = array_merge( $meta_values, array(
                        $bfirstname,
                        $blastname,
                    ) );
                }
    
                // Check if billing details are available, if not adjust the arrays.
                if ( ! empty( $baddress1 ) ) {
                    $meta_keys = array_merge( $meta_keys, array(
                        "pmpro_baddress1",
                        "pmpro_baddress2",
                        "pmpro_bcity",
                        "pmpro_bstate",
                        "pmpro_bzipcode",
                        "pmpro_bcountry",
                        "pmpro_bphone",
                        "pmpro_bemail",
                    ) );
    
                    $meta_values = array_merge( $meta_values, array(
                        $baddress1,
                        $baddress2,
                        $bcity,
                        $bstate,
                        $bzipcode,
                        $bcountry,
                        $bphone,
                        $bemail,
                    ) );
                }
    
                pmpro_replaceUserMeta( $user_id, $meta_keys, $meta_values );
    
                //save first and last name fields
                if ( ! empty( $bfirstname ) ) {
                    $old_firstname = get_user_meta( $user_id, "first_name", true );
                    if ( empty( $old_firstname ) ) {
                        update_user_meta( $user_id, "first_name", $bfirstname );
                    }
                }
                if ( ! empty( $blastname ) ) {
                    $old_lastname = get_user_meta( $user_id, "last_name", true );
                    if ( empty( $old_lastname ) ) {
                        update_user_meta( $user_id, "last_name", $blastname );
                    }
                }
    
                if( $pmpro_level->expiration_period == 'Hour' ){
                    update_user_meta( $user_id, 'pmpro_disable_notifications', true );
                }
    
                //show the confirmation
                $ordersaved = true;
    
                //hook
                do_action( "pmpro_after_checkout", $user_id, $morder );    //added $morder param in v2.0
    
                $sendemails = apply_filters( "pmpro_send_checkout_emails", true);
        
                if($sendemails) { // Send the emails only if the flag is set to true
    
                    //setup some values for the emails
                    if ( ! empty( $morder ) ) {
                        $invoice = new MemberOrder( $morder->id );
                    } else {
                        $invoice = null;
                    }
                    $current_user->membership_level = $pmpro_level; //make sure they have the right level info
    
                    //send email to member
                    $pmproemail = new PMProEmail();
                    $pmproemail->sendCheckoutEmail( $current_user, $invoice );
    
                    //send email to admin
                    $pmproemail = new PMProEmail();
                    $pmproemail->sendCheckoutAdminEmail( $current_user, $invoice );
                }
    
                //CUSTOMIZE RESPONSE
                if(empty($current_user->membership_level))
                    $confirmation_message = "<p>" . __('Your payment has been submitted. Your membership will be activated shortly.', 'paid-memberships-pro' ) . "</p>";
                else
                    $confirmation_message = "<p>" . sprintf(__('Thank you for your membership to %s. Your %s membership is now active.', 'paid-memberships-pro' ), get_bloginfo("name"), $current_user->membership_level->name) . "</p>";

                //confirmation message for this level
                $sqlQuery = $wpdb->prepare("SELECT l.confirmation FROM $wpdb->pmpro_membership_levels l LEFT JOIN $wpdb->pmpro_memberships_users mu ON l.id = mu.membership_id WHERE mu.status = 'active' AND mu.user_id = %s LIMIT 1", $current_user->ID);
                $level_message = $wpdb->get_var($sqlQuery);
                if(!empty($level_message))
                    $confirmation_message .= "\n" . stripslashes($level_message) . "\n";

                if(!empty($invoice) && !empty($invoice->id)) { 
                    $invoice->getUser();
                    $invoice->getMembershipLevel();

                    $confirmation_message .= "<p>" . sprintf(__('Below are details about your membership account and a receipt for your initial membership invoice. A welcome email with a copy of your initial membership invoice has been sent to %s.', 'paid-memberships-pro' ), $invoice->user->user_email) . "</p>";

                    // Check instructions
                    if ( $invoice->gateway == "check" && ! pmpro_isLevelFree( $invoice->membership_level ) ) {
                        $confirmation_message .= '<div class="' . pmpro_get_element_class( 'pmpro_payment_instructions' ) . '">' . wpautop( wp_unslash( pmpro_getOption("instructions") ) ) . '</div>';
                    }
                    $confirmation_message = apply_filters("pmpro_confirmation_message", $confirmation_message, $invoice);
                }

                return [
                    'confirmation_message' => $confirmation_message,
                    'invoice' => $invoice
                ];
            } else {
    
                //uh oh. we charged them then the membership creation failed
    
                // test that the order object contains data
                $test = (array) $morder;
                if ( ! empty( $test ) && $morder->cancel() ) {
                    $pmpro_msg = __( "IMPORTANT: Something went wrong during membership creation. Your credit card authorized, but we cancelled the order immediately. You should not try to submit this form again. Please contact the site owner to fix this issue.", 'paid-memberships-pro' );
                    $morder    = null;
                } else {
                    $pmpro_msg = __( "IMPORTANT: Something went wrong during membership creation. Your credit card was charged, but we couldn't assign your membership. You should not submit this form again. Please contact the site owner to fix this issue.", 'paid-memberships-pro' );
                }

                return parent::sendError($pmpro_msgt, $pmpro_msg, 400);	
            }
        }
    }
}

new FlutterPaidMembershipsPro;