<?php
header('Content-type: text/json');
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/payment_methods.php');

if (!$_POST) {
    $rtn_data = array('error' => true, 'message' => 'request need to be POST');
    die(json_encode($rtn_data));
}

if (!$payment = BillPayment::find(intval($_POST['id']))) {
    $rtn_data = array('error' => true, 'message' => '找不到此筆資料');
    die(json_encode($rtn_data));
}

if (!isset($_POST['column_name']) or !isset($_POST['value'])) {
    $rtn_data = array('error' => true, 'message' => '資料格式不正確');
    die(json_encode($rtn_data));
}

$column_name = $_POST['column_name'];
$value = $_POST['value'];
$data = array($column_name => $value);
$payment->update($data);

echo json_encode(array('error' => false));

