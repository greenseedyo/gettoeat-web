<?php
require_once 'config.php';

if ($_GET['id']) {
    $bill = Bill::find(intval($_GET['id']));
} else {
    $bill = Bill::getTodayPaidBills()->order('paid_at DESC')->first();
}

if (!$bill) {
    exit;
}

$paid_at = intval($bill->paid_at);
if ($prev_bill = Bill::getTodayPaidBills()->search("paid_at < {$paid_at}")->order('paid_at DESC')->first()) {
    $prev_bill_id = $prev_bill->id;
}
if ($next_bill = Bill::getTodayPaidBills()->search("paid_at > {$paid_at}")->order('paid_at ASC')->first()) {
    $next_bill_id = $next_bill->id;
}

?>
<div data-theme="a" data-role="header" class="ui-header ui-bar-a" role="banner">
    <button data-role="button" class="ui-btn-left back-to-table">返回</button>
    <h5 class="ui-title" role="heading" aria-level="1">Buddy House 結帳小幫手</h5>
    <button class="ui-btn-right" id="delete_bill" data-id="<?= intval($bill->id) ?>">刪除</button>
</div>

<div data-role="content">
    <ul data-role="listview" data-divider-theme="b" data-inset="false">
        <li data-theme="d" class="info"><?= sprintf('桌號: %s (點餐時間: %s, 結帳時間: %s) 人數: %s', $bill->getTableName(), date('H:i:s', $bill->ordered_at), date('H:i:s', $bill->paid_at), $bill->custermers) ?></li>
        <?php foreach ($bill->items as $item) { ?>
        <li><?= sprintf('<span>%s x %d</span> <span class="item-price">%d</span>', $item->product->name, $item->amount, $item->product->price * $item->amount) ?></li>
        <?php } ?>
        <?php foreach ($bill->discounts as $discount) { ?>
        <li class="discount"><?= sprintf('<span>%s</span> <span class="item-price">-%d</span>', $discount->event->title, $discount->value) ?></li>
        <?php } ?>
        <li data-theme="e" class="total"><span>總計</span> <span class="item-price"><?= intval($bill->price) ?></span></li>
    </ul>
    <div class="clear"></div>
</div>

<div data-theme="a" data-role="footer" data-position="fixed" data-tap-toggle="false" class="ui-footer ui-bar-a ui-footer-fixed slideup" role="contentinfo">
    <div class="ui-title">
        <div class="ui-title">
            本日業績 $ <span id="today_total"></span>
        </div>
        <?php if ($prev_bill_id) { ?>
        <div style="position:fixed;bottom:2px;">
            <button id="prev_bill" data-inline="true" data-theme="a" data-mini="true" data-prev-id="<?= intval($prev_bill_id) ?>">上一張</button>
        </div>
        <?php } ?>
        <?php if ($next_bill_id) { ?>
        <div style="position:fixed;bottom:2px;right:0px;">
            <button id="next_bill" data-inline="true" data-theme="a" data-mini="true" data-next-id="<?= intval($next_bill_id) ?>">下一張</button>
        </div>
        <?php } ?>
    </div>
</div>
