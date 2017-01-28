<?php
require_once 'config.php';

$today_total = 0;
foreach (Bill::getTodayPaidBills() as $bill) {
    $today_total += $bill->price;
}
echo $today_total;

