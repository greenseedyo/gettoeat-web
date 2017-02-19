<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}
$tables_file = "{$_SERVER['DOCUMENT_ROOT']}/tables/{$store->account}.html";

$categories = $store->categories;
$tables_info = $store->getTablesInfo();
$helper = $tables_info->getHelper();
$total_height = $helper->getTotalHeight();
$total_width = $helper->getTotalWidth();
$tables = $helper->getTables();

include(VIEWS_DIR . '/index.html');
