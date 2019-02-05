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

if (!isset($_POST['payment_method'])) {
    $rtn_data = array('error' => true, 'message' => '資料格式不正確');
    die(json_encode($rtn_data));
}

$data = array('payment_method' => $_POST['payment_method']);
$payment->update($data);

echo json_encode(array('error' => false));

