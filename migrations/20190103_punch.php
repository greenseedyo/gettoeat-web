<?php
require_once(__DIR__ . '/include_migration.php');

function alterTable($link)
{
    $sql = "
ALTER TABLE `staff` ADD `code` varchar(10) NOT NULL DEFAULT '';
    ";
    $link->query($sql);

    $sql = "
ALTER TABLE `staff` ADD KEY `store_id_code` (store_id, code)
    ";
    $link->query($sql);
}
alterTable($link);

function createTable()
{
    PunchLog::createTable();
}
createTable();
