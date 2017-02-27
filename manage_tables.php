<?php
require_once 'config.php';

$tables_info = $store->getTablesInfo();
$helper = $tables_info->getHelper();
$total_height = $helper->getTotalHeight();
$total_width = $helper->getTotalWidth();
$tables = $helper->getTables();
$active_tables = array();
$inactive_tables = array();
$z = count($tables);
$new_grid_x = 20;
$new_grid_y = 10;
$new_grid_z = $z;
$new_grid_width = 80;
$new_grid_height = 40;
foreach ($tables as $table) {
    if ($table->active) {
        $active_tables[] = $table;
    } else {
        $table->x = $new_grid_x * (++ $x);
        $table->y = $new_grid_y;
        $table->z = (-- $z);
        $inactive_tables[] = $table;
    }
}

if ($_POST) {
    try {
        if ('save_table' == $_POST['form_name']) {
            $helper->save($_POST['tables_info']);
        }
        $result = array("error" => false);
    } catch (Exception $e) {
        $result = array("error" => true, "message" => $e->getMessage());
    }
    echo json_encode($result);
    exit;
}

include(VIEWS_DIR . '/manage_tables.html');
