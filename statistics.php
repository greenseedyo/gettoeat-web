<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/StatHelper.php');

$topic = $_GET['topic'] ?: 'overview';
$start_date = $_GET['start_date'] ?: date('Y-m-d', strtotime('-28days'));
$end_date = $_GET['end_date'] ?: date('Y-m-d', strtotime('today'));
$selected_period = $_GET['period'] ?: 'daily';

switch ($topic) {
case 'product':
case 'shift':
    $valid_periods = array(
        'throughout' => '加總',
    );
    break;
default:
    $valid_periods = array(
        'daily' => '一天',
        'weekly' => '一周',
        'monthly' => '一月',
        'yearly' => '一年',
        'throughout' => '加總',
    );
    break;
}

include(VIEWS_DIR . "/statistics/{$topic}.html");
