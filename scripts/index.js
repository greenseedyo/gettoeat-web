var GTE = GTE || {};
GTE.common.initTableGrid($(".table-grid"));
GTE.common.setMapSize($(".map"));

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
var $subtotal = $('.subtotal');

var display_page = function(page_id){
    $('.page').hide();
    eval('$' + page_id + '_page').show();
};

var display_pos_page = function(table){
    if (0 === $('#nav-category').find('li.active').length) {
        setCategoryActive($('#nav-category').find('li:first'));
    }
    $pos_page.data('table', table);
    $pos_page.find('tr.item').remove();
    $pos_page.find('.navbar-title').text(table);
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
    $table_page.find('.table-grid').removeClass('taken');
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
    $submit_page.find('.navbar-title').text(table);
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

var setCategoryActive = function($li){
    $li.addClass('active').siblings('li').removeClass('active');
};

/* -------- talbe 頁設定 -------- */
$table_page.delegate('.table-grid', 'click', function(e){
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

$('#select-change-table').find('li').on('click', function(){
    var new_table = $(this).data('value');
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

/* 切換分類 */
$('#nav-category').find('a').click(function(e){
    e.preventDefault();
    var $this = $(this);
    setCategoryActive($this.closest('li'));
    var cid = $this.data('category_id');
    $pos_page.find('.list-div').hide();
    $('#category-' + cid).show();
});

/* 計算總價 */
var addTotalPrice = function(price){
    var subtotal = parseInt($subtotal.first().text()) + parseInt(price);
    $subtotal.text(subtotal);
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
    if (0 === $item_tr.length) {
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

$submit_page.find('.custermers-button').click(function(e){
    e.preventDefault();
    $('.custermers-button').removeClass('selected');
    $(this).addClass('selected');
});

$('#submit_bill').click(function(e){
    e.preventDefault();
    var $this = $(this);
    $this.attr('disabled', 'disabled');
    var custermers = $('.custermers-button.selected').data('value');
    if ('undefined' == typeof custermers) {
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

$(window).on("unload", function(){
    LS.all_table_datas = JSON.stringify(all_table_datas);
});

