<?php
require_once(__DIR__ . '/../config.php');

if ('cli' === php_sapi_name()) {
    $mysql_host = readline('MySQL Host: ');
    $mysql_user = readline('User: ');
    $mysql_pass = readline('Password: ');
} else {
    $mysql_host = $db_config['host'];
    $mysql_user = 'root';
    $mysql_pass = getenv('MYSQL_ROOT_PASSWORD');
}

$mysqli = new Mysqli;
$db_config = json_decode(file_get_contents(CONFIG_PATH . '/db.json'), 1);
$mysqli->connect($mysql_host, $mysql_user, $mysql_pass, $db_config['database']);
$mysqli->set_charset("utf8");
$link = new Pix_Table_Db_Adapter_Mysqli($mysqli);
