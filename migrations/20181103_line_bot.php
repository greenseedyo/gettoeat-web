<?php
require_once(__DIR__ . '/include_migration.php');

$sql = "
CREATE TABLE `gav` (
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`key`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";
$link->query($sql);

$sql = "
INSERT INTO `gav` VALUES ('line-bot-config','{"access_token":"VNX7fSp5YJ7ItpLlSZOXH9Dz0A8JAW\/RQdjsTlJa0s7rXz7K1j5BOkZ2aG5Zka5uhNYzpdl3cCaxfwa6BEzPG7zQbbJqL7RwXhoh9N2382eEMFXsJ2ehxL97YACjQxr6tB1OQryFaQLtrhi8zYQOqwdB04t89\/1O\/w1cDnyilFU=","channel_secret":"e6cf7c0bd09afdf4a60174c665653230"}',1541230024,1541230024);
";
$link->query($sql);

$sql = "
CREATE TABLE `line_bot_chat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `source_type` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `source_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `joined_at` int(10) unsigned NOT NULL,
  `left_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`),
  KEY `source` (`source_type`,`source_id`),
  KEY `joined_at` (`joined_at`),
  KEY `left_at` (`left_at`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
";
$link->query($sql);

$sql = "
INSERT INTO `gav` VALUES (2,4,'user','U8d06f9b05c23c2e1279dce883a3d3dc5',1541261538,0,1541261538,1541261538);
";
$link->query($sql);
