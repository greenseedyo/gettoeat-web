<?php
require_once(__DIR__ . '/include_migration.php');

function alterTable($link)
{
    $sql = "
ALTER TABLE `shift` CHANGE `sales` `cash_sales` int(10) unsigned NOT NULL
    ";
    $link->query($sql);
}
alterTable($link);

