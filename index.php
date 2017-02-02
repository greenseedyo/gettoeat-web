<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}
$tables_file = "{$_SERVER['DOCUMENT_ROOT']}/tables/{$store->account}.html";

$categories = $store->categories;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
<title><?= htmlspecialchars($store->nickname) ?> 結帳小幫手</title>
<link rel="stylesheet" href="static/jquery.mobile-1.3.1.min.css" />
<link rel="stylesheet" href="static/style.css" />
<script src="static/jquery-1.9.1.min.js"></script>
<script>
$(document).on('mobileinit', function(){ 
    $.mobile.ajaxEnabled = false;
});
</script>
<script src="static/jquery.mobile-1.3.1.min.js"></script>
<script type="text/javascript" src="static/jquery.tmpl.min.js"></script>
</head>
<body>

<!-- table -->
<div class="page" id="table">
    <div data-theme="a" data-role="header">
        <h5><?= htmlspecialchars($store->nickname) ?> 結帳小幫手</h5>
        <button class="ui-btn-right to-bill-page">已結帳單</button>
    </div>
    <?php require_once($tables_file); ?>
</div>

<!-- pos -->
<div class="page" id="pos" style="display:none;">
    <div data-theme="a" data-role="header">
        <input class="ui-btn-left back-to-table" type="button" value="返回" />
        <h5><?= htmlspecialchars($store->nickname) ?> 結帳小幫手</h5>
        <div id="navbar" data-role="navbar" data-iconpos="left">
            <ul>
                <?php foreach ($categories as $category) { ?>
                <li>
                <a href="#page<?= intval($category->id) ?>" data-transition="fade" data-theme="" data-icon="star" <?= 1 == $category->id ? 'class="ui-btn-active ui-state-persist"' : '' ?> data-category_id="<?= intval($category->id) ?>"><?= htmlspecialchars($category->name) ?></a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div id="left-column" data-role="content">
        <table width="100%" cellpadding="10" id="all-items">
            <tr class="thead" style="text-align:center">
                <td width="50%">項目</td>
                <td colspan="3">數量</td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>

    <div id="right-column" data-role="content">
        <?php foreach ($categories as $category) { ?>
        <div class="list-div" id="category-<?= intval($category->id) ?>" <?= 1 == $category->id ? '' : 'style="display:none;"' ?>>
            <?php foreach ($category->getCurrentProducts()->order('position ASC') as $product) { ?>
            <span class="grid">
                <button data-role="none" data-product_id="<?= intval($product->id) ?>">
                    <span class="name"><?= htmlspecialchars($product->name) ?></span><br>
                    <span class="price">$<?= intval($product->price) ?></span><br>
                </button>
            </span>
            <?php } ?>
        </div>
        <?php } ?>
        <div class="clear"></div>
    </div>

    <div data-theme="a" data-role="footer" data-position="fixed" data-tap-toggle="false" style="">
        <div class="ui-title">
            小計 $ <span id="subtotal"></span>
        </div>
        <div style="position:fixed;bottom:2px;">
            <input id="send" type="button" data-inline="true" data-theme="a" value="送單" data-mini="true">
        </div>
        <div class="ui-screen-hidden" id="select-wrapper">
            <select name="select-change-table" data-native-menu="false">
                <option value="outer_1">戶外1</option>
                <option value="outer_2">戶外2</option>
                <option value="outer_3">戶外3</option>
                <option value="sofa_1">沙1</option>
                <option value="sofa_2">沙2</option>
                <option value="left_1">左1</option>
                <option value="left_2">左2</option>
                <option value="left_3">左3</option>
                <option value="left_4">左4</option>
                <option value="middle_1">中1</option>
                <option value="middle_2">中2</option>
                <option value="middle_3">中3</option>
                <option value="right_1">右1</option>
                <option value="right_2">右2</option>
                <option value="bar_1">吧1</option>
                <option value="bar_2">吧2</option>
                <option value="bar_3">吧3</option>
                <option value="bar_4">吧4</option>
                <option value="bar_5">吧5</option>
                <option value="takeout_1">外帶1</option>
                <option value="takeout_2">外帶2</option>
                <option value="takeout_3">外帶3</option>
                <option value="takeout_4">外帶4</option>
                <option value="takeout_5">外帶5</option>
            </select>
        </div>
        <div style="position:fixed;bottom:2px;right:0px;">
            <input id="change-table" type="button" data-inline="true" data-theme="a" value="換桌" data-mini="true">
            <input id="to-submit-page" type="button" data-inline="true" data-theme="a" value="結帳" data-mini="true">
        </div>
    </div>
</div>

<script id="tmpl-item-tr" type="text/x-jquery-tmpl">
<tr class="item" data-price="${price}" id="item-${product_id}" data-product_id="${product_id}">
    <td class="item-name">
        <span>${name}</span>
    </td>
    <td width="30">
        <button class="minus" data-role="none" data-inline="true">－</button>
    </td>
    <td style="text-align:center;">
        <span class="selected-item-amount">${amount}</span>
    </td>
    <td width="30">
        <button class="plus" data-role="none" data-inline="true">＋</button>
    </td>
</tr>
</script>

<!-- submit -->
<div class="page" id="submit" style="display:none;">
    <div data-theme="a" data-role="header">
        <button class="back-to-pos">返回</button>
        <h5><?= htmlspecialchars($store->nickname) ?> 結帳小幫手</h5>
    </div>
    <div data-role="content">
        <div class="submit_content">
            <form method="post">
                <h1>人數</h1>
                <?php for ($i = 1; $i <= 20; $i ++) { ?>
                <span class="grid-small select-custermers"><button class="custermers-button" data-role="none"><span><?= $i ?></span></button></span>
                <?php } ?>
                <br>
                <h1>折扣選擇</h1>
                <select name="event_id" data-native-menu="false">
                    <option value="0" selected>無</option>
                    <option value="3">9折</option>
                    <option value="4">5折</option>
                    <option value="0" disabled>外帶折扣自動計算不需選擇</option>
                </select>
                <br><br>
                <input type="button" id="submit_bill" value="確認結帳" />
            </form>
        </div>
    </div>
</div>

<!-- bill -->
<div class="page" id="bill" style="display:none;">
</div>

<script>
var LS = localStorage;
LS.all_table_datas = LS.all_table_datas || '{}';
var all_table_datas = $.parseJSON(LS.all_table_datas);
if ('object' !== typeof all_table_datas || !all_table_datas) {
    all_table_datas = {};
}
var $table_page = $('#table');
var $pos_page = $('#pos');
var $submit_page = $('#submit');
var $bill_page = $('#bill');
var $tmpl_item_tr = $('#tmpl-item-tr');
var $all_items = $('#all-items');
var $subtotal = $('#subtotal');

var display_page = function(page_id){
    $('.page').hide();
    eval('$' + page_id + '_page').show();
};

var display_pos_page = function(table){
    $pos_page.data('table', table);
    $pos_page.find('tr.item').remove();
    all_table_datas[table] = all_table_datas[table] || {};
    var item_datas = all_table_datas[table].item_datas || {};
    var subtotal = 0;
    if (item_datas.length) {
        for (var i in item_datas) {
            subtotal += item_datas[i].amount * item_datas[i].price;
            $tmpl_item_tr.tmpl(item_datas[i]).appendTo($all_items);
        }
        $subtotal.text(subtotal);
    } else {
        all_table_datas[table] = {};
        all_table_datas[table].ordered_at = new Date().getTime();
    }
    $subtotal.text(subtotal);
    display_page('pos');
};

var display_table_page = function(){
    $table_page.find('.select-table').removeClass('taken');
    for (var table in all_table_datas) {
        var item_datas = all_table_datas[table].item_datas || {};
        if (item_datas.length) {
            $('#table-button-' + table).addClass('taken');
        }
    }
    display_page('table');
};

var display_submit_page = function(table){
    $submit_page.data('table', table);
    display_page('submit');
};

var display_bill_page = function(bill_id){
    bill_id = bill_id || '';
    $.get('ajax_bill.php', {id: bill_id}, function(rtn){
        if ('' == rtn) {
            return;
        }
        $bill_page.html(rtn).trigger('create');
        display_page('bill');
        $.get('ajax_get_today_total.php', function(rtn){
            $('#today_total').text(rtn);
        });
    });
};

var saveItemDatas = function(table){
    var item_datas = [];
    $all_items.find('tr.item').each(function(){
        var $this = $(this);
        var data = {
            product_id: $this.data('product_id'),
            name: $this.find('.item-name').text().trim(),
            price: $this.data('price'),
            amount: parseInt($this.find('.selected-item-amount').text())
        };
        item_datas.push(data);
    });
    if (item_datas.length) {
        all_table_datas[table] = all_table_datas[table] || {};
        all_table_datas[table].item_datas = item_datas;
    } else {
        delete all_table_datas[table];
    }
};

/* -------- talbe 頁設定 -------- */
$table_page.delegate('.select-table', 'click', function(e){
    e.preventDefault();
    current_table = $(this).attr('id').replace('table-button-', '');
    display_pos_page(current_table);
});

$table_page.delegate('.to-bill-page', 'click', function(e){
    e.preventDefault();
    display_bill_page();
});


/* -------- pos 頁設定 -------- */
/* 回 table 頁 */
$pos_page.find('.back-to-table').on('click', function(e){
    e.preventDefault();
    var current_table = $pos_page.data('table');
    saveItemDatas(current_table);
    display_table_page();
});

$('select[name=select-change-table]').change(function(){
    $('#select-wrapper').hide();
    var new_table = $(this).val();
    if ('undefined' !== typeof all_table_datas[new_table]) {
        alert('此桌目前有客人！');
        return;
    }
    var table = $pos_page.data('table');
    var tmp_data = all_table_datas[table];
    delete all_table_datas[table];
    all_table_datas[new_table] = tmp_data;
    display_pos_page(new_table);
});

$('#change-table').click(function(e){
    $('select[name=select-change-table]').prev('a').click();
    e.preventDefault();
});

/* 切換分類 */
$pos_page.find('#navbar').find('a').click(function(e){
    e.preventDefault();
    var cid = $(this).data('category_id');
    $pos_page.find('.list-div').hide();
    $('#category-' + cid).show();
});

/* 計算總價 */
var addTotalPrice = function(price){
    var subtotal = parseInt($subtotal.text());
    $subtotal.text(subtotal + parseInt(price));
};

/* 加數量 */
var amountPlus = function($item_tr){
    var $amount = $item_tr.find('.selected-item-amount');
    $amount.text(function(){
	return parseInt($(this).text()) + 1;
    });
    var price = parseInt($item_tr.data('price'));
    addTotalPrice(price);
};

/* 減數量 */
var amountMinus = function($item_tr){
    var $amount = $item_tr.find('.selected-item-amount');
    $amount.text(function(){
	return parseInt($(this).text()) - 1;
    });
    var price = parseInt($item_tr.data('price'));
    addTotalPrice(-price);
    if (0 === parseInt($amount.text())) {
	$item_tr.remove();
	return;
    }
};

/* 點菜 */
$pos_page.delegate('.list-div button', 'click', function(e){
    var t1 = new Date();
    e.preventDefault();
    var $this = $(this);
    $this.css('background-color', '#FEFF91');
    setTimeout(function(){
        $this.css('background-color', '#f9f9f9');
    }, 100);
    var price = parseInt($this.find('.price').text().substring(1));
    var product_id = $this.data('product_id');
    var $item_tr = $('#item-' + product_id);
    var name = $this.find('.name').text();
    if (0 === $item_tr.size()) {
        $item_tr = $tmpl_item_tr.tmpl({product_id: product_id, name: name, price: price, amount: 0}).appendTo($all_items);
        $all_items.trigger("create");
    }
    amountPlus($item_tr);
    var t2 = new Date();
    //alert(t2.getTime() - t1.getTime());
});

/* 確認出餐 */
$all_items.on('click', '.item-name', function(e){
    e.preventDefault();
    var $this = $(this);
    if (!$this.data('status')) {
        $this.data('status', 'done').css('text-decoration', 'line-through').css('background-color', '#DDDDDD');
    } else if ('done' == $this.data('status')) {
        $this.data('status', '').css('text-decoration', '').css('background-color', '');
    }
});

$all_items.on('click', '.plus', function(e){
    e.preventDefault();
    amountPlus($(this).closest('tr'));
});

$all_items.on('click', '.minus', function(e){
    e.preventDefault();
    amountMinus($(this).closest('tr'));
});

$('#send').click(function(e){
    e.preventDefault();
    // 待寫
});


/* -------- submit 頁設定 -------- */
$('#to-submit-page').click(function(e){
    e.preventDefault();
    var current_table = $pos_page.data('table');
    saveItemDatas(current_table);
    display_submit_page(current_table);
});

$submit_page.find('.back-to-pos').click(function(e){
    e.preventDefault();
    var current_table = $submit_page.data('table');
    display_pos_page(current_table);
});

$submit_page.find('.select-custermers').click(function(e){
    e.preventDefault();
    var $this = $(this);
    $('.custermers-button').removeClass('selected');
    $this.find('button').addClass('selected');
});

$('#submit_bill').click(function(e){
    e.preventDefault();
    var $this = $(this);
    $this.attr('disabled', 'disabled');
    var custermers = $('.custermers-button.selected').text();
    if ('' == custermers) {
        alert('請選擇人數');
        return;
    }
    var table = $submit_page.data('table');
    var data = {
        table: table,
        custermers: custermers,
        event_id: $('select[name=event_id]').val(),
        item_datas: all_table_datas[table].item_datas,
        ordered_at: all_table_datas[table].ordered_at
    };
    $('.custermers-button.selected').removeClass('selected');
    $('select[name=event_id]')[0].selectedIndex = 0;
    $('select[name=event_id]').selectmenu('refresh', true);
    $.post('ajax_submit.php', data, function(rtn){
        var bill_id = rtn;
        delete all_table_datas[table];
        display_bill_page(bill_id);
        $this.removeAttr('disabled');
    });
});


/* -------- bill page -------- */
$bill_page.delegate('.back-to-table', 'click', function(e){
    e.preventDefault();
    display_table_page();
});

$bill_page.delegate('#prev_bill', 'click', function(e){
    e.preventDefault();
    display_bill_page($(this).data('prev-id'));
});

$bill_page.delegate('#next_bill', 'click', function(e){
    e.preventDefault();
    display_bill_page($(this).data('next-id'));
});

$bill_page.delegate('#delete_bill', 'click', function(e){
    e.preventDefault();
    if (!confirm('確定刪除？')) {
        return;
    }
    var data = {id: $(this).data('id')};
    $.post('ajax_delete_bill.php', data, function(rtn){
        display_table_page();
    });
});

$(function(){
    display_table_page();
});

$(window).unload(function(){
    LS.all_table_datas = JSON.stringify(all_table_datas);
});
</script>
<script>
$(function(){
    $('.do-not-remove-me').next('div').remove();
});
</script>
<div class="do-not-remove-me"></div>
</body>
</html>
