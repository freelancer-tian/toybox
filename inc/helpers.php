<?php
if (!defined('ABSPATH')) { exit; }


function toybox_money_i18n($amount) {
return wc_price($amount, ['currency' => get_woocommerce_currency()]);
}