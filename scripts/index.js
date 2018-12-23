var GTE = GTE || {};
GTE.common.initTableGrid($(".table-grid"));
GTE.common.setMapSize($(".map"));
var currencySymbol = GTE.currencySymbol || '$';

/* FIXME: localStorage 在 buddyhouse 的老 ipad 瀏覽器上會有一開店就載入前一周資料的 bug */
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
var $summary_page = $('#summary');
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
        if ('undefined' === typeof all_table_datas[table]) {
            delete all_table_datas[table];
            continue;
        }
        var item_datas = all_table_datas[table].item_datas || {};
        if (item_datas.length) {
            $('#table-button-' + $.escapeSelector(table)).addClass('taken');
        }
    }
    display_page('table');
};

var display_submit_page = function(table){
    $submit_page.data('table', table);
    $submit_page.find('.navbar-title').text(table);
    $submit_page.find('.payment-method-button:eq(0)').click();
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

var display_summary_page = function(){
    if (Object.keys(all_table_datas).length > 0) {
        if (!confirm('仍有未結帳單，是否繼續關帳？')) {
            return;
        }
    }
    $.get('ajax_summary.php', function(rtn){
        $summary_page.html(rtn).trigger('create');
        display_page('summary');
        $(':input[name=adjustment_type]:eq(0)').click();
        $('[data-toggle="tooltip"]').on('click', function(e) {
            e.preventDefault();
        }).tooltip();
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
    var cid = $li.find('a').data('category_id');
    $pos_page.find('.list-div').hide();
    $('#category-' + cid).show();
};

/* -------- table 頁設定 -------- */
$table_page.delegate('.table-grid', 'click', function(e){
    e.preventDefault();
    current_table = $(this).attr('id').replace('table-button-', '');
    display_pos_page(current_table);
});

$table_page.delegate('#navbar-menu li', 'click', function(e){
    $('#navbar-toggle-button').click();
});

$table_page.delegate('.to-bill-page', 'click', function(e){
    e.preventDefault();
    display_bill_page();
});

$table_page.delegate('.to-summary-page', 'click', function(e){
    e.preventDefault();
    display_summary_page();
});


/* -------- pos 頁設定 -------- */
/* 回 table 頁 */
$pos_page.find('.back-to-table').on('click', function(e){
    e.preventDefault();
    var current_table = $pos_page.data('table');
    saveItemDatas(current_table);
    display_table_page();
});

$('#select-change-table').find('li').on('click', function(e){
    e.preventDefault();
    var new_table = $(this).data('value');
    if ('undefined' !== typeof all_table_datas[new_table]) {
        alert('此桌目前有客人！');
        return;
    }
    var table = $pos_page.data('table');
    saveItemDatas(table);
    var tmp_data = all_table_datas[table];
    delete all_table_datas[table];
    all_table_datas[new_table] = tmp_data;
    display_table_page();
});

/* 切換分類 */
$('#nav-category').find('a').click(function(e){
    e.preventDefault();
    var $this = $(this);
    setCategoryActive($this.closest('li'));
});

/* 計算總價 */
var addTotalPrice = function(price){
    var subtotal = parseFloat($subtotal.first().text()) + parseFloat(price);
    $subtotal.text(subtotal);
};

/* 加數量 */
var amountPlus = function($item_tr){
    var $amount = $item_tr.find('.selected-item-amount');
    $amount.text(function(){
	return parseInt($(this).text()) + 1;
    });
    var price = parseFloat($item_tr.data('price'));
    addTotalPrice(price);
};

/* 減數量 */
var amountMinus = function($item_tr){
    var $amount = $item_tr.find('.selected-item-amount');
    $amount.text(function(){
	return parseInt($(this).text()) - 1;
    });
    var price = parseFloat($item_tr.data('price'));
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
    var price = parseFloat($this.find('.price').text().substring(1));
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

$submit_page.find('.boxed-select').click(function(e){
    e.preventDefault();
    $(this).siblings('.boxed-select').removeClass('selected');
    $(this).addClass('selected');
});

$submit_page.find('.select-event').on('change', function(e){
    var $this = $(this);
    var id = $this.val();
    if (0 == id) {
        return;
    }
    var url = '/ajax_get_event_form.php?id=' + id;
    $('.form-by-event-id').eq($this.index() - 1).data('id', id).load(url);
});

$('#submit_bill').click(function(e){
    e.preventDefault();
    var $this = $(this);
    var custermers = $('.custermers-button.selected').data('value');
    if ('undefined' == typeof custermers) {
        alert('請選擇人數');
        return;
    }
    // 暫時只實作單一付款方式
    var payment_method = $('.payment-method-button.selected').data('value');
    $this.attr('disabled', 'disabled');
    var table = $submit_page.data('table');
    var event_options = {};
    $('.select-event').each(function(index){
        var $select = $(this);
        var $form = $('.form-by-event-id').eq(index);
        var options = {};
        $form.find(':input').each(function(){
            var key = $(this).attr('name');
            var value = $(this).val();
            options[key] = value;
        });
        event_options[$select.val()] = options;
        $select[0].selectedIndex = 0;
    });
    var data = {
        table: table,
        custermers: custermers,
        payment_method: payment_method,
        event_options: JSON.stringify(event_options),
        item_datas: all_table_datas[table].item_datas,
        ordered_at: all_table_datas[table].ordered_at
    };
    $('.custermers-button.selected').removeClass('selected');
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


/* -------- summary page -------- */
var summaryPage = {
    getSalesValue: function() {
        return parseFloat($('#sales').text().substr(1)) || 0;
    },

    getOpenAmountValue: function() {
        return parseFloat($('#open_amount').val()) || 0;
    },

    getCloseAmountValue: function() {
        return parseFloat($('#close_amount').val()) || 0;
    },

    getPaidInValue: function() {
        return parseFloat($('#paid_in').val()) || 0;
    },

    getPaidOutValue: function() {
        return parseFloat($('#paid_out').val()) || 0;
    },

    getExpectedAmount: function() {
        var sales = summaryPage.getSalesValue();
        var open_amount = summaryPage.getOpenAmountValue();
        var paid_in = summaryPage.getPaidInValue();
        var paid_out = summaryPage.getPaidOutValue();
        return open_amount + sales + paid_in - paid_out;
    },

    getDifferenceValue: function() {
        var close_amount = summaryPage.getCloseAmountValue();
        var expected_amount = summaryPage.getExpectedAmount();
        return close_amount - expected_amount;
    },

    getDifferenceText: function() {
        var difference_value = summaryPage.getDifferenceValue();
        if (difference_value < 0) {
            return '-' + currencySymbol + (difference_value * (-1).toString());
        } else {
            return '+' + currencySymbol + difference_value.toString();
        }
    },

    getAdjustmentAmountValue: function() {
        return parseFloat($(':input[name=adjustment_amount]').val()) || 0;
    },

    getAdjustmentTypeValue: function() {
        return parseInt($(':input[name=adjustment_type]:checked').val());
    },

    getAdjustmentByInText: function() {
        var $adjustment_by = $(':input[name=adjustment_by]');
        if (0 === $adjustment_by.length) {
            return "";
        }
        if ("0" === $adjustment_by.val()) {
            return "";
        }
        return $adjustment_by.find(':selected').text();
    },

    getFloatValue: function() {
        var close_amount = summaryPage.getCloseAmountValue();
        var adjustment_type = summaryPage.getAdjustmentTypeValue();
        var adjustment_amount = summaryPage.getAdjustmentAmountValue();
        if (GTE.common.shift.adjustmentType.takeout === adjustment_type) {
            float = close_amount - adjustment_amount;
        } else if (GTE.common.shift.adjustmentType.add === adjustment_type) {
            float = close_amount + adjustment_amount;
        } else {
            float = close_amount;
        }
        return float;
    },

    checkValidNumberInput: function(input) {
        var parsedValue = parseFloat(input);
        if (isNaN(parsedValue)) {
            return false;
        }
        if (parsedValue < 0) {
            return false;
        }
        return true;
    },

    checkOpenAmount: function() {
        var open_amount = summaryPage.getOpenAmountValue();
        if (0 === open_amount) {
            return false;
        }
        return true;
    },

    checkCloseAmount: function() {
        var close_amount = summaryPage.getCloseAmountValue();
        if (0 === close_amount) {
            return false;
        }
        return true;
    },

    checkAdjustmentBy: function() {
        var $adjustment_by = $(':input[name=adjustment_by]');
        if (0 === $adjustment_by.length) {  // 不須選擇
            return true;
        }
        var $adjustment_type = $(':input[name=adjustment_type]:checked');
        if ("0" == $adjustment_type.val()) {  // 略過取出/補款
            return true;
        }
        if ("0" == $adjustment_by.val()) {  // 未選擇
            return false;
        }
        return true;
    },

    checkAdjustmentAmount: function() {
        var $adjustment_type = $(':input[name=adjustment_type]:checked');
        if ("0" == $adjustment_type.val()) {  // 略過取出/補款
            return true;
        }
        var adjustment_amount = summaryPage.getAdjustmentAmountValue();
        if (0 === adjustment_amount) {
            return false;
        }
        return true;
    },

    checkFloatValue: function() {
        var float_value = summaryPage.getFloatValue();
        if (float_value <= 0) {
            return false;
        }
        return true;
    },

    getConfirmSummaryContent: function() {
        var confirm_content = "==確認關帳資訊==\n";
        confirm_content += ("錢櫃初始金額: " + currencySymbol + summaryPage.getOpenAmountValue().toString() + "\n");
        confirm_content += ("錢櫃實際現金: " + currencySymbol + summaryPage.getCloseAmountValue().toString() + "\n");
        confirm_content += ("臨時支出: " + currencySymbol + summaryPage.getPaidOutValue().toString() + "\n");
        confirm_content += ("臨時收入: " + currencySymbol + summaryPage.getPaidInValue().toString() + "\n");
        confirm_content += ("現金短溢: " + summaryPage.getDifferenceText() + "\n");
        var adjustment_type = summaryPage.getAdjustmentTypeValue();
        var adjustment_amount = summaryPage.getAdjustmentAmountValue();
        var adjustment_by = summaryPage.getAdjustmentByInText();
        if (GTE.common.shift.adjustmentType.takeout === adjustment_type) {
            confirm_content += ("營收取出: " + currencySymbol + adjustment_amount + "\n");
            if (adjustment_by.length > 0) {
                confirm_content += ("取出人: " + adjustment_by + "\n");
            }
        } else if (GTE.common.shift.adjustmentType.add === adjustment_type) {
            confirm_content += ("錢櫃補款: " + currencySymbol + adjustment_amount + "\n");
            if (adjustment_by.length > 0) {
                confirm_content += ("補款人: " + adjustment_by + "\n");
            }
        }
        confirm_content += ("錢櫃留存現金: " + currencySymbol + summaryPage.getFloatValue().toString() + "\n");
        return confirm_content;
    }
}

$summary_page.delegate('.back-to-table', 'click', function(e) {
    e.preventDefault();
    display_table_page();
});

$summary_page.delegate('.check-group', 'change', function(e) {
    e.preventDefault();
    var $this = $(this);
    if (!summaryPage.checkValidNumberInput($this.val())) {
        alert('此欄位須輸入大於或等於 0 的數字');
        $this.val("");
    }
    var expected_amount = summaryPage.getExpectedAmount();
    var difference_value = summaryPage.getDifferenceValue();
    var difference_text = summaryPage.getDifferenceText();
    if (difference_value < 0) {
        color = 'red';
    } else {
        color = 'green';
    }
    $('#expected_amount').text(currencySymbol + expected_amount.toString());
    $('#difference').text(difference_text).css('color', color);
});

$summary_page.delegate('.adjustment-group', 'change', function(e) {
    e.preventDefault();
    var $this = $(this);
    if (!summaryPage.checkValidNumberInput($this.val())) {
        alert('此欄位須輸入大於或等於 0 的數字');
        $this.val("");
    }
    var float = summaryPage.getFloatValue();
    if (float < 0) {
        alert('營收取出金額不可超過錢櫃實際現金');
        $(':input[name=adjustment_amount]').val("");
        $('#float').text("");
    } else {
        $('#float').text(currencySymbol + float.toString());
    }
});

$summary_page.delegate(':input[name=adjustment_type]', 'change', function(e) {
    e.preventDefault();
    var adjustment_type = summaryPage.getAdjustmentTypeValue();
    var disabled;
    if (GTE.common.shift.adjustmentType.pass === adjustment_type) {
        disabled = true;
    } else {
        disabled = false;
    }
    $(':input[name=adjustment_amount]').prop('disabled', disabled);
    $(':input[name=adjustment_by]').prop('disabled', disabled);
});

$summary_page.delegate('#submit_summary', 'click', function(e) {
    e.preventDefault();
    if (!summaryPage.checkOpenAmount()) {
        alert("請選擇輸入錢櫃初始金額");
        return;
    }
    if (!summaryPage.checkCloseAmount()) {
        alert("請選擇輸入錢櫃實際現金");
        return;
    }
    if (!summaryPage.checkAdjustmentBy()) {
        alert("請選擇取出/補款人");
        return;
    }
    if (!summaryPage.checkAdjustmentBy()) {
        alert("請選擇取出/補款人");
        return;
    }
    if (!summaryPage.checkFloatValue()) {
        if (!confirm('錢櫃留存現金為 0 元，表示你把錢櫃裡的錢全部拿走了，明天開店的時候錢櫃裡一毛錢都沒有，確定嗎？')) {
            return;
        }
    }
    var $this = $(this);
    var confirm_content = summaryPage.getConfirmSummaryContent();
    if (!confirm(confirm_content)) {
        return;
    }
    $this.prop('disabled', true);
    var data = $this.closest('form').serializeArray();
    $.post('ajax_create_summary.php', data, function(rtn){
        if (rtn.message) {
            alert(rtn.message);
        }
        if (rtn.error) {
            return;
        }
        $this.prop('disabled', false);
        display_table_page();
    });
});
