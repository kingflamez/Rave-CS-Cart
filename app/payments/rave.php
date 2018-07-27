<?php
use Tygh\Registry;

function fn_rave_adjust_amount($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
        if ($currencies[$payment_currency]['is_primary'] != 'Y') {
            $price = fn_format_price($price / $currencies[$payment_currency]['coefficient']);
        }
    } else {
        return false;
    }

    return $price;
}

function fn_rave_place_order($original_order_id)
{
    $cart = &$_SESSION['cart'];
    $auth = &$_SESSION['auth'];

    list($order_id, $process_payment) = fn_place_order($cart, $auth);

    $data = array(
        'order_id' => $order_id,
        'type' => 'S',
        'data' => TIME,
    );
    db_query('REPLACE INTO ?:order_data ?e', $data);

    $data = array(
        'order_id' => $order_id,
        'type' => 'E', // extra order ID
        'data' => $original_order_id,
    );
    db_query('REPLACE INTO ?:order_data ?e', $data);

    return $order_id;
}




// if (!defined('BOOTSTRAP')) {
//     die('Access denied');
// }

// Return from payment
if (!empty($_REQUEST['txref'])) {
    // if ($mode == 'return' && !empty($_REQUEST['txref'])) {
    require './init_payment.php';
    if (isset($view) === false) {
        $view = Registry::get('view');
    }

        // $view->assign('order_action', __('placing_order'));
        // $view->display('views/orders/components/placing_order.tpl');
        // fn_flush();
    $payment_id = db_get_field("SELECT ?:payments.payment_id FROM ?:payments LEFT JOIN ?:payment_processors ON ?:payment_processors.processor_id = ?:payments.processor_id WHERE ?:payment_processors.processor_script = 'rave.php'");
    $processor_data = fn_get_payment_method_data($payment_id);
    $ref = $_REQUEST['txref'];
    $order_id = fn_rave_place_order(explode("_", $_REQUEST['txref'])[0]);
    $amount = db_get_field("SELECT ?:orders.total FROM ?:orders  WHERE ?:orders.order_id = " . $order_id);
    $currency = $processor_data['processor_params']['rave_currency'];
    $currencyamount = fn_rave_adjust_amount($amount, $currency);

    if (!empty($order_id) and !empty($currencyamount)) {
        if (fn_check_payment_script('rave.php', $order_id, $processor_data)) {
            $mode = $processor_data['processor_params']['rave_mode'];
            if ($mode == 'test') {
                $url = "https://ravesandboxapi.flutterwave.com";
                $PBFPubKey = $processor_data['processor_params']['rave_tpk'];
                $secretKey = $processor_data['processor_params']['rave_tsk'];
            } else {
                $url = "https://api.ravepay.co";
                $PBFPubKey = $processor_data['processor_params']['rave_lpk'];
                $secretKey = $processor_data['processor_params']['rave_lsk'];
            }
            $query = array(
                "SECKEY" => $secretKey,
                "txref" => $ref
            );

            $data_string = json_encode($query);

            $ch = curl_init($url . '/flwv3-pug/getpaidx/api/v2/verify');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            $response = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            curl_close($ch);

            if (curl_errno($ch)) {   // should be 0
                    // curl ended with an error
                $cerr = curl_error($ch);
                curl_close($ch);
                throw new Exception("Curl failed with response: '" . $cerr . "'.");
            }

            $resp = json_decode($response, true);

            $paymentStatus = $resp['data']['status'];
            $chargeResponsecode = $resp['data']['chargecode'];
            $chargeAmount = $resp['data']['amount'];
            $chargeCurrency = $resp['data']['currency'];

            if (($chargeResponsecode == "00" || $chargeResponsecode == "0") && ($chargeAmount == $currencyamount) && ($chargeCurrency == $currency)) {
                $pp_response['order_status'] = 'P';
                $pp_response['transaction_id'] = $ref;
                $pp_response['Status'] = 'Payment Successful';
                    // fn_change_order_status($order_id, 'P');
                fn_finish_payment($order_id, $pp_response, false);
                fn_order_placement_routines('route', $order_id, false);
            } else {
                $pp_response['order_status'] = 'O';
                $pp_response['transaction_id'] = $ref;
                $pp_response['Status'] = 'Payment Failed';

                    // fn_finish_payment($order_id, $pp_response);
                fn_set_notification('E', __('error'), 'Payment Failed #' . $ref);
                fn_order_placement_routines('checkout_redirect');
            }

        }
    } else {
        fn_set_notification('E', __('error'), 'Payment Unsuccessful' . $_REQUEST['merchant_order_id']);
        fn_order_placement_routines('checkout_redirect');
    }

    exit;
} else {
    defined('BOOTSTRAP') or die('Access denied');
    $mode = $processor_data['processor_params']['rave_mode'];
    if ($mode == 'test') {
        $url = "https://ravesandboxapi.flutterwave.com";
        $PBFPubKey = $processor_data['processor_params']['rave_tpk'];
        $secretKey = $processor_data['processor_params']['rave_tsk'];
        $env = "staging";
    } else {
        $url = "https://api.ravepay.co";
        $PBFPubKey = $processor_data['processor_params']['rave_lpk'];
        $secretKey = $processor_data['processor_params']['rave_lsk'];
        $env = "live";
    }

    $total = fn_rave_adjust_amount($order_info['total'], $processor_data['processor_params']['rave_currency']);
    $paymentMethod = $processor_data['processor_params']['rave_payment_method'];
    $country = $processor_data['processor_params']['rave_country'];
    $logo = $processor_data['processor_params']['rave_logo'];
    $currency = $processor_data['processor_params']['rave_currency'];
    $email = $order_info['email'];
    $firstName = $order_info['b_firstname'];
    $lastName = $order_info['b_lastname'];
    $phone = $order_info['phone'];
    $ref = $order_id;

    if ($currency != "NGN") {
        $paymentMethod = "both";
    }

    $html = '<!DOCTYPE html>
  <html>
  <body>

  <form method="POST" action="https://hosted.flutterwave.com/processPayment.php" id="paymentForm">
    <input type="hidden" name="amount" value="' . $total . '" /> <!-- Replace the value with your transaction amount -->
    <input type="hidden" name="payment_method" value="' . $paymentMethod . '" /> <!-- Can be card, account, both (optional) -->
    <input type="hidden" name="logo" value="' . $logo . '" /> <!-- Replace the value with your logo url (optional) -->
    <input type="hidden" name="country" value="' . $country . '" /> <!-- Replace the value with your transaction country -->
    <input type="hidden" name="currency" value="' . $currency . '" /> <!-- Replace the value with your transaction currency -->
    <input type="hidden" name="email" value="' . $email . '" /> <!-- Replace the value with your customer email -->
    <input type="hidden" name="firstname" value="' . $firstName . '" /> <!-- Replace the value with your customer firstname (optional) -->
    <input type="hidden" name="lastname"value="' . $lastName . '" /> <!-- Replace the value with your customer lastname (optional) -->
    <input type="hidden" name="phonenumber" value="' . $phone . '" /> <!-- Replace the value with your customer phonenumber (optional if email is passes) -->
    <input type="hidden" name="ref" value="' . $ref . '_' . time() . '" />
    <input type="hidden" name="env" value="' . $env . '"> <!-- live or staging -->
    <input type="hidden" name="publicKey" value="' . $PBFPubKey . '"> <!-- Put your public key here -->
    <input type="hidden" name="secretKey" value="' . $secretKey . '"> <!-- Put your secret key here -->
    <input type="hidden" name="successurl" value="' . fn_payment_url('current', 'rave.php') . '"> <!-- Put your success url here -->
    <input type="hidden" name="failureurl" value="' . fn_payment_url('current', 'rave.php') . '"> <!-- Put your failure url here -->
    <!-- <input type="submit" value="Submit" /> -->
  </form>
  <script>
    window.onload = function(){
      document.forms["paymentForm"].submit();
    }
  </script>
</body>
</html>';

    echo $html;
}

?>
