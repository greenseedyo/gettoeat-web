<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/StatHelper.php');

$topic = $_GET['topic'] ?: 'overview';
$day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");

if ('monthly' === $_GET['period']) {
    // 按月統計
    $start_month = $_GET['start_month'];
    $end_month = $_GET['end_month'];
    $period_interval = new DateInterval('P1M');
    $start_datetime = (new Datetime($start_month))->add($day_change_interval);
    $end_datetime = (new Datetime($end_month))->add($period_interval)->add($day_change_interval);
} elseif ('daily' === $_GET['period']) {
    // 按日統計
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $period_interval = new DateInterval('P1D');
    $start_datetime = (new Datetime($start_date))->add($day_change_interval);
    $end_datetime = (new Datetime($end_date))->add($period_interval)->add($day_change_interval);
} else {
    echo json_encode(array('error' => true, 'msg' => 'no "period" specified.'));
}

$helper = new StatHelper($store);
$helper->setTopic($topic);
$helper->setInterval($period_interval);
$helper->setStartDatetime($start_datetime);
$helper->setEndDatetime($end_datetime);
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
