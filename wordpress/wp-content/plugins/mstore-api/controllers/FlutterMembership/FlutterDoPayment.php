<?php

/*
@since 7.4
*/

class FlutterDoPayment
{
    private $attributes = array();
    private $paymentGateway = '';
    private $returnUrl = '';

    public function __construct($params = array(), $paymentGateway = '', $returnUrl = '')
    {
        $this->attributes = $params;
        $this->paymentGateway = $paymentGateway;
        $this->returnUrl = $returnUrl;
    }

    public function insertOrder()
    {
        $createOrder = new \Indeed\Ihc\CreateOrder($this->attributes, $this->paymentGateway);
        $this->attributes['orderId'] = $createOrder->proceed()->getOrderId();
        return $this;
    }

    public function processing()
    {
        switch ($this->paymentGateway) {
            case 'paypal':
                if (ihc_check_payment_available('paypal')) {
                    if (ihc_payment_workflow() == 'new') {
                        // new
                        $paymentGatewayObject = new \Indeed\Ihc\Gateways\PayPalStandard();
                        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                        ->check()
                            ->preparePayment()
                            ->saveOrder()
                            ->chargePayment();
                    } else {
                        // standard
                        $this->insertOrder();
                        $paymentGatewayObject = new \Indeed\Ihc\PaymentGateways\PayPalStandard();
                    }
                }
                break;
            case 'mollie':
                if (ihc_check_payment_available('mollie')) {
                    if (ihc_payment_workflow() == 'new') {
                        // new
                        $paymentGatewayObject = new \Indeed\Ihc\Gateways\Mollie();
                        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                        ->check()
                            ->preparePayment()
                            ->saveOrder()
                            ->chargePayment();
                    } else {
                        // standard
                        $this->insertOrder();
                        $paymentGatewayObject = new \Indeed\Ihc\PaymentGateways\Mollie();
                    }
                }
                break;
            case 'paypal_express_checkout':
                if (ihc_check_payment_available('paypal_express_checkout')) {
                    if (ihc_payment_workflow() == 'new') {
                        // new
                        $paymentGatewayObject = new \Indeed\Ihc\Gateways\PayPalExpressCheckout();
                        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                        ->check()
                            ->preparePayment()
                            ->saveOrder()
                            ->chargePayment();
                    } else {
                        // standard
                        $this->insertOrder();
                        $paymentGatewayObject = new \Indeed\Ihc\PaymentGateways\PayPalExpressCheckout();
                    }
                }
                break;
            case 'pagseguro':
                if (ihc_check_payment_available('pagseguro')) {
                    if (ihc_payment_workflow() == 'new') {
                        // new
                        $paymentGatewayObject = new \Indeed\Ihc\Gateways\Pagseguro();
                        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                        ->check()
                            ->preparePayment()
                            ->saveOrder()
                            ->chargePayment();
                    } else {
                        // standard
                        $this->insertOrder();
                        $paymentGatewayObject = new \Indeed\Ihc\PaymentGateways\Pagseguro();
                    }
                }
                break;
            case 'stripe_checkout_v2':
                if (ihc_check_payment_available('stripe_checkout_v2')) {
                    if (ihc_payment_workflow() == 'new') {
                        // new
                        $paymentGatewayObject = new \Indeed\Ihc\Gateways\StripeCheckout();
                        return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                        ->check()
                            ->preparePayment()
                            ->saveOrder()
                            ->chargePayment();
                    } else {
                        // standard
                        $this->insertOrder();
                        $paymentGatewayObject = new \Indeed\Ihc\PaymentGateways\StripeCheckoutV2();
                    }
                }
                break;
            case 'bank_transfer':
                if (ihc_check_payment_available('bank_transfer')) {
                    $paymentGatewayObject = new \Indeed\Ihc\Gateways\BankTransfer();
                    return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                    ->check()
                        ->preparePayment()
                        ->saveOrder()
                        ->chargePayment();
                }
                break;
            case 'twocheckout':
                $paymentGatewayObject = new \Indeed\Ihc\Gateways\TwoCheckout();
                return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                ->check()
                    ->preparePayment()
                    ->saveOrder()
                    ->chargePayment();
                break;
            default:
                $paymentGatewayObject = apply_filters('ihc_payment_gateway_create_payment_object', false, $this->paymentGateway);
                // @description

                if (!$paymentGatewayObject) {
                    $this->doRedirectBack();
                }
                break;
        }
        if (!empty($paymentGatewayObject)) {
            if (ihc_payment_workflow() == 'new') {
                return $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                ->check()
                    ->preparePayment()
                    ->saveOrder()
                    ->chargePayment();
            } else {
                return $paymentGatewayObject->setAttributes($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                ->initDoPayment() // logs, check if level is not free
                ->doPayment() // processing payment data
                ->redirect(); // redirect to payment service
            }

        }
    }

    private function doRedirectBack()
    {
        if (empty($this->returnUrl)) {
            return;
        }
        wp_redirect($this->returnUrl);
        exit;
    }

}
