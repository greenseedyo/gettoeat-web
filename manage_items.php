<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

if ($_POST) {
    if ('add_category' == $_POST['form_name']) {
        $category = $store->create_categories($_POST);
        header('Location: manage_items.php?category_id=' . $category->id);
    } elseif ('edit_category' == $_POST['form_name']) {
        $category = $store->getCategoryById($_POST['id']);
        if (!$category instanceof CategoryRow) {
            die('bad session.');
        }
        $category->update($_POST);
        header('Location: manage_items.php?category_id=' . $category->id);
    } elseif ('products_order' == $_POST['form_name']) {
        $data = json_decode($_POST['data'], true);
        foreach ($data['on'] as $id => $position) {
            $product_data = array(
                'off' => 0,
                'position' => $position + 1,
            );
            $store->getProductById($id)->update($product_data);
        }
        foreach ($data['off'] as $id => $position) {
            $product_data = array(
                'off' => 1,
                'position' => 0,
            );
            $store->getProductById($id)->update($product_data);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
    } elseif ('add_product' == $_POST['form_name']) {
        $category = $store->getCategoryById($_GET['category_id']);
        $category->create_products($_POST);
        header('Location: manage_items.php?category_id=' . $_GET['category_id']);
    } elseif ('edit_product' == $_POST['form_name']) {
        // TODO: use $store->getProductById()
        $product = $store->getProductById($_POST['id']);
        $product->update($_POST);
        header('Location: manage_items.php?category_id=' . $product->category_id);
    }
}

if ($_GET['category_id']) {
    $category = $store->getCategoryById($_GET['category_id']);
    $products = $category->products->order('position ASC');
    $online_products = array();
    $offline_products = array();
    foreach ($products as $product) {
        if ($product->off) {
            $offline_products[] = $product;
        } else {
            $online_products[] = $product;
        }
    }
} elseif ($_GET['product']) {
    $product = $store->getProductById($_GET['product']);
} else {
    $categories = $store->categories;
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($store->nickname) ?> 菜單管理</title>
    <?php include("{$_SERVER['DOCUMENT_ROOT']}/include/static_common.phtml"); ?>
</head>

<body>
<?php include("{$_SERVER['DOCUMENT_ROOT']}/include/manage_nav.phtml"); ?>
<div class="container">
    <div>
        <h1><?= htmlspecialchars($store->nickname) ?> 菜單管理</h1>
    </div>

    <?php if ($categories instanceof Pix_Table_ResultSet) { ?>
    <div>
        <div class="page-header">
            <h2>分類</h2>
        </div>
        <?php foreach ($categories as $category) { ?>
        <div class="list-group">
            <a class="list-group-item list-group-item-info" href="manage_items.php?category_id=<?= intval($category->id) ?>"><?= htmlspecialchars($category->name) ?></a>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">新增分類</h3>
            </div>
            <div class="panel-body">
                <form id="form-add-category" method="post">
                    <fieldset>
                        名稱：<input type="text" name="name" value="" />
                        <input type="submit" class="save" value="新增" />
                        <input type="hidden" name="form_name" value="add_category" />
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
    <?php } elseif ($products instanceof Pix_Table_ResultSet) { ?>
    <div>
        <div class="page-header">
            <h2>編輯分類: <?= htmlspecialchars($category->name) ?></h2>
            <input type="button" value="回上頁" onclick="document.location.href='manage_items.php';" />
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">更改名稱</h3>
            </div>
            <div class="panel-body">
                <form id="form-edit-category" method="post">
                    <fieldset>
                        名稱：<input type="text" name="name" value="<?= htmlspecialchars($category->name) ?>" />
                        <input type="submit" class="save" value="更新" />
                        <input type="hidden" name="id" value="<?= intval($category->id) ?>" />
                        <input type="hidden" name="form_name" value="edit_category" />
                    </fieldset>
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">項目編輯/排序</h3>
            </div>
            <div class="panel-body">
                <form id="form-products-order" method="post">
                    <input type="submit" class="save" value="儲存" />
                    <fieldset>
                        <h4>上架中</h4>
                        <ul id="ul-on" class="list-group">
                            <?php foreach ($online_products as $product) { ?>
                            <li class="list-group-item ui-state-default" data-id="<?= intval($product->id) ?>">
                                <span class="ui-icon ui-icon-arrowthick-2-n-s" style="display:inline-block"></span>
                                <span><?= htmlspecialchars($product->name) ?> ($<?= intval($product->price) ?>)</h4></span>
                                <span class="function"><a href="manage_items.php?product=<?= intval($product->id) ?>">編輯</a></span>
                                <span class="function"><a href="" class="off">下架</a></span>
                            </li>
                            <?php } ?>
                        </ul>
                        <h4>已下架</h4>
                        <ul id="ul-off" class="list-group">
                            <?php foreach ($offline_products as $product) { ?>
                            <li class="list-group-item ui-state-default" data-id="<?= intval($product->id) ?>">
                                <span class="ui-icon ui-icon-arrowthick-2-n-s" style="display:inline-block"></span>
                                <span><?= htmlspecialchars($product->name) ?> ($<?= intval($product->price) ?>)</h4></span>
                                <span class="function"><a href="manage_items.php?product=<?= intval($product->id) ?>">編輯</a></span>
                                <span class="function"><a href="" class="on">上架</a></span>
                            </li>
                            <?php } ?>
                        </ul>
                    </fieldset>
                    <input type="submit" class="save" value="儲存" />
                    <input type="hidden" name="data" value="" />
                    <input type="hidden" name="form_name" value="products_order" />
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">新增項目</h3>
            </div>
            <div class="panel-body">
                <form id="form-add-product" method="post">
                    <fieldset>
                        名稱：<input type="text" name="name" value="" /><br>
                        價格：<input type="text" name="price" value="" /><br>
                        <input type="hidden" name="category_id" value="<?= intval($category->id) ?>" /><br>
                        <input type="submit" class="save" value="新增" />
                        <input type="hidden" name="form_name" value="add_product" />
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
    <?php } elseif ($product) { ?>
    <div>
        <div class="page-header">
            <h2>編輯項目: <?= htmlspecialchars($product->name) ?></h2>
            <input type="button" value="回上頁" onclick="document.location.href='manage_items.php?category_id=<?= intval($product->category_id) ?>';" />
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">更新資訊</h3>
            </div>
            <div class="panel-body">
                <form id="form-edit-product" method="post">
                    <fieldset>
                        名稱：<input type="text" name="name" value="<?= htmlspecialchars($product->name) ?>" /><br>
                        價格：<input type="text" name="price" value="<?= htmlspecialchars($product->price) ?>" /><br>
                        <input type="hidden" name="id" value="<?= intval($product->id) ?>" /><br>
                        <input type="submit" class="save" value="更新" />
                        <input type="hidden" name="form_name" value="edit_product" />
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

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

$('#form-products-order').submit(function(e){
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

$('#form-add-product, #form-edit-product').submit(function(e){
    var price = parseInt($(this).find('input[name=price]').val());
    if (isNaN(price)) {
        alert('請輸入價格');
        e.preventDefault();
        return;
    }
});

$(function(){
    $("#ul-on").sortable();
});
</script>
</body>
</html>

