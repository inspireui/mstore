<?php
/*
* Template Name: Mstore API
*/

function getValue(&$val, $default = '')
{
    return isset($val) ? $val : $default;
}

$data = null;
if (isset($_POST['order'])) {
    $data = json_decode(urldecode(base64_decode(sanitize_text_field($_POST['order']))), true);
} elseif (filter_has_var(INPUT_GET, 'order')) {
    $data = filter_has_var(INPUT_GET, 'order') ? json_decode(urldecode(base64_decode(sanitize_text_field(filter_input(INPUT_GET, 'order')))), true) : [];
} elseif (filter_has_var(INPUT_GET, 'code')) {
    $code = sanitize_text_field(filter_input(INPUT_GET, 'code'));
    global $wpdb;
    $table_name = $wpdb->prefix . "mstore_checkout";
    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE code = %s", $code);
    $item = $wpdb->get_row($sql);
    if ($item) {
        $data = json_decode(urldecode(base64_decode($item->order)), true);
    } else {
        return var_dump("Can't not get the order");
    }
}

if ($data != null):
    global $woocommerce;
    // Validate the cookie token
    $userId = validateCookieLogin($data['token']);
    if (is_wp_error($userId)) {
        return var_dump($userId);
    }

    // Check user and authentication
    $user = get_userdata($userId);
    if ($user) {
        if (!is_user_logged_in()) {
            wp_set_current_user($userId, $user->user_login);
            wp_set_auth_cookie($userId);

            $url = filter_has_var(INPUT_SERVER, 'REQUEST_URI') ? filter_input(INPUT_SERVER, 'REQUEST_URI') : '';
            header("Refresh: 0; url=$url");
        }
    }
    $woocommerce->session->set('refresh_totals', true);
    $woocommerce->cart->empty_cart();

    // Get product info
    $billing = $data['billing'];
    $shipping = $data['shipping'];
    $products = $data['line_items'];
    foreach ($products as $product) {
        $productId = absint($product['product_id']);

        $quantity = $product['quantity'];
        $variationId = getValue($product['variation_id'], null);

        // Check the product variation
        if (!empty($variationId)) {
            $productVariable = new WC_Product_Variable($productId);
            $listVariations = $productVariable->get_available_variations();
            foreach ($listVariations as $vartiation => $value) {
                if ($variationId == $value['variation_id']) {
                    $attribute = $value['attributes'];
                    $woocommerce->cart->add_to_cart($productId, $quantity, $variationId, $attribute);
                }
            }
        } else {
            $woocommerce->cart->add_to_cart($productId, $quantity);
        }
    }


    if (!empty($data['coupon_lines'])) {
        $coupons = $data['coupon_lines'];
        foreach ($coupons as $coupon) {
            $woocommerce->cart->add_discount($coupon['code']);
        }
    }

    $shippingMethod = '';
    if (!empty($data['shipping_lines'])) {
        $shippingLines = $data['shipping_lines'];
        $shippingMethod = $shippingLines[0]['method_id'];
    }

    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?> >
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?> >

    <div id="page" class="site">
        <div class="site-content-contain">
            <div id="content" class="site-content">
                <div class="wrap">
                    <div id="primary" class="content-area">
                        <main id="main" class="site-main" role="main">
                            <article id="post-6" class="post-6 page type-page status-publish hentry">
                                <div class="entry-content">
                                    <div class="woocommerce">
                                        <?php
                                        wc_print_notices();
                                        ?>
                                        <form
                                                name="checkout" method="post"
                                                class="checkout woocommerce-checkout"
                                                action="<?php echo esc_url(get_bloginfo('url')); ?>/checkout/"
                                                enctype="multipart/form-data">
                                            <?php do_action('woocommerce_checkout_before_customer_details'); ?>
                                            <div class="col2-set" id="customer_details">
                                                <div class="col-1">
                                                    <div class="woocommerce-billing-fields">

                                                        <h3>Billing details</h3>

                                                        <div class="woocommerce-billing-fields__field-wrapper">
                                                            <p class="form-row form-row-first validate-required"
                                                               id="billing_first_name_field" data-priority="10">
                                                                <label for="billing_first_name" class="">First name
                                                                    <abbr class="required" title="required">*</abbr>
                                                                </label>
                                                                <input class="input-text "
                                                                       name="billing_first_name" id="billing_first_name"
                                                                       placeholder=""
                                                                       value="<?php echo isset($billing['first_name']) ? esc_html(getValue($billing['first_name'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-last validate-required"
                                                               id="billing_last_name_field" data-priority="20">
                                                                <label for="billing_last_name" class="">Last name <abbr
                                                                            class="required" title="required">*</abbr>
                                                                </label>
                                                                <input class="input-text "
                                                                       name="billing_last_name" id="billing_last_name"
                                                                       placeholder=""
                                                                       value="<?php echo isset($billing['last_name']) ? esc_html(getValue($billing['last_name'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-wide" id="billing_company_field"
                                                               data-priority="30">
                                                                <label for="billing_company" class="">Company
                                                                    name</label>
                                                                <input class="input-text "
                                                                       name="billing_company" id="billing_company"
                                                                       placeholder=""
                                                                       value="<?php echo isset($data['billing_company']) ? esc_html(getValue($data['billing_company'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-wide address-field update_totals_on_change validate-required"
                                                               id="billing_country_field" data-priority="40">
                                                                <label for="billing_country" class="">Country <abbr
                                                                            class="required" title="required">*</abbr>
                                                                </label>
                                                                <input class="input-text "
                                                                       name="billing_country" id="billing_country"
                                                                       placeholder=""
                                                                       value="<?php echo isset($billing['country']) ? esc_html(getValue($billing['country'])) : ''; ?>"/>

                                                            </p>
                                                            <p class="form-row form-row-wide address-field validate-required"
                                                               id="billing_address_1_field" data-priority="50">
                                                                <label for="billing_address_1" class="">Address <abbr
                                                                            class="required" title="required">*</abbr>
                                                                </label>
                                                                <input class="input-text "
                                                                       name="billing_address_1" id="billing_address_1"
                                                                       placeholder="Street address"
                                                                       value="<?php echo isset($billing['address_1']) ? esc_html(getValue($billing['address_1'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-wide address-field"
                                                               id="billing_address_2_field" data-priority="60">
                                                                <input class="input-text "
                                                                       name="billing_address_2" id="billing_address_2"
                                                                       placeholder="Apartment, suite, unit etc. (optional)"
                                                                       value="<?php echo isset($billing['address_2']) ? esc_html(getValue($billing['address_2'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-wide address-field validate-required"
                                                               id="billing_city_field" data-priority="70">
                                                                <label for="billing_city" class="">Town / City <abbr
                                                                            class="required" title="required">*</abbr>
                                                                </label>
                                                                <input class="input-text "
                                                                       name="billing_city" id="billing_city"
                                                                       placeholder=""
                                                                       value="<?php echo isset($billing['city']) ? esc_html(getValue($billing['city'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-wide address-field validate-state"
                                                               id="billing_state_field" style="display: none">
                                                                <label for="billing_state" class="">State /
                                                                    County</label>
                                                                <input class="hidden" name="billing_state"
                                                                       id="billing_state"
                                                                       value="<?php echo isset($billing['state']) ? esc_html(getValue($billing['state'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-wide address-field validate-postcode"
                                                               id="billing_postcode_field" data-priority="65">
                                                                <label for="billing_postcode" class="">Postcode /
                                                                    ZIP</label>
                                                                <input class="input-text "
                                                                       name="billing_postcode" id="billing_postcode"
                                                                       placeholder=""
                                                                       value="<?php echo isset($billing['postcode']) ? esc_html(getValue($billing['postcode'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-first validate-phone"
                                                               id="billing_phone_field" data-priority="100">
                                                                <label for="billing_phone" class="">Phone</label>
                                                                <input class="input-text "
                                                                       name="billing_phone" id="billing_phone"
                                                                       placeholder=""
                                                                       value="<?php echo isset($billing['phone']) ? esc_html(getValue($billing['phone'])) : ''; ?>"/>
                                                            </p>
                                                            <p class="form-row form-row-last validate-required validate-email"
                                                               id="billing_email_field" data-priority="110">
                                                                <label for="billing_email" class="">Email address <abbr
                                                                            class="required" title="required">*</abbr>
                                                                </label>
                                                                <input class="input-text "
                                                                       name="billing_email" id="billing_email"
                                                                       placeholder=""
                                                                       value="<?php echo isset($billing['email']) ? esc_html(getValue($billing['email'])) : ''; ?>"/>
                                                            </p>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="col-2">
                                                    <div class="woocommerce-shipping-fields">
                                                        <h3 id="ship-to-different-address">
                                                            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                                                                <input id="ship-to-different-address-checkbox"
                                                                       class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                                                                       type="checkbox" name="ship_to_different_address"
                                                                       value="1"/>
                                                                <span>Ship to a different address?</span>
                                                            </label>
                                                        </h3>

                                                        <div class="shipping_address">
                                                            <div class="woocommerce-shipping-fields__field-wrapper">
                                                                <p class="form-row form-row-first validate-required"
                                                                   id="shipping_first_name_field" data-priority="10">
                                                                    <label for="shipping_first_name" class="">First name
                                                                        <abbr class="required" title="required">*</abbr>
                                                                    </label>
                                                                    <input class="input-text "
                                                                           name="shipping_first_name"
                                                                           id="shipping_first_name" placeholder=""
                                                                           value="<?php echo isset($shipping['first_name']) ? esc_html(getValue($shipping['first_name'])) : ''; ?>"/>
                                                                </p>
                                                                <p class="form-row form-row-last validate-required"
                                                                   id="shipping_last_name_field" data-priority="20">
                                                                    <label for="shipping_last_name" class="">Last name
                                                                        <abbr class="required" title="required">*</abbr>
                                                                    </label>
                                                                    <input class="input-text "
                                                                           name="shipping_last_name"
                                                                           id="shipping_last_name" placeholder=""
                                                                           value="<?php echo isset($shipping['last_name']) ? esc_html(getValue($shipping['last_name'])) : ''; ?>"/>
                                                                </p>
                                                                <p class="form-row form-row-wide"
                                                                   id="shipping_company_field" data-priority="30">
                                                                    <label for="shipping_company" class="">Company
                                                                        name</label>
                                                                    <input class="input-text "
                                                                           name="shipping_company" id="shipping_company"
                                                                           placeholder=""
                                                                           value="<?php echo isset($shipping['company']) ? esc_html(getValue($shipping['company'])) : ''; ?>"/>
                                                                </p>
                                                                <p class="form-row form-row-wide address-field update_totals_on_change validate-required"
                                                                   id="shipping_country_field" data-priority="40">
                                                                    <label for="shipping_country" class="">Country <abbr
                                                                                class="required"
                                                                                title="required">*</abbr>
                                                                    </label>
                                                                    <input class="input-text "
                                                                           name="shipping_country" id="shipping_country"
                                                                           placeholder=""
                                                                           value="<?php echo isset($shipping['country']) ? esc_html(getValue($shipping['country'])) : ''; ?>"/>
                                                                </p>
                                                                <p class="form-row form-row-wide address-field validate-required"
                                                                   id="shipping_address_1_field" data-priority="50">
                                                                    <label for="shipping_address_1" class="">Address
                                                                        <abbr class="required" title="required">*</abbr>
                                                                    </label>
                                                                    <input class="input-text "
                                                                           name="shipping_address_1"
                                                                           id="shipping_address_1"
                                                                           placeholder="Street address"
                                                                           value="<?php echo isset($shipping['address_1']) ? esc_html(getValue($shipping['address_1'])) : ''; ?>"/>
                                                                </p>
                                                                <p class="form-row form-row-wide address-field"
                                                                   id="shipping_address_2_field" data-priority="60">
                                                                    <input class="input-text "
                                                                           name="shipping_address_2"
                                                                           id="shipping_address_2"
                                                                           placeholder="Apartment, suite, unit etc. (optional)"
                                                                           value="<?php echo isset($shipping['address_2']) ? esc_html(getValue($shipping['address_2'])) : ''; ?>"/>
                                                                </p>
                                                                <p class="form-row form-row-wide address-field validate-required"
                                                                   id="shipping_city_field" data-priority="70">
                                                                    <label for="shipping_city" class="">Town / City
                                                                        <abbr class="required" title="required">*</abbr>
                                                                    </label>
                                                                    <input class="input-text "
                                                                           name="shipping_city" id="shipping_city"
                                                                           placeholder=""
                                                                           value="<?php echo isset($shipping['city']) ? esc_html(getValue($shipping['city'])) : ''; ?>"/>
                                                                </p>
                                                                <p class="form-row form-row-wide address-field validate-state"
                                                                   id="shipping_state_field" style="display: none">
                                                                    <label for="shipping_state" class="">State /
                                                                        County</label>
                                                                    <input class="hidden"
                                                                           name="shipping_state" id="shipping_state"
                                                                           value="<?php echo isset($shipping['state']) ? esc_html(getValue($shipping['state'])) : ''; ?>"
                                                                           placeholder=""/>
                                                                </p>
                                                                <p class="form-row form-row-wide address-field validate-postcode"
                                                                   id="shipping_postcode_field" data-priority="65">
                                                                    <label for="shipping_postcode" class="">Postcode /
                                                                        ZIP</label>
                                                                    <input class="input-text "
                                                                           name="shipping_postcode"
                                                                           id="shipping_postcode" placeholder=""
                                                                           value="<?php echo isset($shipping['postcode']) ? esc_html(getValue($shipping['postcode'])) : '';; ?>"/>
                                                                </p>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <?php do_action('woocommerce_before_order_notes'); ?>
                                                    <div class="woocommerce-additional-fields">
                                                        <div class="woocommerce-additional-fields__field-wrapper">
                                                            <p class="form-row notes" id="order_comments_field"
                                                               data-priority="">
                                                                <label for="order_comments" class="">Order notes</label>
                                                                <textarea name="order_comments" class="input-text "
                                                                          id="order_comments"
                                                                          placeholder="Notes about your order, e.g. special notes for delivery."
                                                                          rows="2" cols="5"
                                                                          value="<?php echo isset($data['customer_note']) ? esc_html($data['customer_note']) : ''; ?>"><?php echo isset($data['customer_note']) ? esc_html($data['customer_note']) : ''; ?></textarea>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <?php do_action('woocommerce_after_order_notes'); ?>
                                                </div>
                                            </div>

                                            <?php do_action('woocommerce_checkout_after_customer_details'); ?>

                                            <h3 id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h3>

                                            <?php do_action('woocommerce_checkout_before_order_review'); ?>

                                            <div id="order_review">
                                                <table class="shop_table">
                                                    <thead>
                                                    <tr>
                                                        <th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                                                        <th class="product-total"><?php esc_html_e('Total', 'woocommerce'); ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                                                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                                                        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                                            ?>
                                                            <tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                                                                <td class="product-name">
                                                                <?php echo apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;'; ?>
                                                                <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times; %s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); ?>
                                                                <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                                                                </td>
                                                                <td class="product-total">
                                                                <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                    </tbody>
                                                    <tfoot>
                                                    <tr class="cart-subtotal">
                                                        <th><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
                                                        <td><?php wc_cart_totals_subtotal_html(); ?></td>
                                                    </tr>

                                                    <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                                                        <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                                                            <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                                                            <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>

                                                    <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>

                                                        <tr class="shipping">
                                                            <th>Shipping</th>
                                                            <td><input type="radio" checked="checked"
                                                                       class="shipping_method" name="shipping_method[]"
                                                                       id="shipping_method__<?php echo esc_html($shippingMethod); ?>"
                                                                       value="<?php echo esc_html($shippingMethod); ?>"/>
                                                            </td>
                                                        </tr>

                                                    <?php endif; ?>

                                                    <?php foreach (WC()->cart->get_fees() as $fee) : ?>
                                                        <tr class="fee">
                                                            <th><?php echo esc_html($fee->name); ?></th>
                                                            <td><?php wc_cart_totals_fee_html($fee); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>

                                                    <?php if (wc_tax_enabled() && 'excl' === WC()->cart->get_tax_price_display_mode()) : ?>
                                                        <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                                                            <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
                                                                <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                                                                    <th><?php echo esc_html($tax->label); ?></th>
                                                                    <td><?php echo esc_html(wp_kses_post($tax->formatted_amount)); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else : ?>
                                                            <tr class="tax-total">
                                                                <th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                                                                <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <tr class="order-total">
                                                        <th><?php esc_html_e('Total', 'woocommerce'); ?></th>
                                                        <td><?php wc_cart_totals_order_total_html(); ?></td>
                                                    </tr>

                                                    </tfoot>
                                                </table>
                                                <?php
                                                if (!is_ajax()) {
                                                    do_action('woocommerce_review_order_before_payment');
                                                }
                                                ?>
                                                <div id="payment" class="woocommerce-checkout-payment">

                                                    <input type="radio" name="payment_method"
                                                           id="payment_method_<?php echo esc_attr($data['payment_method']); ?>"
                                                           checked="checked"
                                                           value="<?php echo esc_html($data['payment_method']); ?>"/>

                                                    <input type="checkbox"
                                                           class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                                                           checked="checked" name="terms" id="terms">


                                                    <?php do_action('woocommerce_review_order_before_submit'); ?>

                                                    <?php echo apply_filters('woocommerce_order_button_html', '<input type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="" />'); ?>

                                                    <?php do_action('woocommerce_review_order_after_submit'); ?>

                                                    <?php wp_nonce_field('woocommerce-process_checkout'); ?>
                                                </div>
                                                <?php
                                                if (!is_ajax()) {
                                                    do_action('woocommerce_review_order_after_payment');
                                                }
                                                ?>
                                            </div>

                                            <?php do_action('woocommerce_checkout_after_order_review'); ?>

                                        </form>

                                    </div>
                                </div>
                                <!-- .entry-content -->
                            </article>
                            <!-- #post-## -->
                        </main>
                        <!-- #main -->
                    </div>
                    <!-- #primary -->
                </div>
                <!-- .wrap -->
            </div>
            <!-- #content -->
        </div>
        <!-- .site-content-contain -->
    </div>
    <!-- #page -->
    <?php wp_footer(); ?>
    <script type="text/javascript">
        setTimeout(function () {
            document.getElementById('place_order').click();
        }, 1500);
    </script>
    </body>
    </html>
<?php
endif;
?>
