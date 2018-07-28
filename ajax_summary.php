<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

$net_sales = $store->getTodayPaidBills()->sum('price');
$card_sales = 0;
$cash_sales = $net_sales;
$previous_shift = $store->shifts->search(1)->order('created_at DESC')->first();
$open_amount = $previous_shift ? $previous_shift->float : "";

$user_names = $store->getCurrentUsers()->toArray('name');

include(VIEWS_DIR . '/index/partial/summary.html');

