<?php
require_once 'config.php';

if ($bill = $store->getBillById($_POST['id'])) {
    foreach ($bill->items as $item) {
        $item->delete();
    }
    foreach ($bill->discounts as $discount) {
        $discount->delete();
    }
    $bill->delete();
}
