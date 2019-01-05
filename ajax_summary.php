<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/ShiftHelper.php');
require_once(ROOT_DIR . '/helpers/payment_methods.php');

use Helpers\PaymentMethodFactory;
use Helpers\ShiftHelper;

$t = new Bill;
$today_bills = $store->getTodayPaidBills();
$today_sales = $today_bills->sum('price');
$today_bill_ids = $today_bills->toArray('id');
$today_payment_method_items = PaymentMethodFactory::getAllItems();
foreach ($today_payment_method_items as $item) {
    $filtered_bills = $today_bills->filterByPaymentMethodKey($item->getKey());
    $payment_method_sum = $filtered_bills->sum('price');
    $item->setProperty('sum', $payment_method_sum);
}

$previous_shift = $store->shifts->search(1)->order('created_at DESC')->first();
if ($previous_shift) {
    $now = time();
    $bills = $store->bills->search("`paid_at` BETWEEN {$previous_shift->created_at} AND {$now}");
} else {
    $bills = $today_bills;
}
$cash_sales = $bills->filterByPaymentMethodKey(Store::PAYMENT_METHOD_CASH)->sum('price');

$previous_float = $previous_shift ? $previous_shift->getFloat() : "";

$staff_names = $store->getCurrentStaffs()->toArray('name');

$adjustment_types = array(
    Shift::ADJUSTMENT_TAKEOUT => ShiftHelper::getAdjustmentTypeText(Shift::ADJUSTMENT_TAKEOUT),
    Shift::ADJUSTMENT_ADD => ShiftHelper::getAdjustmentTypeText(Shift::ADJUSTMENT_ADD),
    Shift::ADJUSTMENT_PASS => ShiftHelper::getAdjustmentTypeText(Shift::ADJUSTMENT_PASS),
);

include(VIEWS_DIR . '/index/partial/summary.html');

