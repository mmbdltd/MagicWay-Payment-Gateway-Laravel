<?php
namespace App\Library\MoMagic;

abstract class MoMagicAbstraction implements MoMagicInterface
{
    protected $storeId;
    protected $storePassword;
    protected $storeUserName;
    protected $storeUserEmail;
    protected $checkout_api_url;
    protected $access_token_api_url;
    protected $payment_verification_api_url;
    protected $access_token_payload = array();
    protected $payment_validation_payload = array();
    protected $authentication_header = array();

    protected function setStoreId($storeID)
    {
        $this->storeId = $storeID;
    }

    protected function getStoreId()
    {
        return $this->storeId;
    }

    protected function setStorePassword($storePassword)
    {
        $this->storePassword = $storePassword;
    }

    protected function getStorePassword()
    {
        return $this->storePassword;
    }

    protected function setStoreUserName($storeUserName)
    {
        $this->storeUserName = $storeUserName;
    }

    protected function getStoreUserName()
    {
        return $this->storeUserName;
    }

    protected function setStoreUserEmail($storeUserEmail)
    {
        $this->storeUserEmail = $storeUserEmail;
    }

    protected function getStoreUserEmail()
    {
        return $this->storeUserEmail;
    }

    protected function set_checkout_api_url($url)
    {
        $this->checkout_api_url = $url;
    }

    protected function get_checkout_api_url()
    {
        return $this->checkout_api_url;
    }

    protected function set_access_token_api_url($url)
    {
        $this->access_token_api_url = $url;
    }

    protected function get_access_token_api_url()
    {
        return $this->access_token_api_url;
    }

    protected function set_payment_verification_api_url($url)
    {
        $this->payment_verification_api_url = $url;
    }

    protected function get_payment_verification_api_url()
    {
        return $this->payment_verification_api_url;
    }

    public function checkout_api(array $data)
    {
        #Set API header
        $this->set_authentication_api_request_header();
        # Set API URL
        $payment_initiator_api_url = $this->get_checkout_api_url();
        # convert array to json
        $payload = $this->array_to_json($data);
        # finally call payment gateway
        return $this->do_api_request($payment_initiator_api_url, $payload, $this->authentication_header);
    }

    public function access_token_api()
    {
        // Set API data
        $this->set_access_token_api_data();
        #Set  API header
        $this->set_authentication_api_request_header();
        # Set API URL
        $access_token_api_url = $this->get_access_token_api_url();
        # convert array to json
        $payload = $this->array_to_json($this->access_token_payload);
        # finally call payment gateway
        return $this->do_api_request($access_token_api_url, $payload, $this->authentication_header);
    }

    public function payment_verification_api($opr = "", $order_id = "", $payment_ref_id = "", $access_token = "")
    {
        // Set API data
        $this->set_payment_validation_api_data($opr, $order_id, $payment_ref_id);
        #Set  API header
        $this->set_authentication_api_request_header($access_token);
        # Set API URL
        $payment_validation_api_url = $this->get_payment_verification_api_url();
        # convert array to json
        $payload = $this->array_to_json($this->payment_validation_payload);
        # finally call payment gateway
        return $this->do_api_request($payment_validation_api_url, $payload, $this->authentication_header);
    }

    private function do_api_request($api_url, $data, $header)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $header
        ));
        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErrorNo = curl_errno($curl);
        curl_close($curl);
       // echo $response;die;
        if ($code == 200 & !($curlErrorNo)) {
            return $response;
        } else {
            return "FAILED TO CONNECT WITH MagicWay API";
        }
    }

    public function set_access_token_api_data()
    {
        $this->access_token_payload["store_id"] = $this->getStoreId();
        $this->access_token_payload["store_secret"] = $this->getStorePassword();
        $this->access_token_payload["grant_type"] = "password";
        $this->access_token_payload["username"] = $this->getStoreUserName();
        $this->access_token_payload["email"] = $this->getStoreUserEmail();
    }

    public function set_payment_validation_api_data($opr = "", $order_id = "", $payment_ref_id = "")
    {
        $this->payment_validation_payload["store_id"] = $this->getStoreId();
        $this->payment_validation_payload["opr"] = $opr;
        $this->payment_validation_payload["order_id"] = $order_id;
        $this->payment_validation_payload["reference_id"] = $payment_ref_id;
        $this->payment_validation_payload["is_plugin"] = "YES";
    }

    public function parse_checkout_response($response)
    {
        $magic_way_payment_initiate_response = $this->json_to_array($response);
        if (isset($magic_way_payment_initiate_response['checkout_url']) && $magic_way_payment_initiate_response['checkout_url'] != "") {
            $parse_data = ['status' => true, 'checkout_url' => $magic_way_payment_initiate_response['checkout_url']];
        } else {
            $parse_data = ['status' => false, 'message' => $magic_way_payment_initiate_response['message']];
        }
        return $parse_data;
    }

    public function parse_access_token_response($response)
    {
        $access_token_response = $this->json_to_array($response);
        if (isset($access_token_response['access_token']) && $access_token_response['access_token'] != "") {
            $parse_data = ['status' => true, 'access_token' => $access_token_response['access_token']];
        } else {
            $parse_data = ['status' => false, 'message' => $access_token_response['message']];
        }
        return $parse_data;
    }

    public function parse_payment_validation_response($response)
    {
        $payment_validation_response = $this->json_to_array($response);
        if (isset($payment_validation_response['success']) && $payment_validation_response['success']) {
            $pay_status = $payment_validation_response['charge_status'] === "Success" ? true : false;
            $parse_data = ['status' => true, 'pay_status' => $pay_status, 'ecom_order_id' => $payment_validation_response['ecom_order_id']];
        } else {
            $parse_data = ['status' => false, 'message' => $payment_validation_response['message']];
        }
        return $parse_data;
    }


    public function post_redirection($redirect_url)
    {
        return '<form action="' . $redirect_url . '" method="post" id="magic_way_payment_form">
	                <input type="submit" class="button-alt" id="submit_magic_way_payment_form" value="Pay via magic way" style="visibility: hidden" />
	                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	                <script type="text/javascript">
	                    (function(){
	                        $("#submit_magic_way_payment_form").click();
	                    })();
	                </script>
	            </form>';
    }

    public function set_authentication_api_request_header($access_token = "")
    {
        if ($access_token) {
            $this->authentication_header = array(
                "content-type: application/json",
                "Authorization: Bearer $access_token"
            );
        } else {
            $this->authentication_header = array(
                "content-type: application/json"
            );
        }
    }

    private function array_to_json(array $data)
    {
        return json_encode($data);
    }

    private function json_to_array($data="")
    {
        return json_decode($data, true);
    }
}
