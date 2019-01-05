<?php
header('Content-type: text/json');
require_once 'config.php';

$ordered_at = substr($_POST['ordered_at'], 0, 10);
if (date('H', $ordered_at) > $store->getDateChangeAt()) {
    $date = strtotime('today', $ordered_at);
} else {
    $date = strtotime('yesterday', $ordered_at);
}
$cashier = $store->getCashier();
$cashier
    ->setDate($date)
    ->setTable($_POST['table'])
    ->setOrderedAt($ordered_at)
    ->setCustermers($_POST['custermers']);

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

//$preview = $cashier->getPreviewData(); echo $preview;exit;

$link = Pix_Table::getDefaultDb();
$link->query("SET autocommit=0");
$link->query("START TRANSACTION");
try {
    $bill = $cashier->createBill();
    $link->query("COMMIT");
} catch (Exception $e) {
    $link->query("ROLLBACK");
    echo $e->getMessage();
    exit;
}
$link->query("SET autocommit=1");

echo $bill->id;
