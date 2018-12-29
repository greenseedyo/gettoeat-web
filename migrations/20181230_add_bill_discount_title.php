<?php
require_once(__DIR__ . '/include_migration.php');

function alterTable($link)
{
    $sql = "
ALTER TABLE `bill_discount` ADD `title` varchar(255) NOT NULL DEFAULT ''
    ";
    $link->query($sql);
}
alterTable();

function addPastTitles()
{
    foreach (BillDiscount::search(1) as $bill_discount) {
        if ('' != $bill_discount->title) {
            continue;
        }
        $title = $bill_discount->event->title;
        $bill_discount->update(array('title' => $title));
    }
}
addPastTitles();
