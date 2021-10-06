<?php
if (class_exists('FlutterUserAddEdit')) {
    return;
}

class FlutterUserAddEdit
{
    protected $is_public = TRUE;
    protected $user_id = '';
    protected $type = 'create';//create or edit
    protected $action = '';// form action (url)
    protected $user_data = array();
    protected $register_metas = array();
    protected $errors = false;
    protected $register_fields = '';
    protected $register_template = 'ihc-register-1';
    protected $print_errors = array();
    protected $required_fields = array();
    protected $global_css = '';
    protected $global_js = '';
    protected $order_id = 0;
    protected $shortcodes_attr = array();
    protected $set_password_auto = FALSE;
    protected $send_password_via_mail = FALSE;
    private $tos = false;
    private $captcha = false;
    private $disabled_submit_form = '';
    private $bank_transfer_message = FALSE;
    private $display_type = 'display_admin';
    private $coupon = '';
    private $show_sm = FALSE;
    private $payment_gateway = '';
    private $current_level = -1;
    private $exception_fields = array();
    private $taxes_enabled = FALSE;
    private $show_taxes = FALSE;
    private $payment_available_after_excluded_by_lid = array();
    private $rewrite_payment_gateway = TRUE;
    private $preview = FALSE;
    private $authorize_txn_id = FALSE;
    private $is_modal = false;

    /////////
    public function __construct()
    {
        /*
         * @param none
         * @return none
         */
        ///////////////set payment gateway
        if (!empty($_REQUEST['ihc_payment_gateway'])) {
            $this->payment_gateway = $_REQUEST['ihc_payment_gateway'];
            $this->rewrite_payment_gateway = FALSE;
            if (isset($_REQUEST['ihc_payment_gateway_radio'])) {
                unset($_REQUEST['ihc_payment_gateway_radio']);
            }
        } else {
            //DEFAULT
            $this->payment_gateway = get_option('ihc_payment_selected');
        }
    }

    ////////
    public function setVariable($arr = array())
    {
        /*
         * set the input variables
         * @param array
         * @return none
         */
        if (count($arr)) {
            foreach ($arr as $k => $v) {
                $this->$k = $v;
            }
        }
        if ($this->type == 'create') {
            $this->display_type = 'display_public_reg';
            if ($this->is_modal) {
                $this->display_type = 'display_on_modal';
            }
        } else {
            $this->display_type = 'display_public_ap';
        }
        /// TAXES
        $taxes_settings = ihc_return_meta_arr('ihc_taxes_settings');
        $this->taxes_enabled = $taxes_settings['ihc_enable_taxes'];
        $this->show_taxes = $taxes_settings['ihc_show_taxes'];


        /////SET CURRENT LEVEL (lid can be in get)
        if ($this->type == 'create' && !$this->disabled_submit_form) {
            if (isset($this->shortcodes_attr['level']) && $this->shortcodes_attr['level'] !== FALSE) {
                $standard_level = $this->shortcodes_attr['level'];
            } else {
                $standard_level = get_option('ihc_register_new_user_level');
            }
        } else {
            $standard_level = '';
        }
        if (isset($_REQUEST['lid']) || $standard_level) {
            $this->current_level = isset($_REQUEST['lid']) ? $_REQUEST['lid'] : $standard_level;
        }

        if ($this->rewrite_payment_gateway && $this->current_level && ihc_is_magic_feat_active('level_restrict_payment')) {
            /// set default payment type
            $this->payment_gateway = Ihc_Db::get_default_payment_gateway_for_level($this->current_level, $this->payment_gateway);
            $exclude_payments_available = Ihc_Db::get_excluded_payment_types_for_level_id($this->current_level);
            if ($exclude_payments_available) {
                $exclude_array = explode(',', $exclude_payments_available);
                $this->payment_available_after_excluded_by_lid = ihc_get_active_payments_services();
                foreach ($exclude_array as $ek => $ev) {
                    if (isset($this->payment_available_after_excluded_by_lid[$ev])) {
                        unset($this->payment_available_after_excluded_by_lid[$ev]);
                    }
                }
                if (!isset($this->payment_available_after_excluded_by_lid[$this->payment_gateway])) {
                    $this->payment_gateway = key($this->payment_available_after_excluded_by_lid);
                }
            }
        }
    }

    private function set_register_fields()
    {
        /*
         * @param none
         * @return none
         */
        $this->register_fields = ihc_get_user_reg_fields();//register fields
        ksort($this->register_fields);

        /// REMOVE USERNAME FROM EDIT
        if ($this->type == 'edit') {
            $key = ihc_array_value_exists($this->register_fields, 'user_login', 'name');
            if ($key !== FALSE) {
                unset($this->register_fields[$key]);
            }
        }


        /// REMOVE COUPUN FROM PUBLIC - EDIT, ADMIN - ADD NEW, ADMIN - EDIT
        if ($this->type != 'create') {
            $key = ihc_array_value_exists($this->register_fields, 'ihc_coupon', 'name');
            if ($key !== FALSE) {
                unset($this->register_fields[$key]);
            }
        }

        $key = ihc_array_value_exists($this->register_fields, 'ihc_invitation_code_field', 'name');
        if ($key !== FALSE && (!ihc_is_magic_feat_active('invitation_code') || !Ihc_Db::invitation_code_does_exist_codes() || $this->type != 'create')) {
            /// remove invitation code
            unset($this->register_fields[$key]);
        }

    }

