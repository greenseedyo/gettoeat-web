<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

$today_total = 0;
foreach ($store->getTodayPaidBills() as $bill) {
    $today_total += $bill->price;
}
echo $today_total;

