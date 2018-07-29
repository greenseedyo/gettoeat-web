<?php
require_once(__DIR__ . '/../config.php');

$mysqli = new Mysqli;
$db_config = json_decode(file_get_contents(CONFIG_PATH . '/db.json'), 1);
$mysqli->connect($db_config['host'], 'root', getenv('MYSQL_ROOT_PASSWORD'), $db_config['database']);
$mysqli->set_charset("utf8");
$link = new Pix_Table_Db_Adapter_Mysqli($mysqli);

$sql = "
CREATE TABLE `staff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activated` tinyint(4) NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id_activated` (`store_id`,`activated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";
$link->query($sql);

$sql = "
CREATE TABLE `shift` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `sales` double unsigned NOT NULL,
  `open_amount` double unsigned NOT NULL,
  `close_amount` double unsigned NOT NULL,
  `paid_in` double unsigned NOT NULL,
  `paid_out` double unsigned NOT NULL,
  `adjustment_type` int(10) unsigned NOT NULL,
  `adjustment_amount` double unsigned NOT NULL,
  `adjustment_by` int(10) unsigned NOT NULL,
  `closed_by` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `adjustment_by` (`adjustment_by`),
  KEY `closed_by` (`closed_by`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";
$link->query($sql);
