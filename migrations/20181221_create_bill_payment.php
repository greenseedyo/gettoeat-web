<?php
require_once(__DIR__ . '/include_migration.php');

$sql = "
CREATE TABLE IF NOT EXISTS `bill_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `payment_method` tinyint(3) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_id` (`bill_id`),
  KEY `payment_method` (`payment_method`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";
$link->query($sql);

$sql = "
ALTER TABLE `store` ADD `payment_method_keys` varchar(255)
";
$link->query($sql);
