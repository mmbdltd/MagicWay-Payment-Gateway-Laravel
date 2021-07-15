<?php
/**
 * PAYMENT GATEWAY CONFIGURATION VALUES
 * To get production/live credentials contact info@momagicbd.com
 */

return [
    'api_url' => env("PAYMENT_GATEWAY_API_URL", "https://sandbox.magicway.io"),
    'api_path' => [
        'payment_initiate' => "/api/V1/payment-initiate",
        'access_token' => "/api/V1/auth/token",
        'payment_status' => "/api/V1/charge/status"
    ],
    'api_credentials' => [
        'store_id' => env("STORE_ID"),
        'store_password' => env("STORE_PASSWORD"),
        'store_user' => env("STORE_USER"),
        'store_email' => env("STORE_EMAIL"),
    ],
    'success_url' => '/success',
    'failed_url' => '/fail',
    'cancel_url' => '/cancel',
    'ipn_url' => '/ipn'
];
