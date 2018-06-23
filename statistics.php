<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/StatHelper.php');

$topic = $_GET['topic'] ?: 'overview';
$day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");

/* 月營收資料 */
$start_month = $_GET['start_month'] ?: date('Y-m', strtotime('-11month'));
$end_month = $_GET['end_month'] ?: date('Y-m', strtotime('this month'));
$period_interval = new DateInterval('P1M');
$start_datetime = (new Datetime($start_month))->add($day_change_interval);
$end_datetime = (new Datetime($end_month))->add($period_interval)->add($day_change_interval);

$helper = new StatHelper($store);
$helper->setTopic($topic);
$helper->setInterval($period_interval);
$helper->setStartDatetime($start_datetime);
$helper->setEndDatetime($end_datetime);
$stat_result = $helper->getStatResult();
$month_names = $stat_result->getPeriodNames();
$month_datasets = $stat_result->getDataSets();

/* 日營收資料 */
$start_day = $_GET['start_day'] ?: date('Y-m-d', strtotime('-29days'));
$end_day = $_GET['end_day'] ?: date('Y-m-d', strtotime('today'));
$period_interval = new DateInterval('P1D');
$start_datetime = (new Datetime($start_day))->add($day_change_interval);
$end_datetime = (new Datetime($end_day))->add($period_interval)->add($day_change_interval);

$helper = new StatHelper($store);
$helper->setTopic($topic);
$helper->setInterval($period_interval);
$helper->setStartDatetime($start_datetime);
$helper->setEndDatetime($end_datetime);
$stat_result = $helper->getStatResult();
$date_names = $stat_result->getPeriodNames();
$date_datasets = $stat_result->getDataSets();

include(VIEWS_DIR . '/statistics.html');
