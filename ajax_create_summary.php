<?php
header('Content-type: text/json');
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

// 營收取出金額不可超過錢櫃結餘總額
if (Shift::ADJUSTMENT_TAKEOUT === $_POST['adjustment_type']
    and $_POST['adjustment_amount'] > $_POST['close_amount']) {
    $rtn_data = array('error' => true, 'message' => '營收取出金額不可超過錢櫃結餘總額');
    die(json_encode($rtn_data));
}

// 檢查 user 是否存在
if (isset($_POST['adjustment_by'])) {
    $user = $store->getCurrentStaffs()->search(array('id' => $_POST['adjustment_by']))->first();
    if (!$user) {
        $rtn_data = array('error' => true, 'message' => '此人員不存在');
        die(json_encode($rtn_data));
    }
}

$data = array(
    'sales' => (float) $_POST['sales'],
    'open_amount' => (float) $_POST['open_amount'],
    'close_amount' => (float) $_POST['close_amount'],
    'paid_in' => (float) $_POST['paid_in'],
    'paid_out' => (float) $_POST['paid_out'],
    'adjustment_type' => (int) $_POST['adjustment_type'],
    'adjustment_amount' => (float) $_POST['adjustment_amount'],
    'adjustment_by' => (int) $_POST['adjustment_by'],
    'closed_by' => (int) $_POST['closed_by'],
);
$shift = $store->create_shifts($data);

echo json_encode(array('error' => false, 'message' => '關帳完成'));
