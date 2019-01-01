var GTE = GTE || {};
GTE.common.initTableGrid($(".table-grid"));
GTE.common.setMapSize($(".map"));
var currencySymbol = GTE.currencySymbol || '$';

var LS = localStorage;
LS.all_table_datas = LS.all_table_datas || '{}';
var all_table_datas;
try {
    all_table_datas = $.parseJSON(LS.all_table_datas);
} catch (err) {
    all_table_datas = {};
}
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
var brandTitle = $table_page.find('.navbar-brand').text();

var formatNavbarTitle = function(table) {
    return brandTitle + " - " + table;
};

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
    $pos_page.find('.navbar-title').text(formatNavbarTitle(table));
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
        var $table_button = $('#table-button-' + $.escapeSelector(table));
        // 有點餐會變黃色
        if (item_datas.length) {
            $table_button.addClass('taken');
        }
        // 餐點都出了會變綠色
        var all_done = isAllDone(table);
        if (all_done) {
            $table_button.addClass('all-done');
        } else {
            $table_button.removeClass('all-done');
        }
    }
    display_page('table');
};

var isAllDone = function(table) {
    var item_datas = all_table_datas[table].item_datas || {};
    for (i in item_datas) {
        if ('undone' == item_datas[i].status) {
            return false;
        }
    }
    return true;
};

var display_submit_page = function(table){
    $submit_page.data('table', table);
    $submit_page.find('.navbar-title').text(formatNavbarTitle(table));
    $submit_page.find('.payment-method-button:eq(0)').click();
    display_page('submit');
    adjust_event_checkbox_position();
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
            amount: parseInt($this.find('.selected-item-amount').text()),
            status: $this.find('.item-name').data('status')
        };
        item_datas.push(data);
    });
    if (item_datas.length) {
        all_table_datas[table] = all_table_datas[table] || {};
        all_table_datas[table].item_datas = item_datas;
    } else {
        delete all_table_datas[table];
    }
    saveTablesToLocalStorage();
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

$table_page.delegate('.resize-text-increase', 'click', function(e) {
    resizeText(1, true);
});

