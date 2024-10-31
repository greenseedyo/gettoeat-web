<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/WorkTimeCalculator.php');

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('today'));
$selected_staff_id = intval($_GET['staff_id'] ?? 0);

$day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");
$start_datetime = (new Datetime($start_date))->add($day_change_interval);
$end_datetime = (new Datetime($end_date))->add(new DateInterval('P1D'))->add($day_change_interval);
$start_at = $start_datetime->getTimestamp();
$end_at = $end_datetime->getTimestamp();
$staff_ids = $store->staffs->toArray('id');
$staff_names = $store->staffs->toArray('name');
$punch_logs = PunchLog::search("`timestamp` BETWEEN {$start_at} AND {$end_at}")->searchIn('staff_id', $staff_ids);
if ($selected_staff_id > 0) {
    $punch_logs = $punch_logs->search(array('staff_id' => $selected_staff_id));
}

$calculator = new Helpers\WorkTimeCalculator;
$datasets = array();
foreach ($punch_logs as $punch_log) {
    $staff_id = $punch_log->staff_id;
    $staff_name = $staff_names[$punch_log->staff_id];
    $punch_time = (new Datetime)->setTimestamp($punch_log->timestamp);
    $day_start_at = $store->getDayStartAt($punch_time);
    $business_date = (new Datetime)->setTimestamp($day_start_at);
    switch ($punch_log->type) {
    case PunchLog::TYPE_IN:
        $type = Helpers\WorkTimeLog::TYPE_IN;
        break;
    case PunchLog::TYPE_OUT:
        $type = Helpers\WorkTimeLog::TYPE_OUT;
        break;
    }

    $log = new Helpers\WorkTimeLog;
    $log
        ->setStaffId($staff_id)
        ->setStaffName($staff_name)
        ->setBusinessDate($business_date)
        ->setPunchType($punch_log->type)
        ->setPunchTime($punch_time);
    $calculator->add($log);
}
$calculator->combineWorkTimeRecords();
$records = $calculator->getRecords();
$total_work_time = $calculator->getTotalWorkTime();

$record_datasets = array();
foreach ($records as $record) {
    $data = array(
        'business_date' => $record->getFormattedBusinessDate('Y-m-d'),
        'staff_id' => $record->getStaffId(),
        'staff_name' => $record->getStaffName(),
        'punch_in' => $record->getFormattedPunchIn('Y-m-d H:i:s'),
        'punch_out' => $record->getFormattedPunchOut('Y-m-d H:i:s'),
        'work_time' => $record->getFormattedWorkTime(),
    );
    $record_datasets[] = $data;
}

include(VIEWS_DIR . "/punch_logs.html");

