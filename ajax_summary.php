<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/ShiftHelper.php');

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
$previous_float = $previous_shift ? $previous_shift->getFloat() : "";

$staff_names = $store->getCurrentStaffs()->toArray('name');

$adjustment_types = array(
    Shift::ADJUSTMENT_TAKEOUT => ShiftHelper::getAdjustmentTypeText(Shift::ADJUSTMENT_TAKEOUT),
    Shift::ADJUSTMENT_ADD => ShiftHelper::getAdjustmentTypeText(Shift::ADJUSTMENT_ADD),
    Shift::ADJUSTMENT_PASS => ShiftHelper::getAdjustmentTypeText(Shift::ADJUSTMENT_PASS),
);

include(VIEWS_DIR . '/index/partial/summary.html');

