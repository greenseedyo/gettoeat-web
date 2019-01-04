<?php
require_once 'config.php';

if (!$_POST['type']) {
    echo json_encode(array('error' => true, 'msg' => '請選擇上班或下班'));
    exit;
}

if (!$staff = Staff::getByStoreIdAndCode($store->id, $_POST['code'])) {
    echo json_encode(array('error' => true, 'msg' => '找不到此人員'));
    exit;
}

$error = false;
try {
    $staff->punch($_POST['type']);
    $msg = '打卡完成';
} catch (PunchLogRepeatedException $e) {
    $error = true;
    $msg = '重複打卡';
}

echo json_encode(array('error' => $error, 'msg' => $msg));
