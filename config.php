<?php

if (file_exists(__DIR__ . '/debug.php')) {
    require_once(__DIR__ . '/debug.php');
}
define('DEBUG_ENV', true);

if (DEBUG_ENV) {
    define('STATIC_VERSION', time());
} else {
    define('STATIC_VERSION', md5(file_get_contents(__DIR__ . '/fingerprint.txt')));
}

session_start();
date_default_timezone_set('Asia/Taipei');
define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT']);
error_reporting(E_ERROR | E_WARNING);
include(ROOT_DIR . '/pixframework/Pix/Loader.php');
set_include_path(ROOT_DIR . '/pixframework/' . PATH_SEPARATOR . ROOT_DIR . '/models/');
Pix_Loader::registerAutoload();

$config = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../config/db.json'), 1);
$link = new Mysqli;
$link->connect($config['host'], $config['user'], $config['password'], $config['database']);
$link->set_charset("utf8");
Pix_Table::setDefaultDb(new Pix_Table_Db_Adapter_Mysqli($link));

$store_account = explode('.', $_SERVER['HTTP_HOST'])[0];
$_SESSION['store_account'] = $store_account;

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
