<?php
header('Content-type: text/json');
require_once 'config.php';

$cashier = $store->getCashier();

$item_datas = $_POST['item_datas'];
foreach ($item_datas as $item_data) {
    $cart_item = new Store\Cashier\CartItem($item_data['product_id'], $item_data['amount']);
    $cashier->addItem($cart_item);
}
$event_options = json_decode($_POST['event_options'], 1);
foreach ($event_options as $event_id => $options) {
    if ($event = Event::find(intval($event_id))) {
        $cashier->addEvent($event, $options);
    }
}

// 暫時只實作單一付款方式
$cashier->addPayment($_POST['payment_method']);
$cashier->setReceiptItems();
$total_price = $cashier->getTotalPrice();

echo $total_price;
