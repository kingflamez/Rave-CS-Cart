<?php
use Tygh\Registry;


$requeryCount = 0;

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


if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

// Rave Requery
function requery($requeryCount, $mode, $seckey)
{
    $stagingUrl = 'http://flw-pms-dev.eu-west-1.elasticbeanstalk.com/';
    $liveUrl = 'https://api.ravepay.co/';
    $apiLink = $stagingUrl;


    $url = fn_url("payment_notification.return?payment=rave");
    if ($mode == 'live') {
        $apiLink = $liveUrl;
    }

    $txref = $_GET['txref'];
    $requeryCount++;
    $data = array(
        'txref' => $txref,
        'SECKEY' => $seckey,
        'last_attempt' => '1'
        // 'only_successful' => '1'
    );
    // make request to endpoint.
    $data_string = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiLink . 'flwv3-pug/getpaidx/api/xrequery');
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
    $resp = json_decode($response, false);

    if ($resp && $resp->status === "success") {
        if ($resp && $resp->data && $resp->data->status === "successful") {
            return $resp->data;
        } elseif ($resp && $resp->data && $resp->data->status === "failed") {
            return false;
        } else {
                // I will requery again here. Just incase we have some devs that cannot setup a queue for requery. I don't like this.
            if ($requeryCount > 4) {
                return false;
            } else {
                sleep(3);
                return requery($requeryCount, $mode, $seckey);
            }
        }
    } else {
        if ($requeryCount > 4) {
            return false;
        } else {
            sleep(3);
            return requery($requeryCount, $mode, $seckey);
        }
    }
}

// Return from payment
if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'return' && !empty($_REQUEST['txref'])) {
        if (isset($view) === false) {
            $view = Registry::get('view');
        }

        $view->assign('order_action', __('placing_order'));
        $view->display('views/orders/components/placing_order.tpl');
        fn_flush();
        $code = $_REQUEST['txref'];
        $txref = fn_rave_place_order($_REQUEST['txref']);
        $amount = $_REQUEST['amount'];

        

        if (!empty($txref) and !empty($amount)) {
            if (fn_check_payment_script('rave.php', $txref, $processor_data)) {


                $order_info = fn_get_order_info($txref);

                $maintotal = $order_info['total'] + $order_info['payment_surcharge'];
                $maintotal = fn_rave_adjust_amount($maintotal, $order_info['secondary_currency']);
                $pp_response = array();
                $success = false;
                $error = "";
                $mode = $processor_data['processor_params']['rave_env'];
                if ($mode) {
                    # code...
                }
                $seckey = $processor_data['processor_params']['rave_test_sk'];
                if ($mode == 'live') {
                    $seckey = $processor_data['processor_params']['rave_live_sk'];
                }
                

                $verification = requery(0, $mode, $seckey);

                if (($verification === false)) {
                    $success = false;
                    $error = "";
                } else {
                    if ($maintotal == ($verification->amount)) {
                        if ($order_info['secondary_currency'] == $verification->currency ) {
                            $success = true;
                        } else {
                            $success = false;
                            $error = "Invalid Currency";
                        }
                        
                    } else {
                        $success = false;
                        $error = "Invalid Amount";
                    }
                }


                if ($success === true) {
                    $pp_response['order_status'] = 'P';
                    $pp_response['transaction_id'] = $code;
                    $pp_response['Status'] = 'Payment Successful';
                    fn_finish_payment($txref, $pp_response);
                    fn_order_placement_routines('route', $txref);
                } else {
                    $pp_response['order_status'] = 'O';
                    $pp_response['transaction_id'] = $code;
                    $pp_response['Status'] = 'Payment Failed';

                    fn_finish_payment($txref, $pp_response);
                    fn_set_notification('E', __('error'), 'Payment Failed #' . $code);
                    fn_order_placement_routines('checkout_redirect');
                }

            }
        } else {
            fn_set_notification('E', __('error'), 'Payment Unsuccessful' . $_REQUEST['merchant_order_id']);
            fn_order_placement_routines('checkout_redirect');
        }
    }
    exit;
} else {

    $stagingUrl = 'https://rave-api-v2.herokuapp.com';
    $liveUrl = 'https://api.ravepay.co';
    $baseUrl = $stagingUrl;

    $maintotal = $order_info['total'] + $order_info['payment_surcharge'];
    $maintotal = fn_rave_adjust_amount($maintotal, $order_info['secondary_currency']);

    $url = fn_url("payment_notification.return?payment=rave&amount=". $maintotal);
    $mode = $processor_data['processor_params']['rave_env'];

    $publicKey = $processor_data['processor_params']['rave_test_pk'];
    $secretKey = $processor_data['processor_params']['rave_test_sk'];

    if ($mode == 'live') {
        $baseUrl = $liveUrl;
        $publicKey = $processor_data['processor_params']['rave_live_pk'];
        $secretKey = $processor_data['processor_params']['rave_live_sk'];
    }

    $postfields = array();
    $postfields['PBFPubKey'] = $publicKey;
    $postfields['customer_email'] = $order_info['email'];
    $postfields['customer_firstname'] = $order_info['firstname'];
    $postfields['custom_logo'] = $processor_data['processor_params']['rave_logo'];
    $postfields['customer_lastname'] = $order_info['firstname'];
    $postfields['country'] = $processor_data['processor_params']['rave_country'];
    $postfields['redirect_url'] = $url;
    $postfields['txref'] = $order_id;
    $postfields['payment_method'] = $processor_data['processor_params']['payment_method'];
    $postfields['amount'] = $maintotal + 0;
    $postfields['currency'] = $order_info['secondary_currency'];
    $postfields['hosted_payment'] = 1;
    ksort($postfields);
    $stringToHash = "";
    foreach ($postfields as $key => $val) {
        $stringToHash .= $val;
    }
    $stringToHash .= $secretKey;
    $hashedValue = hash('sha256', $stringToHash);
    $transactionData = array_merge($postfields, array('integrity_hash' => $hashedValue));


    $html = "<script type='text/javascript' src='" . $baseUrl . "/flwv3-pug/getpaidx/api/flwpbf-inline.js'></script>
		    <script>
		    document.addEventListener('DOMContentLoaded', function(event) {
			    var data = JSON.parse('" . json_encode($transactionData = array_merge($postfields, array('integrity_hash' => $hashedValue))) . "');
			    getpaidSetup(data);
			});
		    </script>";

    echo <<<EOT
    {$html}
</body>
</html>
EOT;
    exit;
}

?>
