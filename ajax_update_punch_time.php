<?php
header('Content-type: text/json');
require_once 'config.php';

if (!$_POST) {
    $rtn_data = array('error' => true, 'message' => 'request need to be POST');
    die(json_encode($rtn_data));
}

if (!$business_date = $_POST['business_date']) {
    $rtn_data = array('error' => true, 'message' => 'invalid business_date');
    die(json_encode($rtn_data));
}
$day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");
$start_datetime = (new Datetime($business_date))->add($day_change_interval);
$end_datetime = (new Datetime($business_date))->add(new DateInterval('P1D'))->add($day_change_interval);
$start_at = $start_datetime->getTimestamp();
$end_at = $end_datetime->getTimestamp();

if (!$staff = Staff::find($_POST['staff_id'])) {
    $rtn_data = array('error' => true, 'message' => 'invalid staff_id');
    die(json_encode($rtn_data));
}

if ($time = $_POST['punch_in'] ?? false) {
    $type = PunchLog::TYPE_IN;
} elseif ($time = $_POST['punch_out'] ?? false) {
    $type = PunchLog::TYPE_OUT;
} else {
    $rtn_data = array('error' => true, 'message' => 'invalid punch type');
    die(json_encode($rtn_data));
}
$timestamp = strtotime($time);

if ($timestamp < $start_at) {
    $rtn_data = array('error' => true, 'message' => '打卡時間不得早於' . date('Y-m-d H:i:s', $start_at));
    die(json_encode($rtn_data));
} elseif ($timestamp >= $end_at) {
    $rtn_data = array('error' => true, 'message' => '打卡時間不得晚於' . date('Y-m-d H:i:s', $end_at));
    die(json_encode($rtn_data));
}

$punch_in_log = PunchLog::search("`timestamp` BETWEEN {$start_at} AND {$end_at}")
    ->search(array('staff_id' => $staff->id, 'type' => PunchLog::TYPE_IN))
    ->order('timestamp ASC')
    ->first();
$punch_out_log = PunchLog::search("`timestamp` BETWEEN {$start_at} AND {$end_at}")
    ->search(array('staff_id' => $staff->id, 'type' => PunchLog::TYPE_OUT))
    ->order('timestamp DESC')
    ->first();

if (PunchLog::TYPE_IN == $type) {
    if ($punch_out_log and $timestamp > $punch_out_log->timestamp) {
        $rtn_data = array('error' => true, 'message' => '上班時間不可晚於下班時間');
        die(json_encode($rtn_data));
    }
    if ($punch_in_log) {
        $punch_in_log->update(array('timestamp' => $timestamp));
    } else {
        $data = array(
            'type' => $type,
            'timestamp' => $timestamp,
        );
        $punch_in_log = $staff->create_punch_logs($data);
    }
} elseif (PunchLog::TYPE_OUT == $type) {
    if ($punch_in_log and $timestamp < $punch_in_log->timestamp) {
        $rtn_data = array('error' => true, 'message' => '下班時間不可早於下班時間');
        die(json_encode($rtn_data));
    }
    if ($punch_out_log) {
        $punch_out_log->update(array('timestamp' => $timestamp));
    } else {
        $data = array(
            'type' => $type,
            'timestamp' => $timestamp,
        );
        $punch_out_log = $staff->create_punch_logs($data);
    }
}

echo json_encode(array('error' => false, 'escaped_new_value' => date('Y-m-d H:i:s', $timestamp)));

