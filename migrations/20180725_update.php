<?php
require_once(__DIR__ . '/include_migration.php');

$sql = "
CREATE TABLE `staff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `off` tinyint(3) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id_off` (`store_id`,`off`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";
$link->query($sql);

$sql = "
CREATE TABLE `staff_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";
$link->query($sql);
