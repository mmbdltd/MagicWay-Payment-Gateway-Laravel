<?php
namespace App\Library\MoMagic;

interface MoMagicInterface
{
    public function make_checkout(array $data);

    public function access_token();

    public function validate_payment($opr="",$order_id="",$payment_ref_id="",$access_token="");
}
