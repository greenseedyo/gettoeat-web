<?php
header('Content-type: text/json');
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

$item_datas = $_POST['item_datas'];
$ordered_at = substr($_POST['ordered_at'], 0, 10);
if (date('H', $ordered_at) > 6) {
    $date = strtotime('today', $ordered_at);
} else {
    $date = strtotime('yesterday', $ordered_at);
}
$data = array(
    'year' => date('Y', $date),
    'month' => date('m', $date),
    'date' => date('d', $date),
    'day' => date('N', $date),
    'price' => 0,
    'ordered_at' => $ordered_at,
    'paid_at' => 0,
    'table' => $_POST['table'],
    'custermers' => $_POST['custermers'],
);

$bill = $store->create_bills($data);
$bill->pay($item_datas, $_POST['event_id']);
echo $bill->id;
