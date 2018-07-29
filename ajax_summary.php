<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

$today_sales = $store->getTodayPaidBills()->sum('price');
$previous_shift = $store->shifts->search(1)->order('created_at DESC')->first();
if ($previous_shift) {
    $now = time();
    $bills = $store->bills->search("`paid_at` BETWEEN {$previous_shift->created_at} AND {$now}");
} else {
    $bills = $store->getTodayPaidBills();
}
$sales = $bills->sum('price');
$open_amount = $previous_shift ? $previous_shift->getFloat() : "";

$user_names = $store->getCurrentUsers()->toArray('name');

include(VIEWS_DIR . '/index/partial/summary.html');