$table_page.delegate('.resize-text-decrease', 'click', function(e) {
    resizeText(-1, true);
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
    saveTablesToLocalStorage();
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
        $item_tr = $tmpl_item_tr.tmpl({product_id: product_id, name: name, price: price, amount: 0, status: 'undone'}).appendTo($all_items);
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
    if ('done' == $this.data('status')) {
        $this.data('status', 'undone').addClass('undone').removeClass('done');
    } else {
        $this.data('status', 'done').addClass('done').removeClass('undone');
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
    resetSubmitPage();
    refreshCartSubtotal(current_table);
    display_pos_page(current_table);
});

$submit_page.delegate('.back-to-table', 'click', function(e) {
    e.preventDefault();
    resetSubmitPage();
    display_table_page();
});

$submit_page.find('.boxed-select').click(function(e){
    e.preventDefault();
    var group = $(this).data('group');
    $('.boxed-select').filter('[data-group=' + group + ']').removeClass('selected');
    $(this).addClass('selected');
});

$submit_page.find('.toggle-event').on('change', function(e){
    var $this = $(this);
    var $checkbox = $this.find('input[type=checkbox]');
    var $form = $this.siblings('.form-by-event-id');
    var table = $submit_page.data('table');
    if (!$checkbox.is(':checked')) {
        $form.text('');
        refreshCartSubtotal(table);
    } else {
        var id = $checkbox.val();
        var url = '/ajax_get_event_form.php?id=' + id;
        $form.data('id', id).load(url, function() {
            refreshCartSubtotal(table);
            $(this).find(':input').on('change', function() {
                refreshCartSubtotal(table);
            });
        });
    }
});

var formatCartData = function(table) {
    all_table_datas[table] = all_table_datas[table] || {};
    var $submit_bill = $('#submit_bill');
    var custermers = $submit_page.find('.custermers-button.selected').data('value');
    // 暫時只實作單一付款方式
    var payment_method = $submit_page.find('.payment-method-button.selected').data('value');
    var event_options = {};
    $submit_page.find('.toggle-event').each(function(){
        var $item = $(this);
        var $checkbox = $item.find('input[type=checkbox]');
        if (!$checkbox.is(':checked')) {
            return;
        }
        var $form = $item.siblings('.form-by-event-id');
        var options = {};
        $form.find(':input').each(function(){
            var key = $(this).attr('name');
            var value = $(this).val();
            options[key] = value;
        });
        event_options[$checkbox.val()] = options;
    });
    var data = {
        table: table,
        custermers: custermers,
        payment_method: payment_method,
        event_options: JSON.stringify(event_options),
        item_datas: all_table_datas[table].item_datas,
        ordered_at: all_table_datas[table].ordered_at
    };
    return data;
};

var resetSubmitPage = function() {
    $submit_page.find('.custermers-button.selected').removeClass('selected');
    $submit_page.find('.toggle-event label :input[type=checkbox]').prop('checked', false);
    $submit_page.find('form.form-by-event-id').text('');
}

var refreshCartSubtotal = function(table) {
    var url = '/ajax_get_cart_subtotal.php';
    var data = formatCartData(table);
    $.ajax({
        url: url,
        type: 'post',
        data: data,
        beforeSend: function() {
            var height = $submit_page.find('.subtotal').height();
            $img = $('<img src="static/images/ajax-loader.gif">').height(height);
            $subtotal.text('').append($img);
        },
        success: function(rtn) {
            $subtotal.text(rtn);
        }
    });
};

$('#submit_bill').click(function(e) {
    e.preventDefault();
    var custermers = $submit_page.find('.custermers-button.selected').data('value');
    if ('undefined' == typeof custermers) {
        alert('請選擇人數');
        return;
    }
    var $submit_bill = $(this);
    var table = $submit_page.data('table');
    var data = formatCartData(table);
    $.ajax({
        url: 'ajax_submit.php',
        type: 'post',
        data: data,
        beforeSend: function() {
            resetSubmitPage();
            $submit_bill.attr('disabled', 'disabled');
        },
        success: function(rtn) {
            var bill_id = rtn;
            delete all_table_datas[table];
            saveTablesToLocalStorage();
            display_bill_page(bill_id);
        },
        complete: function() {
            $submit_bill.removeAttr('disabled');
        }
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

var adjust_event_checkbox_position = function() {
    var max_width = 0;
    var $labels = $('.toggle-event label');
    $labels.each(function() {
        var width = $(this).width();
        if (width > max_width) {
            max_width = width;
        }
    });
    $labels.width(max_width).css('text-align', 'left');
    $('.toggle-event').siblings('.form-by-event-id').width(max_width);
};

var saveTablesToLocalStorage = function() {
    LS.all_table_datas = JSON.stringify(all_table_datas);
};

var resizeText = function(delta, save) {
    var amount = (delta > 0 ? 1 : -1);
    for (var i = 0; i < Math.abs(delta); i ++) {
        $('.resizable').find('*').css('font-size', function() {
            return parseInt($(this).css('font-size')) + amount + 'px';
        });
    }
    if (true === save) {
        updateFontSizeToLocalStorage(delta);
    }
}

var updateFontSizeToLocalStorage = function(delta) {
    var ori = LS.fontSizeDelta || 0;
    var newValue = parseInt(ori) + parseInt(delta);
    LS.fontSizeDelta = JSON.stringify(newValue);
};

var initFontSize = function() {
    var delta = parseInt(LS.fontSizeDelta);
    resizeText(delta, false);
}

$(function(){
    initFontSize();
    display_table_page();
});


/* -------- summary page -------- */
var summaryPage = {
    getCashSalesValue: function() {
        return parseFloat($('#cash_sales').text().substr(1)) || 0;
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
        var cash_sales = summaryPage.getCashSalesValue();
        var open_amount = summaryPage.getOpenAmountValue();
        var paid_in = summaryPage.getPaidInValue();
        var paid_out = summaryPage.getPaidOutValue();
        return open_amount + cash_sales + paid_in - paid_out;
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
        confirm_content += ("應關帳金額: " + currencySymbol + summaryPage.getCashSalesValue().toString() + "\n");
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
