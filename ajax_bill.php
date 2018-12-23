<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/payment_methods.php');

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

if ($_GET['id']) {
    $bill = $store->getBillById($_GET['id']);
} else {
    $bill = $store->getTodayPaidBills()->order('paid_at DESC')->first();
}

$paid_at = intval($bill->paid_at);
if ($prev_bill = $store->getTodayPaidBills()->search("paid_at < {$paid_at}")->order('paid_at DESC')->first()) {
    $prev_bill_id = $prev_bill->id;
}
if ($next_bill = $store->getTodayPaidBills()->search("paid_at > {$paid_at}")->order('paid_at ASC')->first()) {
    $next_bill_id = $next_bill->id;
}

$factory = new Helpers\PaymentMethodFactory;
$payment_method_items = array();
foreach ($bill->payments as $payment) {
    $item = $factory->getItemByBillPaymentRow($payment);
    $payment_method_items[] = $item;
}

include(VIEWS_DIR . '/index/partial/bill.html');
