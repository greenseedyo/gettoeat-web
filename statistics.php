<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/StatHelper.php');

$topic = $_GET['topic'] ?: 'overview';
$start_month = $_GET['start_month'] ?: date('Y-m', strtotime('-11month'));
$end_month = $_GET['end_month'] ?: date('Y-m', strtotime('this month'));
$start_date = $_GET['start_date'] ?: date('Y-m-d', strtotime('-29days'));
$end_date = $_GET['end_date'] ?: date('Y-m-d', strtotime('today'));

include(VIEWS_DIR . "/statistics/{$topic}.html");
