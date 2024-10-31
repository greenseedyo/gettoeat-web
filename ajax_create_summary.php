<?php
header('Content-type: text/json');
require_once 'config.php';

// 營收取出金額不可超過錢櫃實際現金
if (Shift::ADJUSTMENT_TAKEOUT === $_POST['adjustment_type']
    and $_POST['adjustment_amount'] > $_POST['close_amount']) {
    $rtn_data = array('error' => true, 'message' => '營收取出金額不可超過錢櫃實際現金');
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
    'cash_sales' => (float)($_POST['cash_sales'] ?? 0),
    'open_amount' => (float)($_POST['open_amount'] ?? 0),
    'close_amount' => (float)($_POST['close_amount'] ?? 0),
    'paid_in' => (float)($_POST['paid_in'] ?? 0),
    'paid_out' => (float)($_POST['paid_out'] ?? 0),
    'adjustment_type' => (int)($_POST['adjustment_type'] ?? 0),
    'adjustment_amount' => (float)($_POST['adjustment_amount'] ?? 0),
    'adjustment_by' => (int)($_POST['adjustment_by'] ?? 0),
    'closed_by' => (int)($_POST['closed_by'] ?? 0),
);
$shift = $store->create_shifts($data);

echo json_encode(array('error' => false, 'message' => '關帳完成'));
