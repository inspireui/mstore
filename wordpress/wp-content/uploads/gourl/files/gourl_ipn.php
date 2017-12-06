<?php

if (!defined( 'ABSPATH' ) || !defined( 'GOURL_IPN' )) exit; // Exit if accessed directly

/**
	---------------------------------------------------------------------------------
	GoUrl.io IPN (Instant Payment Notification) for Bitcoin/Altcoin Payment Gateway
	
	Optional. You can use additional actions after a payment has been received (update database records, etc).
	Accept IPN for GoUrl Pay-Per-Product, Pay-Per-Download, Pay-Per-Membership, Pay-Per-View only.
	See screenshot - http://gourl.io/images/plugin2.png
	Simply edit this php file and add section with your order_ID in function gourl_successful_payment() below.
	---------------------------------------------------------------------------------
*/


if (!function_exists('gourl_successful_payment')) 
{
	function gourl_successful_payment ($user_ID = 0, $order_ID = "", $payment_details = array(), $box_status = "")
	{
		// --------------------------
		// DON'T EDIT IT
		// --------------------------

		if (!in_array($box_status, array("cryptobox_newrecord", "cryptobox_updated"))) return false;
		if ($order_ID && 
			strpos($order_ID, "product_") !== 0  &&			// Pay-Per-Product, 	example: product_2  (db record crypto_files.fileID = 2)
			strpos($order_ID, "file_") !== 0  &&			// Pay-Per-Download, 	example: file_5		(db record crypto_products.productID = 5)
			$order_ID != "membership"  &&				// Pay-Per-Membership, 	example: membership
			$order_ID != "payperview" )				// Pay-Per-View 	example: payperview
			return false;
	

		// --------------------------
		// PLEASE EDIT BELOW
		// --------------------------

		if ($box_status == "cryptobox_newrecord") // one time processed
		{	
			switch ($order_ID) 
			{
				case "product_1":
					// code to be executed if you sold product with crypto_products.productID = 1
					// user_id -  wordpress user id, $current_user->ID
					break;
					
				case "product_2":
					// code to be executed if you sold product with crypto_products.productID = 2
					// user_id -  wordpress user id, $current_user->ID
					break;
					
				case "file_1":
					// code to be executed if you sold file with crypto_files.fileID = 1
					// user_id -  according "Store Visitor IDs" option  on file edit page. 
					//            if "Store Visitor IDs" = "Registered Users", user_id is wordpress user id, $current_user->ID; 
					//            otherwise user_id is randomly generated string as user identification from unregistered visitor cookies
					break;
					
				case "payperview":
					// code to be executed if unregistered visitor bought membership Pay-Per-View
					// user_id -  randomly generated string as user identification from unregistered visitor cookies
					break;

				case "membership":
					// code to be executed if registered user bought membership Pay-Per-Membership
					// user_id -  wordpress user id, $current_user->ID
					break;
					
			}
		}	
			
		// Debug - send you email every time when function gourl_successful_payment() appear
		//mail('..your_email@gmail.com', 'Debug - gourl_successful_payment(user_ID = '.$user_ID.', order_ID = '.$order_ID.', payment_details = array..., box_status = '.$box_status.')', 'payment_details = '.str_replace(",", ",\n", json_encode($payment_details)));
			
			
		return true;
	}
}
  
  
  
  
/**********************

	-------------------------
	GoUrl IPN Description
	-------------------------
	
	Function gourl_successful_payment ($user_ID = 0, $order_ID = "", $payment_details = array(), $box_status = "")
	
	The function will automatically appear for each new payment usually two times : 
	a) when a new payment is received, with values: $box_status = cryptobox_newrecord, $payment_details[is_confirmed] = 0
	b) and a second time when existing payment is confirmed (6+ confirmations) with values: $box_status = cryptobox_updated, $payment_details[is_confirmed] = 1.
	
	But sometimes if the payment notification is delayed for 20-30min, the payment/transaction will already be confirmed and the function will
	appear once with values: $box_status = cryptobox_newrecord, $payment_details[is_confirmed] = 1
	
	If payment received with correct amount, function receive: $payment_details[status] = 'payment_received' and $payment_details[userID] = 11, 12, etc (user_id who has made payment)
	If incorrectly paid amount, the system can not recognize user; function receive: $payment_details[status] = 'payment_received_unrecognised' and $payment_details[userID] = ''
	
	Function gets user_ID - user who has made payment, current order_ID, box_status - 'cryptobox_newrecord' OR 'cryptobox_updated'  (description above)
	and payment details as array.

	=================================

	1. EXAMPLE - CORRECT PAYMENT - user with ID = 115 has made payment for product with ID = 8.

	// See screenshot - http://gourl.io/images/plugin2.png 
	// Function gourl_successful_payment($user_ID, $order_ID, $payment_details, $box_status) will receive - 
	
	$user_ID = 115;
	$order_ID = "product_8";	
	$box_status	 = "cryptobox_newrecord"
	$payment_details = array(
				"status":			"payment_received",
				"error":			"",
				"is_paid":			1,
				"paymentID":		17,
				"paymentDate":		"2014-12-12 14:10:46",
				"paymentLink":		"http://example.com/wp-admin/admin.php?page=gourlpayments&s=payment_17",
				"addr":				"BTutorcSLL8PQbfTT3Hahc23fJc8bxD1Wz", // your wallet address on gourl.io
				"tx":				"b605bdc2d0e4954f3ffe29c00046c9ce823a756a012248dc137a483a215a5643",
				"is_confirmed":		0,
				"amount":			"0.0390625",
				"amountusd":		"12.5",
				"coinlabel":		"BTC",
				"coinname":			"bitcoin",
				"boxID":			"34",
				"boxtype":			"paymentbox",
				"boxLink":			"https://gourl.io/view/coin_boxes/34/statistics.html",
				"orderID":			"product_8",
				"userID":			"115",
				"usercountry":		"CAN",
				"userLink":			"http://example.com/wp-admin/user-edit.php?user_id=115"
			);
					
	// Second time function will appear with $box_status = "cryptobox_updated" and $payment_details["is_confirmed"] = 1. Other values will be the same


	=================================
		
						
	2. EXAMPLE - INCORRECT PAYMENT/WRONG AMOUNT -
	
	// Function gourl_successful_payment($user_ID, $order_ID, $payment_details, $box_status) will receive -

	$user_ID = "";
	$order_ID = "";	
	$box_status	 = "cryptobox_newrecord"
	$payment_details = array(
				"status":				"payment_received_unrecognised",
				"error":				"An incorrect bitcoin amount has been received",
				"is_paid":				1,
				"paymentID":			18,
				"paymentDate":			"2014-12-12 14:12:33",
				"paymentLink":			"http://example.com/wp-admin/admin.php?page=gourlpayments&s=payment_18",
				"addr":					"BTutorcSLL8PQbfTT3Hahc23fJc8bxD1Wz", // your wallet address on gourl.io
				"tx":					"bds3dewrdsd9c00046c9ce823a756a012248dc137a483a215a5ffd",
				"is_confirmed":			0,
				"amount":				"0.0071875",
				"amountusd":			"2.3",
				"coinlabel":			"BTC",
				"coinname":				"bitcoin",
				"boxID":				"34",
				"boxtype":				"paymentbox",
				"boxLink":				"https://gourl.io/view/coin_boxes/34/statistics.html",
				"orderID":				"",
				"userID":				"",
				"usercountry":			"",
				"userLink":				""
			);

	// Second time function will appear with $box_status = "cryptobox_updated" and $payment_details["is_confirmed"] = 1. Other values will be the same 
      
************************************/
  
?>