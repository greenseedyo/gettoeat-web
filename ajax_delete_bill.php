<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

if ($bill = $store->getBillById($_POST['id'])) {
    foreach ($bill->items as $item) {
        $item->delete();
    }
    foreach ($bill->discounts as $discount) {
        $discount->delete();
    }
    $bill->delete();
}
