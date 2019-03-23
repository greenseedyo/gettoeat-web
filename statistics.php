<?php
require_once 'config.php';

$topic = $_GET['topic'] ?: 'overview';
$start_date = $_GET['start_date'] ?: date('Y-m-d', strtotime('-28days'));
$end_date = $_GET['end_date'] ?: date('Y-m-d', strtotime('today'));
$selected_period = $_GET['period'] ?: 'daily';

switch ($topic) {
case 'product':
    $valid_periods = array(
        'throughout' => '加總',
    );
    $categories = $store->categories->toArray(array('id', 'name'));
    $filtered_category_id = $_GET['filters']['category_id'];
    $filtered_custermers = $_GET['filters']['custermers'];
    break;
default:
    $valid_periods = array(
        'hourly' => '一小時',
        'daily' => '一天',
        'weekly' => '一週',
        'monthly' => '一月',
        'yearly' => '一年',
        'throughout' => '加總',
    );
    break;
}

include(VIEWS_DIR . "/statistics/{$topic}.html");
