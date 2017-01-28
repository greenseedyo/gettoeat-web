<?php
require_once 'config.php';

if ($_POST) {
    if ('products' == $_POST['form_name']) {
        $data = json_decode($_POST['data'], true);
        foreach ($data['on'] as $id => $position) {
            $product_data = array(
                'off' => 0,
                'position' => $position + 1,
            );
            Product::find(intval($id))->update($product_data);
        }
        foreach ($data['off'] as $id => $position) {
            $product_data = array(
                'off' => 1,
                'position' => 0,
            );
            Product::find(intval($id))->update($product_data);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
    } elseif ('add_product' == $_POST['form_name']) {
        Product::insert($_POST);
        header('Location: manage_items.php?category=' . $_GET['category']);
    } elseif ('edit_product' == $_POST['form_name']) {
        $product = Product::find(intval($_POST['id']));
        $product->update($_POST);
        header('Location: manage_items.php?category=' . $product->category);
    }
}

if ($_GET['category']) {
    $category = Category::find(intval($_GET['category']));
    $products = Product::search(array('category' => $category->id))->order('position ASC');
} elseif ($_GET['product']) {
    $product = Product::find(intval($_GET['product']));
} else {
    $categories = Category::search(1);
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
<title>Buddy House</title>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js"></script>
<style>
ul { list-style-type: none; margin: 0; padding: 0; width: 500px; }
ul li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
ul li span.ui-icon { position: absolute; margin-left: -1.3em; }
ul li span.function { font-size: 12px; margin-left: 10px; }
</style>
</head>

<body>
<?php if ($categories) { ?>
<h3>分類</h3>
<?php foreach ($categories as $category) { ?>
<a href="manage_items.php?category=<?= intval($category->id) ?>"><?= htmlspecialchars($category->name) ?></a><br>
<?php } ?>
<?php } elseif ($products) { ?>
<h3><?= htmlspecialchars($category->name) ?></h3>
<form id="form-products" method="post">
    <input type="submit" class="save" value="儲存" />
    <input type="button" value="回上頁" onclick="document.location.href='manage_items.php';" />
    <fieldset>
        <ul id="ul-on">
            <?php foreach ($products->search(array('off' => 0)) as $product) { ?>
            <li class="ui-state-default" data-id="<?= intval($product->id) ?>">
            <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
            <span><?= htmlspecialchars($product->name) ?></span>
            <span class="function"><a href="manage_items.php?product=<?= intval($product->id) ?>">編輯</a></span>
            <span class="function"><a href="" class="off">下架</a></span>
            </li>
            <?php } ?>
        </ul>
        <hr>
        <h4>已下架</h4>
        <ul id="ul-off">
            <?php foreach ($products->search(array('off' => 1)) as $product) { ?>
            <li class="ui-state-default" data-id="<?= intval($product->id) ?>">
            <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
            <span><?= htmlspecialchars($product->name) ?></span>
            <span class="function"><a href="manage_items.php?product=<?= intval($product->id) ?>">編輯</a></span>
            <span class="function"><a href="" class="on">上架</a></span>
            </li>
            <?php } ?>
        </ul>
        <hr>
    </fieldset>
    <input type="submit" class="save" value="儲存" />
    <input type="button" value="回上頁" onclick="document.location.href='manage_items.php';" />
    <input type="hidden" name="data" value="" />
    <input type="hidden" name="form_name" value="products" />
</form>
<br><br>
<form name="form-add-product" method="post">
    <h4>新增項目</h4>
    <fieldset>
        名稱：<input type="text" name="name" value="" /><br>
        價格：<input type="text" name="price" value="" /><br>
        成本歸類-叫料：<input type="text" name="cost_percent_1" value="0" /><br>
        成本歸類-好市多：<input type="text" name="cost_percent_2" value="0" /><br>
        成本歸類-德淶寶：<input type="text" name="cost_percent_3" value="0" /><br>
        成本歸類-比利時：<input type="text" name="cost_percent_4" value="0" /><br>
        成本歸類-市場：<input type="text" name="cost_percent_5" value="0" /><br>
        <input type="hidden" name="category" value="<?= intval($category->id) ?>" /><br>
        <input type="submit" class="save" value="新增" />
        <input type="hidden" name="form_name" value="add_product" />
    </fieldset>
</form>
<hr>
<?php } elseif ($product) { ?>
<form name="form-edit-product" method="post">
    <h4>編輯項目</h4>
    <fieldset>
        名稱：<input type="text" name="name" value="<?= htmlspecialchars($product->name) ?>" /><br>
        價格：<input type="text" name="price" value="<?= htmlspecialchars($product->price) ?>" /><br>
        成本歸類-叫料：<input type="text" name="cost_percent_1" value="<?= htmlspecialchars($product->cost_percent_1) ?>" /><br>
        成本歸類-好市多：<input type="text" name="cost_percent_2" value="<?= htmlspecialchars($product->cost_percent_2) ?>" /><br>
        成本歸類-德淶寶：<input type="text" name="cost_percent_3" value="<?= htmlspecialchars($product->cost_percent_3) ?>" /><br>
        成本歸類-比利時：<input type="text" name="cost_percent_4" value="<?= htmlspecialchars($product->cost_percent_4) ?>" /><br>
        成本歸類-市場：<input type="text" name="cost_percent_5" value="<?= htmlspecialchars($product->cost_percent_5) ?>" /><br>
        <input type="hidden" name="id" value="<?= intval($product->id) ?>" /><br>
        <input type="submit" class="save" value="更新" />
        <input type="hidden" name="form_name" value="edit_product" />
    </fieldset>
</form>
<?php } ?>

<script>
$('#ul-on').on('click', '.off', function(e){
    e.preventDefault();
    $(this).closest('li').appendTo('#ul-off');
    $(this).removeClass('off').addClass('on').text('上架');
});

$('#ul-off').on('click', '.on', function(e){
    e.preventDefault();
    $(this).closest('li').appendTo('#ul-on');
    $(this).removeClass('on').addClass('off').text('下架');
});

$('#form-products').submit(function(e){
    if (!confirm('確定儲存？')) {
        e.preventDefault();
        return;
    }
    var data = {on: {}, off: {}};
    $('#ul-on').find('li').each(function(i){
        var id = $(this).data('id');
        data.on[id] = i;
    });
    $('#ul-off').find('li').each(function(i){
        var id = $(this).data('id');
        data.off[id] = i;
    });
    $('input[name=data]').val(JSON.stringify(data));
});

$(function(){
    $("#ul-on").sortable();
});
</script>
</body>
</html>

