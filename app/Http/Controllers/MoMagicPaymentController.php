<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\MoMagic\MoMagicConnector;

class MoMagicPaymentController extends Controller
{

    public function checkout()
    {
        return view('checkout');
    }

    public function order(Request $request)
    {
        # Organize the checkout data
        $post_data = array();
        # please fill up all the fields
        $post_data['currency'] = "BDT"; // string (3)	Mandatory - The currency type must be mentioned.
        $post_data['amount'] = $request->input('amount') ? filter_var($request->input('amount'), FILTER_SANITIZE_STRING) : "0.00"; # amount must >= 10
        $post_data['amount'] = sprintf("%.2f", ceil($post_data['amount']));
        if ($post_data['amount'] < 10) {
            echo "Minimum amount must be greater than 10 BDT.";
            die;
        }
        $post_data['order_id'] = "MMBD_" . uniqid(); // Mandatory-field, you can replace this value with your own value and it must be unique
        $post_data['cus_name'] = $request->input('customer_name') ? filter_var($request->input('customer_name'), FILTER_SANITIZE_STRING) : "UNKNOWN";
        $post_data['cus_email'] = $request->input('customer_email') ? filter_var($request->input('customer_email'), FILTER_SANITIZE_EMAIL) : "";
        if (!filter_var($post_data['cus_email'], FILTER_VALIDATE_EMAIL)) {
            $email = $post_data['cus_email'];
            echo "$email is not a valid email address.";
            die;
        }
        if (empty($post_data['cus_email'])) {
            echo "Please give a valid mail address.";
            die;
        }
        $post_data['cus_msisdn'] = $request->input('customer_mobile') ? filter_var($request->input('customer_mobile'), FILTER_SANITIZE_STRING) : "";
        if (empty($post_data['cus_msisdn'])) {
            echo "Please give a valid mobile number.";
            die;
        }
        $post_data['cus_country'] = $request->input('country') ? filter_var($request->input('country'), FILTER_SANITIZE_STRING) : "BD";
        $post_data['cus_state'] = $request->input('state') ? filter_var($request->input('state'), FILTER_SANITIZE_STRING) : "UNKNOWN";
        $post_data['cus_city'] = $request->input('state') ? filter_var($request->input('state'), FILTER_SANITIZE_STRING) : "UNKNOWN";
        $post_data['cus_postcode'] = $request->input('zip') ? filter_var($request->input('zip'), FILTER_SANITIZE_STRING) : "UNKNOWN";
        $post_data['cus_address'] = $request->input('address') ? filter_var($request->input('address'), FILTER_SANITIZE_STRING) : "UNKNOWN";
        $post_data['product_name'] = $request->input('product_name') ? filter_var($request->input('product_name'), FILTER_SANITIZE_STRING) : "UNKNOWN";
        $post_data['num_of_item'] = $request->input('num_of_item') ? filter_var($request->input('num_of_item'), FILTER_SANITIZE_STRING) : 1;

        #Before  going to initiate the payment order status need to insert or update as Pending.
        DB::table('orders')
            ->where('transaction_id', $post_data['order_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_msisdn'],
                'amount' => $post_data['amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_address'],
                'transaction_id' => $post_data['order_id'],
                'currency' => $post_data['currency']
            ]);
        $magic_way = new MoMagicConnector();
        # call payment checkout, it will redirect customer vendor payment channel selection page
        $magic_way->make_checkout($post_data);
    }


    public function success(Request $request)
    {
        echo "<br>Transaction is Successful</br>";
    }

    public function fail(Request $request)
    {
        echo "<br>Unfortunately your Transaction FAILED.</br>";
    }

    public function cancel(Request $request)
    {
        echo "<br>Transaction has been CANCELLED.</br>";
    }

    public function ipn(Request $request)
    {
        #Received all the payment information from the gateway
        $opr = $request->input('opr') ? filter_var($request->input('opr'), FILTER_SANITIZE_STRING) : "";
        $payment_ref_id = $request->input('payment_ref_id') ? filter_var($request->input('payment_ref_id'), FILTER_SANITIZE_STRING) : "";
        $order_id = $request->input('order_id') ? filter_var($request->input('order_id'), FILTER_SANITIZE_STRING) : "";
        $status = $request->input('status') ? filter_var($request->input('status'), FILTER_SANITIZE_STRING) : "";
        if ($opr && $payment_ref_id && $order_id && $status) {
            $magic_way = new MoMagicConnector();
            # call access token API , it will give a JWT token
            $access_token_response = $magic_way->access_token();
            if ($access_token_response['status']) {
                $access_token = $access_token_response['access_token'];
                $payment_validation_response = $magic_way->validate_payment($opr, $order_id, $payment_ref_id, $access_token);
                if ($payment_validation_response['status']) {
                    $payment_verification_status = $payment_validation_response['pay_status'] ? 'Processing' : 'Failed';
                    $ecom_order_id = $payment_validation_response['ecom_order_id'];
                    $order_details = DB::table('orders')
                        ->where('transaction_id', $ecom_order_id)
                        ->select('transaction_id', 'status')->first();
                    if (!isset($order_details->transaction_id)) {
                        echo "Invalid Order ID.";
                        die;
                    }
                    if ($order_details->status === 'Pending') {
                        DB::table('orders')
                            ->where('transaction_id', $ecom_order_id)
                            ->update(['status' => $payment_verification_status]);
                        if ($payment_verification_status === 'Processing') {
                            $message = "Transaction is successfully Completed.";
                        } else {
                            $message = "Unfortunately your Transaction FAILED.";
                        }
                    } else if (in_array($order_details->status, array('Processing', 'Failed'))) {
                        $message = "This order is already processing";
                    } else {
                        $message = "Payment processing done, please contact with service provider";
                    }
                } else {
                    $message = $payment_validation_response['message'];
                }
            } else {
                $message = $access_token_response['message'];
            }
        } else {
            $message = "Invalid payment request.";
        }
        echo htmlentities($message, ENT_QUOTES, 'UTF-8');
        die;
    }
}
