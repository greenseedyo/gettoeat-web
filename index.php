<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/payment_methods.php');

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}
$events = $store->getCurrentEvents();
$tables_file = "{$_SERVER['DOCUMENT_ROOT']}/tables/{$store->account}.html";

$categories = $store->getOnlineCategories();
$tables_info = $store->getTablesInfo();
$helper = $tables_info->getHelper();
$total_height = $helper->getTotalHeight();
$total_width = $helper->getTotalWidth();
$tables = $helper->getTables();

$factory = new Helpers\PaymentMethodFactory;
$payment_method_keys = $store->getPaymentMethodKeys();
$payment_method_items = $factory->getItemsByKeys($payment_method_keys);

include(VIEWS_DIR . '/index.html');