    /////////
    public function form()
    {
        /*
         * @param none
         * @return string
         */

        /*extra fields that must be transalted:*/
        $extraFields[] = array(
            esc_html__("Confirm Password", 'ihc'),
            esc_html__("Last Name", 'ihc'),
            esc_html__("First Name", 'ihc'),
            esc_html__("Username", 'ihc'),
            esc_html__("Email", 'ihc'),
            esc_html__("Confirm Email", 'ihc'),
            esc_html__("Website", 'ihc'),
            esc_html__("Password", 'ihc'),
            esc_html__("Biographical Info", 'ihc')
        );
        /**/

        $this->userdata();//settings the user data
        $this->set_register_fields();
        $level_data = ihc_get_level_by_id($this->current_level);

        $str = '';
        $i = 0;
        if ($this->register_template == 'ihc-register-6' || $this->register_template == 'ihc-register-11'
            || $this->register_template == 'ihc-register-12' || $this->register_template == 'ihc-register-13') {
            $count_reg = $this->count_register_fields();
        }

        foreach ($this->register_fields as $v) {
            if (isset($v[$this->display_type]) && $v[$this->display_type] > 0) {
                $i++;
                if ($this->register_template == 'ihc-register-6' || $this->register_template == 'ihc-register-11'
                    || $this->register_template == 'ihc-register-12' || $this->register_template == 'ihc-register-13') {
                    if ($i == 1) {
                        $str .= '<div class="ihc-register-col">';
                    }
                    if ($i - 1 == ceil($count_reg / 2)) {
                        $str .= '</div><div class="ihc-register-col ihc-register-secundary-col">';
                    }
                }
                // TEST CUSTOM FIELD - LEVELS RELATION
                if (isset($v['target_levels']) && $v['target_levels'] != '') {
                    $field_target_levels = explode(',', $v['target_levels']);
                    if ($field_target_levels && is_array($field_target_levels)) {
                        foreach ($field_target_levels as $temp_level_key => $temp_level) {
                            if (!Ihc_Db::does_level_exists($temp_level)) {
                                unset($field_target_levels[$temp_level_key]);
                            }
                        }
                    }
                    if ($this->type == 'create') {
                        if ($field_target_levels && !in_array($this->current_level, $field_target_levels)) {
                            continue;
                        }
                    } else {
                        /// do stuff
                        $user_levels_temp = explode(',', $this->user_data['ihc_user_levels']);
                        if ($user_levels_temp) {

                            $k = 0;
                            foreach ($user_levels_temp as $lid_temp) {
                                if (in_array($lid_temp, $field_target_levels)) {
                                    $k = 1;
                                }
                            }
                            if ($k == 0)
                                continue;
                        }

                    }
                }

                switch ($v['name']) {
                    case 'tos':
                        if ($this->tos) {
                            $disp_tos = $this->print_tos($v);
                            if ($disp_tos != '') {
                                $str .= $disp_tos;
                                $this->required_fields[] = array('name' => 'tos', 'type' => 'checkbox');
                                if (!empty($this->print_errors['tos'])) {
                                    $str .= '<div class="ihc-register-notice">' . $this->print_errors['tos'] . '</div>';
                                }
                            }
                        }
                        break;
                    case 'recaptcha':
                        if ($this->captcha) {
                            $disp_captcha = $this->print_captcha($v);
                            if ($disp_captcha != '') {
                                $str .= $disp_captcha;
                                if (!empty($this->print_errors['captcha'])) {
                                    $str .= '<div class="ihc-register-notice">' . $this->print_errors['captcha'] . '</div>';
                                }
                            }
                        }
                        break;
                    case 'ihc_social_media':
                        if ($this->type == 'create') {
                            $str .= ihc_print_social_media_icons('register');
                            $this->show_sm = TRUE;
                        } //else {
                        //continue ;
                        //}
                        break;
                    case 'payment_select':
                        $payments_available = ihc_get_active_payments_services();
                        if ($this->type == 'create' && $this->current_level != -1 && $level_data['payment_type'] == 'payment'
                            && !empty($payments_available) && count($payments_available) > 1) {

                            if (!empty($level_data['access_type']) && $level_data['access_type'] == 'regular_period') {
                                $is_reccurence = 1;
                            } else {
                                $is_reccurence = 0;
                            }

                            $default_payment = (empty($this->payment_gateway)) ? get_option('ihc_payment_selected') : $this->payment_gateway;

                            $continue = true;
                            if (!empty($this->payment_available_after_excluded_by_lid)) {
                                $payments_available = $this->payment_available_after_excluded_by_lid;
                                if (count($payments_available) < 2) {
                                    //continue; /// single payment gateway ... don't work in some php versions
                                    $continue = false;
                                }
                            }

                            if ($continue) {

                                $str .= ihc_print_payment_select($default_payment, $v, $payments_available, $is_reccurence, $v['req']);

                                if (!empty($payments_available['stripe'])) {
                                    $include_stripe = TRUE;
                                }
                                if (!empty($payments_available['authorize'])) {
                                    $include_authorize = TRUE;
                                }
                                if (!empty($payments_available['braintree'])) {
                                    $include_braintree = TRUE;
                                }

                                if (!empty($v['req'])) {
                                    $this->required_fields[] = array('name' => 'ihc_payment_gateway', 'type' => 'hidden');
                                }

                            }

                        }
                        break;
                    case 'ihc_optin_accept':
                    case 'ihc_memberlist_accept':
                        $disabled = '';
                        if ($this->type == 'edit' && $v['name'] == 'user_login') {
                            $disabled = 'disabled';
                        }
                        //FORM FIELD
                        $parent_id = 'ihc_reg_' . $v['type'] . '_' . rand(1, 10000);
                        $temp_type_class = 'iump-form-' . $v['type'];
                        $str .= '<div class="iump-form-line-register ' . $temp_type_class . '" id="' . $parent_id . '">';
                        if ($v['req']) {
                            $str .= '<span class="ihc-required-sign">*</span>';
                        }
                        if (isset($v['native_wp']) && $v['native_wp']) {
                            $labelInside = esc_html__($v['label'], 'ihc');
                        } else {
                            $labelInside = ihc_correct_text($v['label']);
                        }

                        $val = '';
                        if (isset($this->user_data[$v['name']])) {
                            $val = $this->user_data[$v['name']];
                        }
                        if (empty($val) && $v['type'] == 'plain_text') { //maybe it's plain text
                            $val = $v['plain_text_value'];
                        }
                        if ($v['name'] === 'ihc_optin_accept' && $v['ihc_optin_accept_checked'] === '1') {
                            $val = 1;
                        }
                        if ($v['name'] === 'ihc_memberlist_accept' && $v['ihc_memberlist_accept_checked'] === '1') {
                            $val = 1;
                        }
                        $multiple_values = FALSE;
                        if (isset($v['values']) && $v['values']) {
                            //is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
                            $multiple_values = ihc_from_simple_array_to_k_v($v['values']);
                        }

                        if (empty($v['sublabel'])) {
                            $v['sublabel'] = '';
                        }

                        if (empty($v['class'])) {
                            $v['class'] = '';
                        }

                        $str .= indeed_create_form_element(array('type' => $v['type'], 'name' => $v['name'], 'value' => $val,
                            'disabled' => $disabled, 'multiple_values' => $multiple_values,
                            'user_id' => $this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'], 'form_type' => $this->type,
                            'is_public' => true, 'ihc_form_type' => $this->type, 'label' => $labelInside));
                        $str .= '</div>';
                        if (!empty($v['req'])) {
                            $this->required_fields[] = array('name' => $v['name'], 'type' => $v['type']);
                        }

                        break;
                    default:
                        $str .= $this->print_fields($v);
                        break;
                }//end of switch
            }
        }/// end of foreach

        if ($this->register_template == 'ihc-register-6' || $this->register_template == 'ihc-register-11'
            || $this->register_template == 'ihc-register-12' || $this->register_template == 'ihc-register-13') {
            $str .= '</div>';
        }

        if (!$this->disabled_submit_form) {
            if (!empty($this->payment_gateway) && $this->type == 'create') {
                $the_selected_payment = (ihc_check_payment_available($this->payment_gateway)) ? $this->payment_gateway : '';
                $str .= '<input type="hidden" name="ihc_payment_gateway" value="' . $the_selected_payment . '" />';
            }
            /*******************************PUBLIC****************************/
            //SPECIAL PAYMENTS (authorize, stripe)
            if (isset($this->current_level) && $level_data !== false && $level_data['payment_type'] == 'payment') {
                if (($this->payment_gateway == 'authorize' || !empty($include_authorize)) && ihc_check_payment_available('authorize') && isset($level_data['access_type']) && $level_data['access_type'] == 'regular_period') {
                    //AUTHORIZE RECCURING
                    if (!class_exists('ihcAuthorizeNet')) {
                        require_once IHC_PATH . 'classes/PaymentGateways/ihcAuthorizeNet.class.php';
                    }
                    $auth_pay = new ihcAuthorizeNet();
                    $display_authorize = ($this->payment_gateway == 'authorize') ? '' : 'ihc-display-none';
                    $str .= '<div id="ihc_authorize_r_fields" class="' . $display_authorize . '">';
                    $str .= '<div class="ihc_payment_register_wrapper">';
                    $str .= '<div class="ihc_payment_details">' . esc_html__('Authorize.Net details', 'ihc') . '</div>';
                    $str .= $auth_pay->payment_fields();
                    $str .= '</div>';
                    $str .= '</div>';
                    if (!empty($this->print_errors) && !empty($this->print_errors['payment_gateway-authorize'])) {
                        $str .= '<div class="ihc-register-notice">' . $this->print_errors[0] . '</div>';
                    }
                }

                if (($this->payment_gateway == 'stripe' || !empty($include_stripe)) && ihc_check_payment_available('stripe')) {

                    if (isset($_POST['stripeEmail']) && isset($_POST['stripeToken'])) {
                        //already have a token
                        $str .= indeed_create_form_element(array('type' => 'hidden', 'name' => 'stripeToken', 'value' => $_POST['stripeToken']));
                        $str .= indeed_create_form_element(array('type' => 'hidden', 'name' => 'stripeEmail', 'value' => $_POST['stripeEmail']));
                    } else {
                        if (!class_exists('ihcStripe')) {
                            require_once IHC_PATH . 'classes/PaymentGateways/ihcStripe.class.php';
                        }
                        $payment_obj = new ihcStripe();
                        $bind = ($this->payment_gateway == 'stripe') ? TRUE : FALSE;
                        $str .= $payment_obj->payment_fields($this->current_level, $bind);
                    }
                }

                if (($this->payment_gateway == 'braintree' || !empty($include_braintree)) && ihc_check_payment_available('braintree')) {
                    require_once IHC_PATH . 'classes/PaymentGateways/Ihc_Braintree.class.php';
                    $braintree = new Ihc_Braintree();
                    $display_braintree = ($this->payment_gateway == 'braintree') ? '' : 'ihc-display-none';
                    $str .= '<div id="ihc_braintree_r_fields" class="' . $display_braintree . '">';
                    $str .= '<div class="ihc_payment_register_wrapper">';
                    $str .= '<div class="ihc_payment_details">' . esc_html__('Braintree details', 'ihc') . '</div>';
                    $str .= $braintree->get_form();
                    $str .= '</div>';
                    $str .= '</div>';
                }
            }

            //ACTIONS
            if ($this->type == 'edit') {
                $str .= indeed_create_form_element(array('type' => 'hidden', 'name' => 'ihcaction', 'value' => 'update'));
            } else {
                $str .= indeed_create_form_element(array('type' => 'hidden', 'name' => 'ihcaction', 'value' => 'register'));
            }
            //LEVELS
            if ($this->current_level != -1) {
                $str .= indeed_create_form_element(array('type' => 'hidden', 'name' => 'lid', 'value' => $this->current_level));
            }
        }

        if ($this->register_template == 'ihc-register-7') {
            $str .= '<div class="impu-temp7-row">';
        }

        $str = apply_filters('ump_before_submit_form', $str, true, $this->type); // str, isPublic, type

        $str .= '<div class="iump-submit-form">';

        $bttn_submit_class = 'button button-primary button-large';
        if ($this->register_template != 'ihc-register-1' && $this->preview) {
            $bttn_submit_class = '';
        }
        if ($this->register_template == 'ihc-register-14') {
            $str .= '<div class="iump-register-row-left">';
        }
        if ($this->type == 'create') {
            $str .= indeed_create_form_element(array('type' => 'submit', 'name' => 'Submit', 'value' => esc_html__('Register', 'ihc'),
                'class' => $bttn_submit_class, 'id' => 'ihc_submit_bttn', 'disabled' => $this->disabled_submit_form));
        } else {
            $str .= indeed_create_form_element(array('type' => 'submit', 'name' => 'Update', 'value' => esc_html__('Save Changes', 'ihc'),
                'class' => $bttn_submit_class, 'id' => 'ihc_submit_bttn', 'disabled' => $this->disabled_submit_form));
        }
        if ($this->register_template == 'ihc-register-14') {
            $str .= '</div>';

            if ($this->type == 'create') {
                $str .= '<div class="iump-register-row-right">';
                $pag_id = get_option('ihc_general_login_default_page');
                if ($pag_id !== FALSE) {
                    $login_page = get_permalink($pag_id);
                    if (!$login_page) {
                        $login_page = get_home_url();
                    }
                    $str .= '<div class="ihc-login-link"><a href="' . $login_page . '">' . esc_html__('LogIn', 'ihc') . '</a></div>';
                }
                $str .= '</div>';
            }
            $str .= '<div class="iump-clear"></div>';
        }
        $str .= '</div>';

        if ($this->register_template == 'ihc-register-7') {
            $str .= '</div>';
        }

        $str .= $this->social_register_request_data();

        if (count($this->exception_fields) > 0) {
            $str .= '<input type="hidden" name="ihc_exceptionsfields" id="ihc_exceptionsfields" value="' . implode(',', $this->exception_fields) . '" />';
        }

        //wrapp it all in a form
        if ($this->type == 'edit') {
            $form_detail = ' name="edituser" id="edituser" class="ihc-form-create-edit" enctype="multipart/form-data" ';
        } else {
            $form_detail = ' name="createuser" id="createuser" class="ihc-form-create-edit" enctype="multipart/form-data" ';
        }
        $str .= $this->printNonce();
        $umpFormType = 'register';
        if ($this->is_modal) {
            $umpFormType = 'modal';
        } else if ($this->type == 'edit') {
            $umpFormType = 'edit';
        }
        $str .= '<input type="hidden" name="ihcFormType" value="' . $umpFormType . '" />';

        $str = indeed_form_start($this->action, 'post', $form_detail) . $str . indeed_form_end();

        //SOCIAL LOGGER
        if ($this->type == 'create' && $this->show_sm) {
            $str .= $this->ihc_social_form();
        }

        //MESSAGE ABOUT LEVEL
        $str .= $this->add_level_details_on_register_form();

        //CSS
        $this->global_css .= get_option('ihc_register_custom_css'); //add custom css to global css
        if ($this->global_css !== '') {
            wp_register_style('dummy-handle', false);
            wp_enqueue_style('dummy-handle');
            wp_add_inline_style('dummy-handle', stripslashes($this->global_css));
        }
        //AJAX CHECK FIELDS VALUES (ONLY FOR PUBLIC REGISTER)
        $js = '';
        if ($this->type == 'create') {
            $js .= 'var ihc_req_fields_arr = [];';
            $js .= 'jQuery( window ).on( "load", function(){';

            /// require fields
            foreach ($this->required_fields as $req_field) {
                if (in_array($req_field['type'], array('text', 'textarea', 'number', 'password', 'conditional_text'))) {
                    $js .= 'jQuery(".ihc-form-create-edit [name=' . $req_field['name'] . ']").on("blur", function(){
							ihcRegisterCheckViaAjax("' . $req_field['name'] . '");
						});';
                }
                $js .= 'ihc_req_fields_arr.push("' . $req_field['name'] . '");
					';
            }

