<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

$tables_info = $store->getTablesInfo();
$helper = $tables_info->getHelper();
$tables = $helper->getTables();

if ($_POST) {
    if ('add_table' == $_POST['form_name']) {
        $helper->addTable($_POST['name']);
        $helper->save();
        header('Location: manage_tables.php');
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($store->nickname) ?> 桌號管理</title>
    <?php include("{$_SERVER['DOCUMENT_ROOT']}/include/static_common.phtml"); ?>
</head>

<body>
<?php include("{$_SERVER['DOCUMENT_ROOT']}/include/manage_nav.phtml"); ?>
<div class="container">
    <div>
        <h1><?= htmlspecialchars($store->nickname) ?> 桌號管理</h1>
    </div>
    <div>
        <?php if (!empty($tables)) { ?>
        <div class="page-header">
            <h2>桌號</h2>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">桌位圖</h3>
            </div>
            <div class="panel-body">
                <div id="tables-off" class="well">
                    <?php foreach ($tables as $key => $table) { ?>
                    <div type="button" class="table-grid btn btn-default btn-lg"><?= htmlspecialchars($table->name) ?></div>
                    <?php } ?>
                </div>
                <div id="tables-on" class="well well-lg">
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">新增桌號</h3>
            </div>
            <div class="panel-body">
                <form id="form-add-table" method="post">
                    <fieldset>
                        名稱：<input type="text" name="name" value="" />
                        <input type="submit" class="save" value="新增" />
                        <input type="hidden" name="form_name" value="add_table" />
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    var grid = 10;
    $(".table-grid").draggable({stack: "div", grid: [grid, grid]}).resizable({grid: grid});
    $("#tables-on")
        .resizable({grid: grid})
        .droppable({
            acceept: ".table-grid",
            over: function(event, ui) {
                $(ui.draggable).css("background-color", "#ffeebb");
            },
            out: function(event, ui) {
                $(ui.draggable).css("background-color", "#ffffff");
            }
        });
});
</script>
</body>
</html>

