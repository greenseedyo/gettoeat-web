<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/ShiftHelper.php');

$start_date = $_GET['start_date'] ?: date('Y-m-d', strtotime('-28days'));
$end_date = $_GET['end_date'] ?: date('Y-m-d', strtotime('today'));

$day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");
$start_datetime = (new Datetime($start_date))->add($day_change_interval);
$end_datetime = (new Datetime($end_date))->add(new DateInterval('P1D'))->add($day_change_interval);
$start_at = $start_datetime->getTimestamp();
$end_at = $end_datetime->getTimestamp();
$shifts = Shift::search("`created_at` BETWEEN {$start_at} AND {$end_at}");
$datasets = array();
foreach ($shifts as $shift) {
    $datetime = new Datetime(date('c', $shift->created_at));
    $business_date = date('Y-m-d', $store->getDayStartAt($datetime));
    $dataset = array(
        'business_date' => $business_date,
        'sales' => $shift->sales,
        'open_amount' => $shift->open_amount,
        'close_amount' => $shift->close_amount,
        'paid_in' => $shift->paid_in,
        'paid_out' => $shift->paid_out,
        'expected_amount' => $shift->getExpectedAmount(),
        'difference' => $shift->getDifference(),
        'adjustment_type_text' => ShiftHelper::getAdjustmentTypeText($shift->adjustment_type),
        'adjustment_amount' => $shift->adjustment_amount,
        'adjustment_by' => $shift->getAdjustmentByInText() ?: '老闆 (未設定)',
        'float' => $shift->getFloat(),
        'created_at' => $shift->created_at,
    );
    $datasets[] = $dataset;
}

include(VIEWS_DIR . "/shift_records.html");
