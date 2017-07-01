<?php

$environment = getenv("ENV");

switch ($environment) {
case 'development':
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', true);
    define('STATIC_VERSION', time());
    break;
case 'production':
default:
    error_reporting(E_ERROR & E_WARNING);
    ini_set('display_errors', false);
    define('STATIC_VERSION', md5(file_get_contents(__DIR__ . '/fingerprint.txt')));
    break;
}

session_start();
date_default_timezone_set('Asia/Taipei');
define('VIEWS_DIR', $_SERVER['DOCUMENT_ROOT'] . "/views");
define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT']);
error_reporting(E_ERROR | E_WARNING);
include(ROOT_DIR . '/pixframework/Pix/Loader.php');
set_include_path(ROOT_DIR . '/pixframework/' . PATH_SEPARATOR . ROOT_DIR . '/models/');
Pix_Loader::registerAutoload();

define('CONFIG_PATH', __DIR__ . "/../config/{$environment}");

$db_config = json_decode(file_get_contents(CONFIG_PATH . '/db.json'), 1);
$link = new Mysqli;
$link->connect($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);
$link->set_charset("utf8");
Pix_Table::setDefaultDb(new Pix_Table_Db_Adapter_Mysqli($link));

$store_account = explode('.', $_SERVER['HTTP_HOST'])[0];
$_SESSION['store_account'] = $store_account;

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

/*
if (!getenv('DATABASE_URL')) {
die('need DATABASE_URL');
}
if (!preg_match('#mysql://([^:]*):([^@]*)@([^/]*)/(.*)#', strval(getenv('DATABASE_URL')), $matches)) {
die('mysql only');
}
$db = new StdClass;
$db->host = $matches[3];
$db->username = $matches[1];
$db->password = $matches[2];
$db->dbname = $matches[4];
$config = new StdClass;
$config->master = $config->slave = $db;
Pix_Table::setDefaultDb(new Pix_Table_Db_Adapter_MysqlConf(array($config)));
 */
