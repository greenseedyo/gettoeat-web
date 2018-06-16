<?php
require_once 'config.php';

/* 月營收資料 */
$month_names = array();
$month_turnovers = array();
$month_discounts = array();
for ($i = 11; $i >= 0; $i --) {
    $yearmonth = mktime(0, 0, 0, date('m') - $i, 1, date('Y'));
    $year = date('Y', $yearmonth);
    $month = date('m', $yearmonth);
    $key = "{$year}-{$month}";
    $month_names[] = $key;
    $month_turnovers[$key] = 0;
    $month_discounts[$key] = 0;
    foreach ($store->bills->search(array('year' => $year, 'month' => $month)) as $bill) {
        $month_turnovers[$key] += $bill->price;
        foreach ($bill->discounts as $discount) {
            $month_discounts[$key] += $discount->value;
        }
    }
}

/* 每日營收資料 */
$date_names = array();
$date_turnovers = array();
$date_discounts = array();
for ($i = 30; $i >= 0; $i--) {
    $date_timestamp = mktime(0, 0, 0, date('m'), date('d') - $i, date('Y'));
    $year = date('Y', $date_timestamp);
    $month = date('m', $date_timestamp);
    $date = date('d', $date_timestamp);
    $day = date('D', $date_timestamp);
    $key = "{$month}-{$date}({$day})";
    $date_names[] = $key;
    $date_turnovers[$key] = 0;
    $date_discounts[$key] = 0;
    foreach ($store->bills->search(array('year' => $year, 'month' => $month, 'date' => $date)) as $bill) {
        $date_turnovers[$key] += $bill->price;
        foreach ($bill->discounts as $discount) {
            $date_discounts[$key] += $discount->value;
        }
    }
}

include(VIEWS_DIR . '/statistics.html');
