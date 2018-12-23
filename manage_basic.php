<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/payment_methods.php');

if ($_POST and 'set_payment_methods' === $_POST['form_name']) {
    $new_keys = array_keys($_POST['payment_method_keys'] ?: array());
    $store->setPaymentMethodKeys($new_keys);
    header('Location: manage_basic.php');
}

$current_payment_method_keys = $store->getPaymentMethodKeys();
$all_payment_methods_items = Helpers\PaymentMethodFactory::getAllItems();

include(VIEWS_DIR . '/manage_basic.html');

