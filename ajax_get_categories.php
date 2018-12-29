<?php
require_once 'config.php';

if (!$_GET['store_id']) {
    echo json_encode(array('error' => true, 'msg' => 'no store id given'));
}

$categories = $store->categories->toArray();
echo json_encode(array('error' => false, 'categories' => $categories));
