<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/StatHelper.php');

$topic = $_GET['topic'] ?? 'overview';
$day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$start_datetime = (new Datetime($start_date))->add($day_change_interval);
$end_datetime = (new Datetime($end_date))->add(new DateInterval('P1D'))->add($day_change_interval);

$filters = $_GET['filters'] ?? array();

if ($start_datetime >= $end_datetime) {
    echo json_encode(array('error' => true, 'msg' => '開始日期不可大於結束日期'));
    exit;
}

switch ($_GET['period'] ?? null) {
case 'hourly':
    $period_interval = new DateInterval('PT1H');
    $limit_interval = new DateInterval('P8D');  // 連今天算8天
    $limit_hint = "一週";
    break;
case 'daily':
    $period_interval = new DateInterval('P1D');
    $limit_interval = new DateInterval('P1Y1M');  // 連本月算13個月
    $limit_hint = "一年";
    break;
case 'weekly':
    $period_interval = new DateInterval('P1W');
    break;
case 'monthly':
    $period_interval = new DateInterval('P1M');
    break;
case 'yearly':
    $period_interval = new DateInterval('P1Y');
    break;
case 'throughout':
    $period_interval = $start_datetime->diff($end_datetime);
    break;
default:
    echo json_encode(array('error' => true, 'msg' => '請選擇統計週期'));
    exit;
}

if (isset($limit_interval)) {
    $limit_datetime = clone $start_datetime;
    $limit_datetime = $limit_datetime->add($limit_interval);
    if ($limit_datetime < $end_datetime) {
        echo json_encode(array('error' => true, 'msg' => "請縮小日期範圍 (最多{$limit_hint})"));
        exit;
    }
}

$helper = new Helpers\StatHelper($store);
$helper->setTopic($topic);
$helper->setInterval($period_interval);
$helper->setStartDatetime($start_datetime);
$helper->setEndDatetime($end_datetime);

foreach ($filters as $key => $value) {
    $helper->setFilter($key, $value);
}

$stat_result = $helper->getStatResult();

$rtn = array();
foreach ($stat_result->getCharts() as $title => $chart) {
    $rtn[$title] = array(
        'title' => $chart->getTitle(),
        'xAxisCategories' => $chart->getXAxisCategories(),
        'datasets' => $chart->getDataSets(),
    );
}

echo json_encode($rtn);
