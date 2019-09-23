# DEVELOPER.md

## Test cases

* Complete a purchase for a simple product using the Credit Card subflow
* Complete a purchase for a simple product using the PayPal subflow

* Complete a purchase for a simple subscription using the Credit Card subflow
* Using WCS_DEBUG and Tools > Scheduled Actions, run woocommerce_scheduled_subscription_payment for that subscription

* Complete a purchase for a simple subscription using the PayPal subflow
* Using WCS_DEBUG and Tools > Scheduled Actions, run woocommerce_scheduled_subscription_payment for that subscription

## Checkout Form Logic

* Since the payment methods can be refreshed as the user enters their billing and
shipping information, the extension's `script.js` periodically checks the state of the
form ( using `wcCheckPaypalBraintree` ) and loads ( `wcLoadPaypalBraintree` ) and
unloads ( `wcUnloadPaypalBraintree` ) PayPal Braintree as necessary.

### Hosted Fields Flow

* The credit card number, CVV and expiration date fields on the checkout form are emitted as
simple DIVs by WC_Gateway_Paypal_Braintree::payment_fields, and then the extension's `script.js`
wcLoadPaypalBraintree function calls `braintree.js` braintree.setup to take them over.

* braintree.setup replaces each of those three fields with a "hosted" iframe served by
braintreegateway.com - this means that the credit card number, CVV and expiration date entries
cannot be seen by the merchant's site's javascript and thus supports PCI compliance.

* When the user clicks the **Place Order** button on the checkout form, `braintree.js` intercepts
the click and requests a nonce (basically a payment token) for the credit card data the
user has entered.  We detect that click as well and interrupt the form submittal that
would happen.  Then, when the nonce request returns from Braintree, the nonce is saved
in a form hidden field ( `#paypalbraintree_nonce` ) and we submit the form.

### PayPal Button on Checkout Form (Checkout with PayPal) Flow

* If the customer would rather not enter their credit card, exp and CVV values, a PayPal
button is also injected into the form by `braintree.js`
* When the user clicks on the **PayPal** button on the checkout form, `braintree.js` intercepts
the click and displays a modal for the customer to sign in to PayPal to complete their payment.  When
the customer completes sign in, a nonce is saved in a form hidden field ( `#paypalbraintree_nonce` )
and we submit the form.
* **NOTE: The currencies supported for Checkout with PayPal are limited compared to the Hosted Fields
flow.  See https://developers.braintreepayments.com/guides/paypal/checkout-with-paypal/javascript/v2#currency-support **

## Debugging

### Checkout with PayPal Button Style Problems

* Unlike the hosted fields, the PayPal Button can pick up styles from the theme. The extension's
`styles.css` resets a few that Storefront was setting, in the context of the PayPal button, but
other themes may need additional styles to be reset.

### Errors during the PayPal Button on Checkout Form Flow

* If you get a 'Sorry we cannot connect to PayPal. Please try again in a few minutes. Try Again?' message
after clicking on the PayPal Button and signing in, open the Network tab in the browser's Developer Tools
and then click 'Try Again?'  You will see a more detailed error message in the response to the GET request
there.

