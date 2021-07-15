### MagicWay Payment Gateway Integration - Laravel Library

__Tags:__ e-commerce, magicway, payment-gateway, checkout, shop, cart, local-payment-gateway, international-payment-gateway

__Requires:__  PHP >= 7.2, Laravel >= 6.0 and MySQL

__License:__ MIT


#### Core Library Directory Structure

```
 |-- config/
    |-- magic_way.php
 |-- app/Library/MoMagic
    |-- MoMagicAbstraction.php (core file)
    |-- MoMagicInterface.php (core file)
    |-- MoMagicConnector.php (core file)
 |-- README.md
 |-- orders.sql
```

#### Instructions:

* __Step 1:__ Download and extract the library files.

* __Step 2:__ Copy the `Library` folder and put it in the laravel project's `app/` directory. If needed, then run `composer dump -o`.

* __Step 3:__ Copy the `config/magic_way.php` file into your project's `config/` folder.

Now, we have already copied the core library files. Let's do copy some other helpers files that is provided to understand the integration process. The other files are not related to core library. 

* __Optional:__ If you later encounter issues with session destroying after redirect, you can set ```'same_site' => null,``` in your `config/session.php` file.

* __Step 4:__ Add `STORE_ID`, `STORE_PASSWORD`,`STORE_USER` and `STORE_EMAIL` values on your project's `.env` file.

* __Step 5:__ Copy the `MoMagicPaymentController` into your project's `Controllers` folder.

* __Step 6:__ Copy the defined routes from `routes/web.php` into your project's route file.

* __Step 7:__ Add the below routes into the `$excepts` array of `VerifyCsrfToken` middleware.


protected $except = [
    '/success','/fail','/cancel','/ipn'
];



* __Step 8:__ Copy the `resources/views/*.blade.php` files into your project's `resources/views/` folder.


Now, let's go to the main integration part. 

* __Step 9:__ Create a database and import the orders.sql table schema.

* __Step 10:__ For Checkout integration, you can update the MoMagicPaymentController->checkout() or use a different method according to your need. We have provided a basic sample from where you can kickstart the payment gateway integration.

* __Step 11:__ When user click Continue to checkout button, redirect customer to payment channel selection page.

* __Step 12:__ For redirecting action from MagicWay Payment gateway, we have also provided sample success(), fail(), cancel() and ipn() methods in MoMagicPaymentController. You can update those methods according to your need.


### Contributors

>Arifur Rahman

> info@momagicbd.com
