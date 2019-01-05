<?php
require_once 'config.php';

$today_total = $store->getTodayPaidBills()->sum('price');
echo $today_total;