            if (empty($this->is_modal)) {
                $js .= 'jQuery(".ihc-form-create-edit").on("submit", function() {
								if (window.must_submit==1){
									window.must_submit = 0;
									return true;
								} else {
									ihcRegisterCheckViaAjaxRec(ihc_req_fields_arr);
									return false;
								}
							});';
            } else {
                $js .= 'jQuery(".ihc-form-create-edit").on("submit", function() {
								if (window.must_submit==1){
									//window.must_submit = 0;
									return true;
								} else {
									ihcRegisterCheckViaAjaxRec(ihc_req_fields_arr);
									return false;
								}
							});';
            }


            $js .= '});';
        }
        if (!empty($this->global_js)) {
            $js .= $this->global_js;
        }

        wp_add_inline_script('ihc-public-dynamic', $js);
        wp_enqueue_script('ihc-public-dynamic');

        return $str;
    }


    /////////
    public function userdata()
    {
        //setting $user_data for current user
        if ($this->user_id) {
            /// EDIT
            $data = get_userdata($this->user_id);
            $user_fields = ihc_get_user_reg_fields();
            if ($data) {
                foreach ($user_fields as $user_field) {
                    $name = $user_field['name'];
                    if ($user_field['native_wp'] == 1) {
                        //native wp field, get value from get_userdata ( $data object )
                        if (isset($data->$name) && $data->$name) {
                            $this->user_data[$name] = $data->$name;
                        }
                    } else {
                        //custom field, get value from get_user_meta()
                        $this->user_data[$name] = get_user_meta($this->user_id, $name, true);
                    }
                }
            }
            //user wp role
            if (isset($data->roles[0])) {
                $this->user_data['role'] = $data->roles[0];
            }
            ///user levels
            $this->user_data['ihc_user_levels'] = \Indeed\Ihc\UserSubscriptions::getAllForUserAsList($this->user_id);

            //remove coupon data
            unset($this->user_data['ihc_coupon']);
        } else {
            /// CREATE
            $user_fields = ihc_get_user_reg_fields();
            global $ihc_stored_form_values;

            foreach ($user_fields as $user_field) {
                $name = $user_field['name'];
                $this->user_data[$name] = '';
                if (isset($_POST[$name])) {
                    /// prev value from submit form
                    $this->user_data[$name] = $_POST[$name];
                } else if (isset($ihc_stored_form_values[$name])) {
                    /// prev value from submit form, before register with sm
                    $this->user_data[$name] = $ihc_stored_form_values[$name];
                }
            }
            $this->user_data['ihc_user_levels'] = '';
            $this->user_data['role'] = '';
        }
    }

    /**
     * @param none
     * @return string
     */
    protected function printNonce()
    {
        $nonce = wp_create_nonce('ihc_user_add_edit_nonce');
        return "<input type='hidden' name='ihc_user_add_edit_nonce' value='$nonce' />";
    }

    private function edit_ap_check_conditional_logic($field_data = array())
    {
        $value = get_user_meta($this->user_id, $field_data['conditional_logic_corresp_field'], TRUE);

        if ($field_data['conditional_logic_cond_type'] == 'has') {
            //has value
            if ($field_data['conditional_logic_corresp_field_value'] == $value) {
                return 1;
            }
        } else {
            //contain value
            if (is_string($value) && is_string($field_data['conditional_logic_corresp_field_value'])
                && strpos($value, $field_data['conditional_logic_corresp_field_value']) !== FALSE) {
                return 1;
            }
        }

        return 0;
    }

    private function check_for_conditional_logic($field_arr, $field_id)
    {
        /*
         * @param string, string
         * @return none
         */
        if (!empty($field_arr['conditional_logic_corresp_field']) && $field_arr['conditional_logic_corresp_field'] != -1) {
            //so this field is correlated with another

            ////Js ACTION
            $key = ihc_array_value_exists($this->register_fields, $field_arr['conditional_logic_corresp_field'], 'name');
            if ($key !== FALSE && !empty($this->register_fields[$key]['type'])) {
                $show = ($field_arr['conditional_logic_show'] == 'yes') ? 1 : 0;

                if ($this->type == 'edit') {
                    if ($show) {
                        /// 'yes'
                        $no_on_edit = $this->edit_ap_check_conditional_logic($field_arr);
                    } else {
                        /// 'no'
                        $no_on_edit = !$this->edit_ap_check_conditional_logic($field_arr);
                    }
                }

                switch ($this->register_fields[$key]['type']) {
                    case 'text':
                    case 'textarea':
                    case 'number':
                    case 'password':
                    case 'date':
                    case 'conditional_text':
                    case 'unique_value_text':
                        $js_function = 'ihcAjaxCheckFieldConditionOnblurOnclick("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", ' . $show . ');';
                        $this->global_js .= '
								jQuery(".ihc-form-create-edit [name=' . $field_arr['conditional_logic_corresp_field'] . ']").on("blur", function(){
									' . $js_function . '
								});
							';
                        break;
                    case 'checkbox':
                        $js_function = 'ihcAjaxCheckOnClickFieldCondition("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", "checkbox", ' . $show . ');';
                        $this->global_js .= '
								jQuery(".ihc-form-create-edit [name=\'' . $field_arr['conditional_logic_corresp_field'] . '[]\'], .ihc-form-create-edit [name=' . $field_arr['conditional_logic_corresp_field'] . ']").on("click", function(){
									' . $js_function . '
								});
							';
                        break;
                    case 'radio':
                        $js_function = 'ihcAjaxCheckOnClickFieldCondition("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", "radio", ' . $show . ');';
                        $this->global_js .= '
								jQuery(".ihc-form-create-edit [name=' . $field_arr['conditional_logic_corresp_field'] . ']").on("click", function(){
									' . $js_function . '
								});
							';
                        break;
                    case 'select':
                        $js_function = 'ihcAjaxCheckFieldConditionOnblurOnclick("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", ' . $show . ');';
                        $this->global_js .= '
								jQuery(".ihc-form-create-edit [name=' . $field_arr['conditional_logic_corresp_field'] . ']").on("change", function(){
									' . $js_function . '
								});
							';
                        break;
                    case 'multi_select':
                        $js_function = 'ihcAjaxCheckOnChangeMultiselectFieldCondition("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", ' . $show . ');';
                        $this->global_js .= '
								jQuery(".ihc-form-create-edit [name=\'' . $field_arr['conditional_logic_corresp_field'] . '[]\']").on("change", function(){
									' . $js_function . '
								});
							';
                        break;
                }
                if (!empty($js_function)) {
                    $this->global_js .= 'jQuery( window ).on( "load", function(){' . $js_function . '});';
                }
            }

            //conditional logic & required => add new exception
            if ($field_arr['req']) {
                $this->exception_fields[] = $field_arr['name'];
            }

            if (empty($show) || !empty($no_on_edit)) {
                //// hide the conditional logic only for public create
                //we must hide this field and show only when correlated field it's completed with desired value
                $this->global_css .= "#$field_id{display: none;}";
            }
        }
    }

    protected function print_fields($v = array())
    {
        /*
         * @param array
         * @return string
         */
        $str = '';
        $disabled = '';
        $placeholder = '';
        $callback = '';
        if ($this->type == 'edit' && $v['name'] == 'user_login') {
            $disabled = 'disabled';
        }
        $parent_id = 'ihc_reg_' . $v['type'] . '_' . rand(1, 10000);


        $this->check_for_conditional_logic($v, $parent_id);

        if ($v['type'] == 'date') {
            if (!empty($v['req']) || $v['type'] == 'conditional_text') {
                $callback = 'ihcRegisterCheckViaAjax("' . $v['name'] . '");'; /// require field
                $this->required_fields[] = array('name' => $v['name'], 'type' => $v['type']);
            }
        } else {

            /// DYNAMIC PRICE MODULE
            if ($v['type'] == 'ihc_dynamic_price') {
                if (!ihc_is_magic_feat_active('level_dynamic_price')) {
                    return '';
                }
                $temp_dynamic_settings = ihc_return_meta_arr('level_dynamic_price');//getting metas
                if (empty($temp_dynamic_settings['ihc_level_dynamic_price_levels_on'][$this->current_level])) {
                    return '';
                }
            }
            /// DYNAMIC PRICE MODULE

            /// REQUIRE INPUT
            if (!empty($v['req']) || $v['type'] == 'conditional_text') {
                $this->required_fields[] = array('name' => $v['name'], 'type' => $v['type']);
            }
            /// REQUIRE INPUT

        }

        switch ($this->register_template) {
            case 'ihc-register-3':
            case 'ihc-register-8':
            case 'ihc-register-9':
                //////// FORM FIELD
                $temp_type_class = 'iump-form-' . $v['type'];
                $str .= '<div class="iump-form-line-register ' . $temp_type_class . '" id="' . $parent_id . '">';
                if ($v['type'] == 'text' || $v['type'] == 'password') {
                    if ($v['req']) {
                        $placeholder .= '*';
                    }
                    if (isset($v['native_wp']) && $v['native_wp']) {
                        $placeholder .= esc_html__($v['label'], 'ihc');
                    } else {
                        $placeholder .= ihc_correct_text($v['label']);
                    }
                } else {
                    $str .= '<label class="iump-labels-register">';
                    if ($v['req']) {
                        $str .= '<span class="ihc-required-sign">*</span>';
                    }
                    if (isset($v['native_wp']) && $v['native_wp']) {
                        $str .= esc_html__($v['label'], 'ihc');
                    } else {
                        $str .= ihc_correct_text($v['label']);
                    }
                    $str .= '</label>';
                }
                $val = '';
                if (isset($this->user_data[$v['name']]) && !empty($this->user_data[$v['name']])) {
                    $val = $this->user_data[$v['name']];
                } elseif (isset($_POST[$v['name']])) {
                    $val = $_POST[$v['name']];
                }
                if (empty($val) && $v['type'] == 'plain_text') { //maybe it's plain text
                    $val = $v['plain_text_value'];
                }

                $multiple_values = FALSE;
                if (isset($v['values']) && $v['values']) {
                    //is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
                    $multiple_values = ihc_from_simple_array_to_k_v($v['values']);
                }

                if (empty($v['sublabel'])) {
                    $v['sublabel'] = '';
                }
                if (empty($v['class'])) {
                    $v['class'] = '';
                }

                $str .= indeed_create_form_element(array('type' => $v['type'], 'name' => $v['name'], 'value' => $val,
                        'disabled' => $disabled, 'placeholder' => $placeholder, 'multiple_values' => $multiple_values,
                        'user_id' => $this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'],
                        'callback' => $callback, 'form_type' => $this->type, 'lid' => $this->current_level,
                        'is_public' => true, 'ihc_form_type' => $this->type,
                        'is_modal' => $this->is_modal)
                );
                if (!empty($this->print_errors[$v['name']])) {
                    $str .= '<div class="ihc-register-notice">' . $this->print_errors[$v['name']] . '</div>';
                }
                $str .= '</div>';
                break;
            case 'ihc-register-4':
                //////// FORM FIELD
                $add_class = '';
                if ($v['type'] == 'select' || $v['type'] == 'multi_select' || $v['type'] == 'file' || $v['type'] == 'upload_image' || $v['type'] == 'ihc_country') {
                    $add_class = 'ihc-no-backs';
                }
                $temp_type_class = 'iump-form-' . $v['type'];
                $str .= '<div class="iump-form-line-register ' . $add_class . ' ' . $temp_type_class . '" id="' . $parent_id . '">';
                if ($v['type'] == 'text' || $v['type'] == 'password' || $v['type'] == 'unique_value_text' || $v['type'] == 'ihc_invitation_code_field' || $v['type'] == 'date') {
                    if ($v['req']) {
                        $placeholder .= '*';
                    }
                    if (isset($v['native_wp']) && $v['native_wp']) {
                        $placeholder .= esc_html__($v['label'], 'ihc');
                    } else {
                        $placeholder .= ihc_correct_text($v['label']);
                    }
                } else {
                    $str .= '<label class="iump-labels-register">';
                    if ($v['req']) {
                        $str .= '<span class="ihc-required-sign">*</span>';
                    }
                    if (isset($v['native_wp']) && $v['native_wp']) {
                        $str .= esc_html__($v['label'], 'ihc');
                    } else {
                        $str .= ihc_correct_text($v['label']);
                    }
                    $str .= '</label>';
                }
                $val = '';
                if (isset($this->user_data[$v['name']]) && !empty($this->user_data[$v['name']])) {
                    $val = $this->user_data[$v['name']];
                } elseif (isset($_POST[$v['name']])) {
                    $val = $_POST[$v['name']];
                }
                if (empty($val) && $v['type'] == 'plain_text') { //maybe it's plain text
                    $val = $v['plain_text_value'];
                }

                $multiple_values = FALSE;
                if (isset($v['values']) && $v['values']) {
                    //is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
                    $multiple_values = ihc_from_simple_array_to_k_v($v['values']);
                }

                if (empty($v['sublabel'])) {
                    $v['sublabel'] = '';
                }
                if (empty($v['class'])) {
                    $v['class'] = '';
                }

                $str .= indeed_create_form_element(array('type' => $v['type'], 'name' => $v['name'], 'value' => $val,
                    'disabled' => $disabled, 'placeholder' => $placeholder, 'multiple_values' => $multiple_values,
                    'user_id' => $this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'],
                    'callback' => $callback, 'form_type' => $this->type, 'lid' => $this->current_level,
                    'is_public' => true, 'ihc_form_type' => $this->type,
                    'is_modal' => $this->is_modal));
                if (!empty($this->print_errors[$v['name']])) {
                    $str .= '<div class="ihc-register-notice">' . $this->print_errors[$v['name']] . '</div>';
                }
                $str .= '</div>';
                break;

            case 'ihc-register-6':
                //////// FORM FIELD
                $temp_type_class = 'iump-form-' . $v['type'];
                $str .= '<div class="iump-form-line-register ' . $temp_type_class . '" id="' . $parent_id . '">';
                $str .= '<label class="iump-labels-register">';
                if ($v['req']) {
                    $str .= '<span class="ihc-required-sign">*</span>';
                }
                if (isset($v['native_wp']) && $v['native_wp']) {
                    $str .= esc_html__($v['label'], 'ihc');
                } else {
                    $str .= ihc_correct_text($v['label']);
                }
                $str .= '</label>';

                $val = '';
                if (isset($this->user_data[$v['name']]) && !empty($this->user_data[$v['name']])) {
                    $val = $this->user_data[$v['name']];
                } elseif (isset($_POST[$v['name']])) {
                    $val = $_POST[$v['name']];
                }
                if (empty($val) && $v['type'] == 'plain_text') { //maybe it's plain text
                    $val = $v['plain_text_value'];
                }

                $multiple_values = FALSE;
                if (isset($v['values']) && $v['values']) {
                    //is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
                    $multiple_values = ihc_from_simple_array_to_k_v($v['values']);
                }

                if (empty($v['sublabel'])) {
                    $v['sublabel'] = '';
                }

                if (empty($v['class'])) {
                    $v['class'] = '';
                }

                $str .= indeed_create_form_element(array('type' => $v['type'], 'name' => $v['name'], 'value' => $val,
                    'disabled' => $disabled, 'multiple_values' => $multiple_values,
                    'user_id' => $this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'],
                    'callback' => $callback, 'form_type' => $this->type, 'lid' => $this->current_level,
                    'is_public' => true, 'ihc_form_type' => $this->type,
                    'is_modal' => $this->is_modal));
                if (!empty($this->print_errors[$v['name']])) {
                    $str .= '<div class="ihc-register-notice">' . $this->print_errors[$v['name']] . '</div>';
                }
                $str .= '</div>';
                break;

            default:
                //////// FORM FIELD
                $temp_type_class = 'iump-form-' . $v['type'];
                $str .= '<div class="iump-form-line-register ' . $temp_type_class . '" id="' . $parent_id . '">';
                $str .= '<label class="iump-labels-register">';
                if ($v['req']) {
                    $str .= '<span class="ihc-required-sign">*</span>';
                }
                if (isset($v['native_wp']) && $v['native_wp']) {
                    $str .= esc_html__($v['label'], 'ihc');
                } else {
                    $str .= ihc_correct_text($v['label']);
                }
                $str .= '</label>';

                $val = '';
                if (isset($this->user_data[$v['name']]) && !empty($this->user_data[$v['name']])) {
                    $val = $this->user_data[$v['name']];
                } elseif (isset($_POST[$v['name']])) {
                    $val = $_POST[$v['name']];
                }

                if (empty($val) && $v['type'] == 'plain_text') { //maybe it's plain text
                    $val = $v['plain_text_value'];
                }

                $multiple_values = FALSE;
                if (isset($v['values']) && $v['values']) {
                    //is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
                    $multiple_values = ihc_from_simple_array_to_k_v($v['values']);
                }

                if (empty($v['sublabel'])) {
                    $v['sublabel'] = '';
                }

                if (empty($v['class'])) {
                    $v['class'] = '';
                }

                $str .= indeed_create_form_element(array('type' => $v['type'], 'name' => $v['name'], 'value' => $val,
                    'disabled' => $disabled, 'multiple_values' => $multiple_values,
                    'user_id' => $this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'],
                    'callback' => $callback, 'form_type' => $this->type, 'lid' => $this->current_level,
                    'is_public' => true,
                    'ihc_form_type' => $this->type,
                    'is_modal' => $this->is_modal));
                if (!empty($this->print_errors[$v['name']])) {
                    $str .= '<div class="ihc-register-notice">' . $this->print_errors[$v['name']] . '</div>';
                }
                $str .= '</div>';
                break;
        }
        return $str;
    }


    private function print_tos($v = array())
    {
        $str = '';
        $tos_msg = stripslashes(get_option('ihc_register_terms_c'));//getting tos message
        $tos_page_id = get_option('ihc_general_tos_page');
        $tos_link = get_permalink($tos_page_id);
        if (!$tos_msg || !$tos_page_id) {
            return '';
        }
        $view = new \Indeed\Ihc\IndeedView();
        $data = array(
            'class' => (empty($v['class'])) ? '' : $v['class'],
            'id' => 'ihc_tos_field_parent_' . rand(1, 1000),
            'tos_msg' => $tos_msg,
            'tos_link' => $tos_link,
            'tos_page_id' => $tos_page_id,
        );
        return $view->setTemplate(IHC_PATH . 'public/views/register-tos.php')->setContentData($data, true)->getOutput();
    }


    //////
    private function print_captcha($v = array())
    {
        $type = get_option('ihc_recaptcha_version');
        if ($type !== false && $type == 'v3') {
            $key = get_option('ihc_recaptcha_public_v3');
        } else {
            $key = get_option('ihc_recaptcha_public');
        }

        if (empty($key)) {
            return '';
        }
        $view = new \Indeed\Ihc\IndeedView();
        $data = array(
            'class' => (empty($v['class'])) ? '' : $v['class'],
            'key' => $key,
            'langCode' => indeed_get_current_language_code(),
            'type' => $type,
        );
        return $view->setTemplate(IHC_PATH . 'public/views/register-captcha.php')->setContentData($data, true)->getOutput();
    }

    private function add_level_details_on_register_form()
    {
        /*
         * @param level id
         * @return string
         */
        if ($this->current_level > -1) { /// get_option("ihc_register_show_level_price") &&
            $this->global_js .= 'jQuery( window ).on( "load", function(){ihcUpdateCart();});';
        }
    }

    /**
     * @param none
     * @return none
     */
    public function save_update_user()
    {
        if ($this->type == 'create') {
            do_action('ump_before_insert_user', $_POST);
            // @description run before insert user. @param postData
        }

        //settings the user data, in case of new user, set the array only with keys
        $this->userdata();

        // set the meta register array, values selected in dashboard, register tab
        $this->register_metas = array_merge(ihc_return_meta_arr('register'), ihc_return_meta_arr('register-msg'), ihc_return_meta_arr('register-custom-fields'));

        //register fields, function available in utilities.php,
        //return something $arr[] = array($this->display_type=>'', 'name'=>'', 'label'=>'', 'type'=>'', 'order'=>'', 'native_wp' => '', 'req' => '' );
        $this->set_register_fields();
        $this->register_with_social();

        // filter the errors on form field values. It's called in classses/RegistrationEvents.php and return an array like [ 'user_email' => 'User E-mail is not set' ]
        $errors = apply_filters('ihc_filter_register_process_check_errors', [], $_POST, $this->register_fields, $this->user_id);

        if ($errors) {
            $this->errors = $errors;
        }

        // filter the form fields. It's called in classses/RegistrationEvents.php
        $this->register_fields = apply_filters('ihc_filter_register_process_form_fields', $this->register_fields, $_POST, $this->user_id);

        $this->set_coupon();

        $this->fields = [];
        // filter the form values ( only the fields that are native in wp ) that will be stored in wp_users. It's called in classses/RegistrationEvents.php
        $this->fields = apply_filters('ihc_filter_wp_fields_values', $this->fields, $_POST, $this->register_fields, $this->user_id);


        if (empty($this->user_id)) {
            // we set the role via filter. It's called in classses/RegistrationEvents.php
            $this->fields['role'] = apply_filters('ihc_filter_register_role', '', $_POST, $this->shortcodes_attr, 'register');
        }


        $custom_meta_user = [];
        // filter the form values ( meta user ) that will be stored in usermeta. It's called in classes/RegistrationEvents.php
        $custom_meta_user = apply_filters('ihc_filter_custom_fields_values', $custom_meta_user, $_POST, $this->register_fields, $this->user_id);

        //PAY CHECK
        $paid = 0;
        if (!empty($this->payment_gateway) && ihc_check_payment_available($this->payment_gateway) && isset($_POST['lid'])) {//ihcpay
            do {

                //======================== if price after discount is 0
                $level_data = ihc_get_level_by_id($_POST['lid']);
                if (ihc_dont_pay_after_discount($_POST['lid'], $this->coupon, $level_data)) {
                    //will continue in set_levels() method
                    break;
                }
                //========================
                if ($level_data['payment_type'] != 'payment') { /// extra check for braintree
                    break; /// free level
                }

                switch ($this->payment_gateway) {//ihcpay
                    case 'authorize':
                        /*************** AUTHORIZE *****************/
                        if (!ihc_is_level_reccuring($_POST['lid'])) {
                            break;
                        }
                        global $ihc_pay_error;
                        $pay_errors = '';
                        foreach ($_POST as $key => $vals) {
                            $exp_key = explode('_', $key);
                            if ($exp_key[0] == 'ihcpay') {
                                if (empty($_POST[$key])) {
                                    $ihc_pay_error['not_empty'] = $this->register_metas['ihc_register_err_req_fields'];
                                    $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                    $pay_errors = 1;
                                }
                            }
                        }

                        /// EXTRA TESTINT FOR EXPIRATION DATE & CARD NUMBER
                        if ($pay_errors == '') {
                            ///TESTING EXPIRATION DATE
                            if (preg_match("/[^0-9]/", $_POST['ihcpay_card_expire'])) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['wrong_expiration'] = esc_html__('Please enter the expiration date in the MMYY format', 'ihc');
                                $pay_errors = 1;
                            }
                            $current_year_a = date('y', indeed_get_unixtimestamp_with_timezone());
                            $current_month_a = date('m', indeed_get_unixtimestamp_with_timezone());
                            $temp_string = (string)$_POST['ihcpay_card_expire'];
                            $post_month = $temp_string[0] . $temp_string[1];
                            $post_month = (int)$post_month;
                            $post_year = $temp_string[2] . $temp_string[3];
                            $post_year = (int)$post_year;
                            $expiration_length = strlen($_POST['ihcpay_card_expire']);
                            if ($expiration_length != 4) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['wrong_expiration'] = esc_html__('Please enter the expiration date in the MMYY format', 'ihc');
                                $pay_errors = 1;
                            }
                            if ($post_month > 12) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['wrong_expiration'] = esc_html__('Please enter the expiration date in the MMYY format', 'ihc');
                                $pay_errors = 1;
                            }
                            if ($post_year < $current_year_a) {
                                /// ERROR ON YEAR (TOO OLD)
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['wrong_expiration'] = esc_html__('Please enter the expiration date in the MMYY format', 'ihc');
                                $pay_errors = 1;
                            } else if ($post_year == $current_year_a) {
                                /// TESTING THE MONTH
                                if ($post_month < $current_month_a) {
                                    $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                    $ihc_pay_error['wrong_expiration'] = esc_html__('Please enter the expiration date in the MMYY format', 'ihc');
                                    $pay_errors = 1;
                                }
                            }

                            /// TESTING CARD NUMBER
                            if (preg_match("/[^0-9]/", $_POST['ihcpay_card_number'])) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['invalid_card'] = esc_html__('Invalid card number', 'ihc');
                                $pay_errors = 1;
                            }
                            $card_number_length = strlen($_POST['ihcpay_card_number']);
                            if ($card_number_length < 13 || $card_number_length > 16) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['invalid_card'] = esc_html__('Invalid card number', 'ihc');
                                $pay_errors = 1;
                            }
                            /// FIRST NAME
                            if (preg_match("/[^A-Za-z_]/", $_POST['ihcpay_first_name'])) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['invalid_first_name'] = esc_html__('Name field can only contain letters', 'ihc');
                                $pay_errors = 1;
                            }
                            /// LAST NAME
                            if (preg_match("/[^A-Za-z_]/", $_POST['ihcpay_last_name'])) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['invalid_last_name'] = esc_html__('Name field can only contain letters', 'ihc');
                                $pay_errors = 1;
                            }
                        }

                        if ($pay_errors == '' && !$this->errors) {
                            if (!class_exists('ihcAuthorizeNet')) {
                                require_once IHC_PATH . 'classes/PaymentGateways/ihcAuthorizeNet.class.php';
                            }
                            $auth_pay = new ihcAuthorizeNet();
                            $charge = $auth_pay->charge($_POST);
                            if ($charge) {
                                $pay_result = $auth_pay->subscribe($_POST);
                                if ($pay_result['code'] == 2) {
                                    $paid = 1;
                                    $trans_id = $pay_result['trans_id'];
                                    $this->authorize_txn_id = $trans_id;
                                    $trans_info = $pay_result;
                                    $trans_info['ihc_payment_type'] = 'authorize';
                                } else {
                                    $this->errors[] = $pay_result['message'];
                                    $pay_errors = 1;
                                    $this->errors['payment_gateway-authorize'] = TRUE;
                                }
                            } else {
                                /// error
                                $this->errors['payment_gateway-authorize'] = TRUE;
                            }
                        }
                        break;
                    case 'stripe':
                        /*************** STRIPE *****************/
                        if (isset($_POST['stripeToken'])) {
                            if (!$this->errors) {
                                if (!class_exists('ihcStripe')) {
                                    require_once IHC_PATH . 'classes/PaymentGateways/ihcStripe.class.php';
                                }
                                $payment_obj = new ihcStripe();
                                $pay_result = $payment_obj->charge($_POST);
                                if ($pay_result['message'] == "pending") {
                                    $paid = 1;
                                    $trans_id = $pay_result['trans_id'];
                                    $trans_info = $pay_result;
                                    $trans_info['ihc_payment_type'] = 'stripe';
                                }
                                unset($_POST['stripeToken']);
                            }
                            if (!empty($pay_result['cardErrors'])) {
                                \Indeed\Ihc\JsAlerts::setError(esc_html__('Stripe: Card is not valid', 'ihc'));
                            }
                        }
                        break;
                    case 'braintree':
                        /*************** BRAINTREE *****************/
                        $pay_errors = '';
                        global $ihc_pay_error;
                        foreach ($_POST as $key => $vals) {
                            if (strpos($key, 'ihc_braintree') === 0) {
                                if (empty($_POST[$key])) {
                                    $ihc_pay_error['braintree']['not_empty'][$key] = $this->register_metas['ihc_register_err_req_fields'];

                                    $pay_errors = 1;
                                }
                            }
                        }

                        /// EXTRA TESTINT FOR EXPIRATION DATE & CARD NUMBER
                        if ($pay_errors == '') {
                            ///TESTING EXPIRATION DATE
                            if (preg_match("/[^0-9]/", $_POST['ihc_braintree_card_expire_year'])) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['braintree']['wrong_expiration'] = esc_html__('Please enter the expiration date in the MMYY format', 'ihc');
                            }
                            $current_year_a = date('y', indeed_get_unixtimestamp_with_timezone());
                            $current_month_a = date('m', indeed_get_unixtimestamp_with_timezone());
                            $post_month = (int)$_POST['ihc_braintree_card_expire_month'];
                            $post_year = (int)$_POST['ihc_braintree_card_expire_year'];
                            if ($post_month > 12) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['braintree']['wrong_expiration'] = esc_html__('Wrong month', 'ihc');
                            }
                            if ($post_year < $current_year_a) {
                                /// ERROR ON YEAR (TOO OLD)
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['braintree']['wrong_expiration'] = esc_html__('Wrong year', 'ihc');
                            } else if ($post_year == $current_year_a) {
                                /// TESTING THE MONTH
                                if ($post_month < $current_month_a) {
                                    $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                    $ihc_pay_error['braintree']['wrong_expiration'] = esc_html__('Wrong month', 'ihc');
                                }
                            }

                            /// TESTING CARD NUMBER
                            if (preg_match("/[^0-9]/", $_POST['ihc_braintree_card_number'])) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['braintree']['invalid_card'] = esc_html__('Invalid card number', 'ihc');
                            }
                            $card_number_length = strlen($_POST['ihc_braintree_card_number']);
                            if ($card_number_length < 13 || $card_number_length > 16) {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['braintree']['invalid_card'] = esc_html__('Invalid card number', 'ihc');
                            }
                            /// FIRST NAME
                            if (!isset($_POST['ihc_braintree_first_name']) || $_POST['ihc_braintree_first_name'] == '') {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['braintree']['invalid_first_name'] = esc_html__('First Name field cannot be empty.', 'ihc');
                            }
                            /// LAST NAME
                            if (!isset($_POST['ihc_braintree_last_name']) || $_POST['ihc_braintree_last_name'] == '') {
                                $this->errors[] = $this->register_metas['ihc_register_err_req_fields'];
                                $ihc_pay_error['braintree']['invalid_last_name'] = esc_html__('Last Name field cannot be empty.', 'ihc');
                            }
                        }
                        break;
                }
            } while (FALSE);
        }

        $this->errors = apply_filters('ump_before_printing_errors', $this->errors);
        if ($this->errors || !empty($pay_errors)) {
            //print the error and exit
            return $this->return_errors();
        }

        //=========================== SAVE / UPDATE
        //wp native user
        if ($this->type == 'create') {
            //add new user

            if (!isset($this->fields['user_login']) || $this->fields['user_login'] === '') {
                $this->set_password_and_username();
            }
            $this->fields = apply_filters('ump_before_register_new_user', $this->fields);

            $this->user_id = wp_insert_user($this->fields);

            do_action('ump_on_register_action', $this->user_id);
            // @description run on register action. @param user id (integer)

        } else {
            //update user
            $this->fields = apply_filters('ump_before_update_user', $this->fields);
            $this->fields['ID'] = $this->user_id;
            wp_update_user($this->fields);
            do_action('ump_on_update_action', $this->user_id);
            // @description run on user update his profile. @param user id (integer)
        }

        //PAY SAVE only authorize && stripe
        if ($paid == 1) {
            $dont_save_order = TRUE;
            ihc_insert_update_transaction($this->user_id, $trans_id, $trans_info, $dont_save_order);
        }

        //custom user meta
        if ($custom_meta_user) {
            foreach ($custom_meta_user as $k => $v) {
                do_action('ihc_before_user_save_custom_field', $this->user_id, $k, $v);
                // @description run before save user custom information (user meta). @param user id(integer), custom information name (string), custom information (mixed)
                update_user_meta($this->user_id, $k, $v);
                do_action('ihc_user_save_custom_field', $this->user_id, $k, $v);
                // @description run after save user custom information (user meta). @param user id(integer), custom information name (string), custom information (mixed)
            }
        }

        if ($this->type == 'create') {
            //// used for : opt in, double email verification, individual page. It's called in classses/RegistrationEvents.php
            do_action('ihc_register_action_after_insert', $this->user_id, $_POST, $this->register_metas, $this->shortcodes_attr, 'register');
        }


        //auto login
        if (isset($this->shortcodes_attr['autologin']) && $this->shortcodes_attr['autologin'] !== FALSE) {
            $this->register_metas['ihc_register_auto_login'] = $this->shortcodes_attr['autologin'];
        }
        if ($this->type == 'create' &&
            !empty($this->register_metas['ihc_register_auto_login']) && !empty($this->register_metas['ihc_register_new_user_role'])
            && $this->register_metas['ihc_register_new_user_role'] != 'pending_user') {
            wp_set_auth_cookie($this->user_id);
        }

        $this->save_coupon();//save coupon if used

        ///
        if ($this->type == 'create') {
            if (!empty($this->register_metas['ihc_register_new_user_role']) && $this->register_metas['ihc_register_new_user_role'] == 'pending_user') {
                //PENDING
                do_action('ihc_action_create_user_review_request', $this->user_id, (isset($_POST['lid'])) ? $_POST['lid'] : 0);
            } else {
                do_action('ihc_action_create_user_register', $this->user_id, (isset($_POST['lid'])) ? $_POST['lid'] : 0);
            }
        } else {
            do_action('ihc_action_update_user', $this->user_id);
        }
        ///

        $redirectUrl = $this->set_levels();//USER LEVELS
        if (isset($redirectUrl)) {
            if ($this->payment_gateway == 'bank_transfer') {
                return ["redirectUrl" => $redirectUrl, "bankInfo" => ihc_print_bank_transfer_order($this->user_id, $_POST['lid'])];
            }
            return ["redirectUrl" => $redirectUrl];
        }

        /// set the correct expire time for authorize recurring levels
        if (!empty($paid) && $this->payment_gateway == 'authorize') {//ihcpay
            //only authorize with recurring
            $level_data = ihc_get_level_by_id($_POST['lid']);
            if (isset($level_data['access_type']) && $level_data['access_type'] == 'regular_period') {

                \Indeed\Ihc\UserSubscriptions::makeComplete($this->user_id, $_POST['lid']);
                $this->insert_the_order('Completed');///Save Order
            }
        }

        if ($this->send_password_via_mail) {
            $this->notify_user_send_password();
        }

        return ["success" => true];

    }


    private function set_password_and_username()
    {
        /*
         * @param none
         * @return none
         */
        if (empty($this->fields['user_login'])) {
            $this->fields['user_login'] = (isset($_POST['user_email'])) ? $_POST['user_email'] : '';
        }
        if (empty($this->fields['user_pass'])) {
            $this->fields['user_pass'] = wp_generate_password(10);
            $this->send_password_via_mail = TRUE;
        }
    }


    private function ihc_is_req_conditional_field($field_meta = array())
    {
        /*
         * @param array
         * @return none
         */
        if (!empty($field_meta['type']) && $field_meta['type'] == 'conditional_text') {
            $field_name = $field_meta['name'];
            if ($field_meta['conditional_text'] != $_POST[$field_name]) {
                if (!empty($field_meta['error_message'])) {
                    $this->errors[$field_name] = ihc_correct_text($field_meta['error_message']);
                } else {
                    $this->errors[$field_name] = esc_html__("Error");
                }
            }
        }
    }

    public function update_level($url_return = '')
    {
        /*
         * used only in public section (ihc_acquire_new_level() in IHC_PATH/functions.php), for add new levels to user
         * @param none
         * @return none
         */
        $lid = (isset($_REQUEST['lid'])) ? $_REQUEST['lid'] : -1;
        $lid = (int)$lid; /// let's be sure this is a number
        /****************** PUBLIC ******************/
        if (isset($lid) && $lid !== 'none' && $lid > -1) { //'lid' can be none in some older versions of plugin
            $level_data = ihc_get_level_by_id($lid);

            if ($level_data['payment_type'] == 'payment') {

                //======================== if price after discount is 0
                if (ihc_dont_pay_after_discount($lid, $this->coupon, $level_data, TRUE)) {
                    $this->handle_levels_assign($lid);
                    \Indeed\Ihc\UserSubscriptions::makeComplete($this->user_id, $lid);
                    if ($url_return) {
                        wp_redirect($url_return);
                        exit();
                    } else {
                        return;
                    }
                }
                //========================

                switch ($this->payment_gateway) {
                    case 'authorize':
                        //redirect to payment
                        if (isset($level_data['access_type']) && $level_data['access_type'] == 'regular_period') {
                            /////// AUTHORIZE RECCURING
                            if (ihc_check_payment_available('authorize')) {
                                $url_return = add_query_arg('ihc_authorize_fields', '1', $url_return);
                                $url_return = add_query_arg('lid', $lid, $url_return);
                                if ($this->coupon) {
                                    $url_return = add_query_arg('ihc_coupon', $this->coupon, $url_return);
                                }
                                if ($this->taxes_enabled) {
                                    $ihc_country = get_user_meta($this->user_id, 'ihc_country', TRUE);
                                    $url_return = add_query_arg('ihc_country', $ihc_country, $url_return);
                                    $state = get_user_meta($this->user_id, 'ihc_state', TRUE);
                                    $url_return = add_query_arg('ihc_state', $state, $url_return);
                                }
                                //
                                $this->insert_the_order();

                                wp_redirect($url_return);
                                exit();
                            } else {
                                $redirect_back = TRUE;
                            }
                        } else {
                            /////// AUTHORIZE SIMPLE
                            $this->handle_levels_assign($lid);

                            //REDIRECT
                            if (ihc_check_payment_available('authorize')) {
                                $href = IHC_URL . 'classes/PaymentGateways/authorize_payment.php?lid=' . $lid . '&uid=' . $this->user_id;
                                if ($this->coupon) {
                                    $href .= '&ihc_coupon=' . $this->coupon;
                                }
                                if (!empty($_POST['ihc_country'])) {
                                    $href .= '&ihc_country=' . $_POST['ihc_country'];
                                }
                                if ($this->taxes_enabled) {
                                    $ihc_country = get_user_meta($this->user_id, 'ihc_country', TRUE);
                                    $href .= '&ihc_country=' . $ihc_country;
                                    $state = get_user_meta($this->user_id, 'ihc_state', TRUE);
                                    $href .= '&ihc_state=' . $state;
                                }
                                $this->insert_the_order();
                                wp_redirect($href);
                                exit();
                            } else {
                                $redirect_back = TRUE;
                            }
                        }
                        break;
                    case 'twocheckout':
                        $this->handle_levels_assign($lid);

                        if (ihc_payment_workflow() == 'new') {
                            // new
                            return $this->goToPayment();
                        }

                        if (ihc_check_payment_available('twocheckout')) {
                            $this->insert_the_order();
                            if ($this->taxes_enabled) {
                                $ihc_country = get_user_meta($this->user_id, 'ihc_country', TRUE);
                            } else {
                                $ihc_country = FALSE;
                            }
                            ihc_twocheckout_submit($this->user_id, $lid, $this->coupon, $ihc_country);
                        } else {
                            $redirect_back = TRUE;
                        }
                        break;
                    case 'bank_transfer':
                        $this->handle_levels_assign($lid);

                        if (ihc_payment_workflow() == 'new') {
                            // new
                            return $this->goToPayment();
                        }

                        if (ihc_check_payment_available('bank_transfer')) {
                            if ($url_return) {
                                $url = $url_return;
                                $bt_params = array('ihc_success_bt' => true,
                                    'ihc_lid' => $lid,
                                    'ihc_uid' => $this->user_id,
                                );
                                if ($this->coupon) {
                                    $coupon_data = ihc_check_coupon($this->coupon, $lid);
                                    if ($coupon_data) {
                                        if ($coupon_data['discount_type'] == 'percentage') {
                                            $bt_params['cp'] = $coupon_data['discount_value'];
                                        } else {
                                            $bt_params['cc'] = $coupon_data['discount_value'];
                                        }
                                        ihc_submit_coupon($this->coupon);
                                    }
                                }

                                //country
                                if ($this->taxes_enabled) {
                                    $ihc_country = get_user_meta($this->user_id, 'ihc_country', TRUE);
                                    $bt_params['ihc_country'] = $ihc_country;
                                    $state = get_user_meta($this->user_id, 'ihc_state', TRUE);
                                    $bt_params['ihc_state'] = $state;
                                }

                                $this->insert_the_order();
                                $dynamic_data = array('order_id' => $this->order_id);

                                /*************************** DYNAMIC PRICE ***************************/
                                if (ihc_is_magic_feat_active('level_dynamic_price') && isset($_POST['ihc_dynamic_price'])) {
                                    $temp_amount = $_POST['ihc_dynamic_price'];
                                    if (ihc_check_dynamic_price_from_user($lid, $temp_amount)) {
                                        $bt_params['ihc_dynamic_price'] = $temp_amount;
                                        $dynamic_data['ihc_dynamic_price'] = $temp_amount;
                                    }
                                }
                                /**************************** DYNAMIC PRICE ***************************/

                                do_action('ihc_bank_transfer_charge', array_merge(['uid' => $this->user_id, 'lid' => $lid], $dynamic_data));

                                $url = add_query_arg($bt_params, $url);
                                $url .= '#ihc_bt_success_msg';

                                Ihc_User_Logs::set_user_id($this->user_id);
                                Ihc_User_Logs::set_level_id((isset($this->current_level)) ? $this->current_level : '');
                                Ihc_User_Logs::write_log(esc_html__('Bank Transfer Payment: Start process.', 'ihc'), 'payments');
                                wp_redirect($url);
                                exit();
                            }
                        } else {
                            $redirect_back = TRUE;
                        }
                        break;
                    case 'stripe':
                        if (ihc_check_payment_available('stripe')) {
                            $page = get_option('ihc_general_user_page');
                            $url = get_permalink($page);
                            $url = add_query_arg('ihc_ap_menu', 'profile', $url);
                            $url = add_query_arg('lid', $lid, $url);
                            if ($this->taxes_enabled) {
                                $ihc_country = get_user_meta($this->user_id, 'ihc_country', TRUE);
                                $url = add_query_arg('ihc_country', $ihc_country, $url);
                                $state = get_user_meta($this->user_id, 'ihc_state', TRUE);
                                $url = add_query_arg('ihc_state', $state, $url);
                            }
                            if (isset($_POST['ihc_dynamic_price'])) {
                                $url = add_query_arg('ihc_dynamic_price', $_POST['ihc_dynamic_price'], $url);
                            }
                            $this->insert_the_order();
                            wp_redirect($url);
                            exit();
                        } else {
                            $redirect_back = TRUE;
                        }
                        break;

                    case 'braintree':

                        if (ihc_check_payment_available('braintree')) {
                            $url_return = add_query_arg('ihc_braintree_fields', '1', $url_return);
                            $url_return = add_query_arg('lid', $lid, $url_return);
                            $url_return = add_query_arg('uid', $this->user_id, $url_return);
                            if ($this->coupon) {
                                $url_return = add_query_arg('ihc_coupon', $this->coupon, $url_return);
                            }
                            if ($this->taxes_enabled) {
                                $ihc_country = get_user_meta($this->user_id, 'ihc_country', TRUE);
                                $url_return = add_query_arg('ihc_country', $ihc_country, $url_return);
                                $state = get_user_meta($this->user_id, 'ihc_state', TRUE);
                                $url_return = add_query_arg('ihc_state', $state, $url_return);
                            }
                            if (isset($_POST['ihc_dynamic_price'])) {
                                $url_return = add_query_arg('ihc_dynamic_price', $_POST['ihc_dynamic_price'], $url_return);
                            }
                            $this->insert_the_order();
                            wp_redirect($url_return);
                            exit();
                        } else {
                            $redirect_back = TRUE;
                        }
                        break;

                    default:
                        $this->handle_levels_assign($lid);
                        return $this->goToPayment();
                        break;
                }//end switch
                if (!empty($redirect_back)) {
                    wp_redirect($url_return);
                    exit();
                }
            } else {
                /****************** FREE LEVEL ******************/
                $this->handle_levels_assign($lid);
                if ($url_return) {
                    wp_redirect($url_return);
                    exit();
                }
            }
        }

    }

    ///LEVELs
    private function set_levels()
    {

        /****************** PUBLIC ******************/
        if (isset($_POST['lid']) && $_POST['lid'] !== 'none' && $_POST['lid'] > -1) { //'lid' can be none in a older version

            $this->handle_levels_assign($_POST['lid']);
            $level_data = ihc_get_level_by_id($_POST['lid']);

            //======================== if price after discount is 0

            if (ihc_dont_pay_after_discount($_POST['lid'], $this->coupon, $level_data, TRUE)) {
                \Indeed\Ihc\UserSubscriptions::makeComplete($this->user_id, esc_sql($_POST['lid']));
                return;
            }
            //========================

            if ($level_data['payment_type'] == 'payment') {
                switch ($this->payment_gateway) {
                    case 'authorize':
                        if (isset($level_data['access_type']) && $level_data['access_type'] == 'regular_period') {
                        } else {
                            if (ihc_check_payment_available('authorize')) {
                                /// SAVE THE ORDER
                                $this->insert_the_order();

                                $href = IHC_URL . 'classes/PaymentGateways/authorize_payment.php?lid=' . $_POST['lid'] . '&uid=' . $this->user_id;
                                if ($this->coupon) {
                                    $href .= '&ihc_coupon=' . $this->coupon;
                                }
                                if (!empty($_POST['ihc_country']) && $this->taxes_enabled) {
                                    $href .= '&ihc_country=' . $_POST['ihc_country'];
                                    $state = get_user_meta($this->user_id, 'ihc_state', TRUE);
                                    $href .= '&ihc_state=' . $state;
                                }
                                if (isset($_POST['ihc_dynamic_price'])) {
                                    $href .= "&ihc_dynamic_price=" . $_POST['ihc_dynamic_price'];
                                }
                                wp_redirect($href);
                                exit();
                            }
                        }
                        break;
                    case 'twocheckout':
                        if (ihc_payment_workflow() == 'new') {
                            // new
                            return $this->goToPayment();
                        }
                        if (ihc_check_payment_available('twocheckout')) {
                            /// SAVE THE ORDER
                            $this->insert_the_order();
                            if (!empty($_POST['ihc_country']) && $this->taxes_enabled) {
                                $ihc_country = $_POST['ihc_country'];
                            } else {
                                $ihc_country = FALSE;
                            }
                            ihc_twocheckout_submit($this->user_id, $_POST['lid'], $this->coupon, $ihc_country);
                        }
                        break;
                    case 'bank_transfer':
                        if (ihc_payment_workflow() == 'new') {
                            // new
                            return $this->goToPayment();
                        }

                        if (ihc_check_payment_available('bank_transfer')) {
                            /// SAVE THE ORDER
                            Ihc_User_Logs::set_user_id($this->user_id);
                            Ihc_User_Logs::set_level_id((isset($this->current_level)) ? $this->current_level : '');
                            Ihc_User_Logs::write_log(esc_html__('Bank Transfer Payment: Start process.', 'ihc'), 'payments');
                            $this->insert_the_order();
                            $dynamic_data = array('order_id' => $this->order_id);
                            do_action('ihc_bank_transfer_charge', array_merge(['uid' => $this->user_id, 'lid' => $_POST['lid']], $dynamic_data));
                            $this->bank_transfer_message = TRUE;
                        }
                        break;
                    case 'stripe':
                        $this->insert_the_order();
                        break;
                    case 'braintree':
                        if (ihc_check_payment_available('braintree')) {
                            $post_data = $_POST;
                            $post_data['uid'] = $this->user_id;
                            $post_data['lid'] = $_POST['lid'];
                            $post_data['ihc_coupon'] = $this->coupon;
                            if (!empty($post_data['ihc_country']) && !$this->taxes_enabled) {
                                unset($post_data['ihc_country']);
                            }
                            if (isset($_POST['ihc_dynamic_price'])) {
                                $post_data['ihc_dynamic_price'] = $_POST['ihc_dynamic_price'];
                            }
                            if (version_compare(phpversion(), '7.2', '>=')) {
                                // braintree v2
                                require_once IHC_PATH . 'classes/PaymentGateways/Ihc_Braintree_V2.class.php';
                                $braintree = new Ihc_Braintree_V2();
                                $braintree->do_charge($post_data);
                            } else {
                                // braintree v1
                                require_once IHC_PATH . 'classes/PaymentGateways/Ihc_Braintree.class.php';
                                $braintree = new Ihc_Braintree();
                                $braintree->do_charge($post_data);
                            }

                        }
                        break;
                    default:
                        return $this->goToPayment();
                        break;
                }
            }
        }

    }

    private function insert_the_order($status = 'pending')
    {
        /*
         * @param none
         * @return none
         */
        $lid = (empty($_POST['lid'])) ? FALSE : $_POST['lid'];
        if ($lid === FALSE) {
            $lid = (empty($_GET['lid'])) ? FALSE : $_GET['lid'];
        }

        if (!empty($this->user_id) && $lid !== FALSE) {
            $levels = \Indeed\Ihc\Db\Memberships::getAll();
            $amount = $levels[$lid]['price'];
            $extra_order_info = array();

            /*************************** DYNAMIC PRICE ***************************/
            if (ihc_is_magic_feat_active('level_dynamic_price') && isset($_POST['ihc_dynamic_price'])) {
                $temp_amount = $_POST['ihc_dynamic_price'];
                if (ihc_check_dynamic_price_from_user($lid, $temp_amount)) {
                    $amount = $temp_amount;
                }
            }
            /**************************** DYNAMIC PRICE ***************************/

            ///
            if (!empty($this->authorize_txn_id)) {
                $extra_order_info['txn_id'] = $this->authorize_txn_id;
            }

            if (isset($levels[$lid]['access_type']) && $levels[$lid]['access_type'] == 'regular_period') {
                if (isset($levels[$lid]['access_trial_price']) && $levels[$lid]['access_trial_price'] != '') {
                    /// AMOUNT FOR TRIAL
                    $amount = $levels[$lid]['access_trial_price'];
                    $extra_order_info['is_trial'] = TRUE;
                }
            }

            if ($this->coupon) {
                $coupon_data = ihc_check_coupon($this->coupon, $lid);
                if ($coupon_data) {
                    $extra_order_info['discount_value'] = ihc_get_discount_value($amount, $coupon_data);
                    $extra_order_info['coupon_used'] = $this->coupon;
                    $amount = ihc_coupon_return_price_after_decrease($amount, $coupon_data, TRUE, $this->user_id, $lid);/// will do the submit also
                }
            }
            /// TAXES
            if (!empty($_POST['ihc_country'])) {
                $ihc_country = $_POST['ihc_country'];
            } else {
                $ihc_country = get_user_meta($this->user_id, 'ihc_country', TRUE);
            }
            if (!empty($_POST['ihc_state'])) {
                $state = $_POST['ihc_state'];
            } else {
                $state = get_user_meta($this->user_id, 'ihc_state', TRUE);
                if ($state == FALSE) {
                    $state = '';
                }
            }

            $country = ($ihc_country == FALSE) ? '' : $ihc_country;
            $taxes_data = ihc_get_taxes_for_amount_by_country($country, $state, $amount);
            if ($taxes_data && !empty($taxes_data['total'])) {
                $amount = $amount + $taxes_data['total'];
                $extra_order_info['tax_value'] = $taxes_data['total'];
            }

            if ($this->payment_gateway == 'stripe' && $amount > 0.0 && $amount < 0.50) {
                $amount = 0.5;/// minimum for stripe.
            }
            if ($this->payment_gateway == 'stripe' && isset($extra_order_info['is_trial']) && $extra_order_info['is_trial'] == TRUE) {
                //Do not store Orders with 0 amount for Stripe trial period;
                //return;
            }

            $this->order_id = ihc_insert_update_order($this->user_id, $lid, $amount, $status, $this->payment_gateway, $extra_order_info);
        }
    }

    /**
     * @param string with all level ids separated by comma
     * @param bool (if the action it's made by admin)
     * @return none
     */
    private function handle_levels_assign($request_levels)
    {
        $levels = explode(',', $request_levels);
        if (empty($levels)) {
            return;
        }
        foreach ($levels as $lid) {
            if (\Indeed\Ihc\UserSubscriptions::getOne($this->user_id, $lid)
                && strtotime(\Indeed\Ihc\UserSubscriptions::getExpireTimeForSubscription($this->user_id, $lid)) > current_time('timestamp')) {
                continue;
            }
            \Indeed\Ihc\UserSubscriptions::assign($this->user_id, $lid);
            $levelData = \Indeed\Ihc\Db\Memberships::getOne($lid);

            if ($levelData['payment_type'] == 'free') {
                \Indeed\Ihc\UserSubscriptions::makeComplete($this->user_id, $lid, false);
            }
        }

    }//end of handle_levels_assign()

    ///deprecated
    protected function notify_user()
    {
        //email notification to user
        if ($this->type == 'create') {
            if (!empty($this->register_metas['ihc_register_new_user_role']) && $this->register_metas['ihc_register_new_user_role'] == 'pending_user') {
                //PENDING
                do_action('ihc_action_create_user_review_request', $this->user_id, (isset($_POST['lid'])) ?: 0);
            } else {
                do_action('ihc_action_create_user_register', $this->user_id, (isset($_POST['lid'])) ?: 0);
            }
        } else {
            do_action('ihc_action_update_user', $this->user_id);
        }
    }

    protected function notify_user_send_password()
    {
        /*
         * @param none
         * @return none
         */
        do_action('ihc_register_lite_action', $this->user_id, ['{NEW_PASSWORD}' => $this->fields['user_pass']]);

    }

    ///// RETURN ERROR
    protected function return_errors()
    {
        /*
         * set the global variable with the error string
         */
        if (!empty($this->errors)) {
            global $ihc_error_register;
            $ihc_error_register = $this->errors;
            $keys = array_keys($this->errors);
            return new WP_Error(400, $this->errors[$keys[0]], array('status' => 400));
        }
    }

    private function count_register_fields()
    {
        $count = 0;
        foreach ($this->register_fields as $v) {
            if ($v[$this->display_type] > 0) {
                $count++;
            }
        }
        return $count;
    }

    protected function succes_message()
    {
        /*
         * @param none
         * @return none
         */
        if ($this->type == 'create') {
            $q_arg = 'create_message';
        } else {
            $q_arg = 'update_message';
        }

        $redirect = get_option('ihc_general_register_redirect');
        $redirect = apply_filters('ump_public_filter_redirect_page_after_register', $redirect);

        if ($redirect && $redirect != -1 && $this->type == 'create') {
            //custom redirect
            $url = get_permalink($redirect);
            if (!$url) {
                $url = ihc_get_redirect_link_by_label($redirect, $this->user_id);
                if (strpos($url, IHC_PROTOCOL . $_SERVER['HTTP_HOST']) !== 0) {
                    //if it's a external custom redirect we don't want to add extra params in url, so let's redirect from here
                    wp_redirect($url);
                    exit();
                }
            }
        }

        if (empty($url)) {
            $url = IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        if ($this->type == 'create') {
            $url = apply_filters('ihc_register_redirect_filter', $url, $this->user_id, (isset($_POST['lid'])) ? $_POST['lid'] : 0);
        }


        if ($this->bank_transfer_message) {
            /// bt redirect only to same page
            $bt_params = array('ihc_register' => $q_arg,
                'ihcbt' => 'true',
                'ihc_lid' => $_POST['lid'],
                'ihc_uid' => $this->user_id
            );
            if ($this->coupon) {
                $coupon_data = ihc_check_coupon($this->coupon, $_POST['lid']);
                if ($coupon_data) {
                    if ($coupon_data['discount_type'] == 'percentage') {
                        $bt_params['cp'] = $coupon_data['discount_value'];
                    } else {
                        $bt_params['cc'] = $coupon_data['discount_value'];
                    }
                    ihc_submit_coupon($this->coupon);
                }
            }

            //country
            if (!empty($_POST['ihc_country'])) {
                $bt_params['ihc_country'] = $_POST['ihc_country'];
            }
            if (!empty($_POST['ihc_state'])) {
                $bt_params['ihc_state'] = $_POST['ihc_state'];
            }
            $url = add_query_arg($bt_params, $url);
        } else {
            $url = add_query_arg(array('ihc_register' => $q_arg), $url);
        }

        if (!empty($bt_params)) {
            $url .= '#ihc_bt_success_msg';
        }

        wp_redirect($url);
        exit();
    }

    ////SOCIAL MEDIA
    private function social_register_request_data()
    {
        $str = '';
        if ($this->is_public) {
            if (!empty($_GET['ihc_fb'])) {
                $ihc_register_sm_value = 'fb';
                $ihc_sm_value = $_GET['ihc_fb'];
                $ihc_sm_name = 'ihc_fb';
            } else if (!empty($_GET['ihc_tw'])) {
                $ihc_register_sm_value = 'tw';
                $ihc_sm_value = $_GET['ihc_tw'];
                $ihc_sm_name = 'ihc_tw';
            } else if (!empty($_GET['ihc_in'])) {
                $ihc_register_sm_value = 'in';
                $ihc_sm_value = $_GET['ihc_in'];
                $ihc_sm_name = 'ihc_in';
            } else if (!empty($_GET['ihc_tbr'])) {
                $ihc_register_sm_value = 'tbr';
                $ihc_sm_value = $_GET['ihc_tbr'];
                $ihc_sm_name = 'ihc_tbr';
            } else if (!empty($_GET['ihc_ig'])) {
                $ihc_register_sm_value = 'ig';
                $ihc_sm_value = $_GET['ihc_ig'];
                $ihc_sm_name = 'ihc_ig';
            } else if (!empty($_GET['ihc_vk'])) {
                $ihc_register_sm_value = 'vk';
                $ihc_sm_value = $_GET['ihc_vk'];
                $ihc_sm_name = 'ihc_vk';
            } else if (!empty($_GET['ihc_goo'])) {
                $ihc_register_sm_value = 'goo';
                $ihc_sm_value = $_GET['ihc_goo'];
                $ihc_sm_name = 'ihc_goo';
            }
            if (!empty($ihc_register_sm_value) && !empty($ihc_sm_value) && !empty($ihc_sm_name)) {
                $str .= indeed_create_form_element(array('name' => 'ihc_sm_register', 'value' => $ihc_register_sm_value, 'type' => 'hidden'));
                $str .= indeed_create_form_element(array('name' => $ihc_sm_name, 'value' => $ihc_sm_value, 'type' => 'hidden'));
            }
        }
        return $str;
    }

    private function register_with_social()
    {
        /*
         * test if user was register with social. If true generate a password if it's not set
         * @param none
         * @return none
         */
        if (isset($_POST['ihc_sm_register'])) {
            //generate password if it's not set
            if (empty($_POST['pass1'])) {
                $password = wp_generate_password();
                $_POST['pass1'] = $password;
                $_POST['pass2'] = $password;
            }

            //add social key to current register_fields array
            $name = 'ihc_' . $_POST['ihc_sm_register'];
            $this->register_fields[] = array('name' => $name);

        }
    }//end of register_with_social

    private function ihc_social_form()
    {
        $view = new \Indeed\Ihc\IndeedView();
        return $view->setTemplate(IHC_PATH . 'public/views/register-social_form.php')
            ->setContentData(array('url' => IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']), true)
            ->getOutput();
    }

    ////COUPONS
    public function set_coupon($coupon = '')
    {
        $this->coupon = (isset($_POST['ihc_coupon'])) ? $_POST['ihc_coupon'] : $coupon;
        if ($this->coupon) {
            $this->coupon = str_replace(' ', '', $this->coupon);
            if (!empty($this->register_fields)) {
                $ihc_coupon = ihc_array_value_exists($this->register_fields, 'ihc_coupon', 'name');
                if (isset($ihc_coupon) && $ihc_coupon !== FALSE) {
                    unset($this->register_fields[$ihc_coupon]);
                }
            }
        }
    }

    public function save_coupon()
    {
        if ($this->coupon && $this->user_id) {
            $user_coupons = array();
            $user_coupons[] = get_user_meta($this->user_id, 'ihc_coupon', TRUE);
            $user_coupons[] = $this->coupon;
            update_user_meta($this->user_id, 'ihc_coupon', $user_coupons);
        }
    }

    public function goToPayment()
    {
        $lid = isset($_POST['lid']) ? esc_sql($_POST['lid']) : '';
        if ($lid === '' && isset($_GET['lid'])) {
            // in some rare cases ( payment for ulp course ), the level id is found in GET
            $lid = $_GET['lid'];
        }
        $options = array(
            'uid' => $this->user_id,
            'lid' => $lid,
            'ihc_coupon' => $this->coupon,
            'ihc_country' => esc_sql((isset($_POST['ihc_country'])) ? $_POST['ihc_country'] : ''),
            'ihc_state' => get_user_meta($this->user_id, 'ihc_state', true),
            'ihc_dynamic_price' => esc_sql((isset($_POST['ihc_dynamic_price'])) ? $_POST['ihc_dynamic_price'] : ''),
            'defaultRedirect' => '',
            'is_register' => $this->type == 'create' ? true : false,
        );
        if (!class_exists('FlutterDoPayment')) {
            require_once(__DIR__ . '/FlutterDoPayment.php');
        }
        $paymentObject = new FlutterDoPayment($options, $this->payment_gateway);
        $data = $paymentObject->processing();
        $redirectUrl = (function () {
            return $this->redirectUrl;
        })->call($data);
        return $redirectUrl;
    }

}
