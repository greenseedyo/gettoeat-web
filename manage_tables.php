<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

$tables_info = $store->getTablesInfo();
$helper = $tables_info->getHelper();
$tables = $helper->getTables();
$active_tables = array();
$inactive_tables = array();
$z = count($tables);
$max_height = 0;
foreach ($tables as $table) {
    if ($table->active) {
        $active_tables[] = $table;
    } else {
        $table->x = 10 * (++ $x);
        $table->y = 10;
        $table->z = $z --;
        $max_height = ($table->height > $max_height ? $table->height : $max_height);
        $inactive_tables[] = $table;
    }
}
$well_height = $max_height + 20;

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
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">桌位圖</h3>
            </div>
            <div class="panel-body" style="font-size: 0">
                <div class="alert alert-info" role="alert">
                    <ul>
                        <li>將網格大小調整至適當尺寸，即為室內空間</li>
                        <li>將桌號拖曳至網格內並調整至適當尺寸</li>
                    </ul>
                </div>
                <form id="form-save-table" method="post">
                    <fieldset>
                        <button type="submit" class="btn btn-default">儲存</button>
                        <input type="hidden" name="form_name" value="save_table" />
                    </fieldset>
                </form>
                <hr>
                <div id="tables-inactive" class="well" style="position: relative; height: <?= intval($well_height) ?>px;">
                    <?php foreach ($inactive_tables as $key => $table) { ?><div type="button" class="table-grid btn btn-default btn-lg" style="position: absolute; background-color: #eee; border: 0px; box-shadow: 0px 0px 0px 1px #bbb inset; box-sizing: content-box; padding: 0; width: <?= intval($table->width) ?>px; height: <?= intval($table->height) ?>px; line-height: <?= intval($table->height) ?>px; left: <?= intval($table->x) ?>px; top: <?= intval($table->y) ?>px; z-index: <?= intval($table->z) ?>"><?= htmlspecialchars($table->name) ?></div><?php } ?>
                </div>

                <div id="trash-can" class="well" style="display: inline-block; height: 100px; width: 100px; vertical-align:top; font-size: 20px; text-align: center; line-height: 60px;">
                    <span class="glyphicon glyphicon-trash"></span>
                </div>

                <div style="display: inline-block; margin: 10px;">&nbsp;</div>

                <div id="tables-active" style="display: inline-block; position: relative; box-shadow: 0px 0px 0px 1px #ddd inset; background-color: transparent; background-image: linear-gradient(0deg, transparent 1%, #ddd 0%, transparent 3%),linear-gradient(90deg, transparent 1%, #ddd 0%, transparent 3%),  linear-gradient(180deg, transparent 1%, #ddd 0%, transparent 3%), linear-gradient(270deg, transparent 1%, #ddd 0%, transparent 3%); background-size: 20px 20px; height: 400px; width: 600px;">
                </div>
                <hr>
                <form id="form-save-table" method="post">
                    <fieldset>
                        <button type="submit" class="btn btn-default">儲存</button>
                        <input type="hidden" name="form_name" value="save_table" />
                    </fieldset>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<script>
$(function() {
    var grid = 10;
    $(".table-grid")
        .draggable({
            stack: "div",
            grid: [grid, grid],
            stop: function() {
                var $grid = $(this);
                if (false == $grid.data("active")) {
                    $grid.appendTo("#tables-inactive").css({"top": "10px", "left": "10px"});
                }
            }
        })
        .resizable({
            grid: grid,
            resize: function() {
                console.log($(this).height());
                $(this).css("line-height", $(this).height() + "px");
            }
        });

    $("#tables-active")
        .resizable({grid: grid})
        .droppable({
            acceept: ".table-grid",
            over: function(event, ui) {
                $(ui.draggable).data("active", true).css({
                    "background-color": "#ffeebb"
                });
            },
            drop: function(event, ui) {
                var $grid = $(ui.draggable);
                if (!$grid.parent().is(this)) {
                    var gridOffset = $grid.offset();
                    var activeOffset = $(this).offset();
                    $grid.appendTo(this).css({
                        "top": gridOffset.top - activeOffset.top - 1,
                        "left": gridOffset.left - activeOffset.left - 1
                    });
                }
            },
            out: function(event, ui) {
                $(ui.draggable).data("active", false).css("background-color", "#ffffff");
            }
        });

    $("#form-save-table").submit(function(e) {
    });
});
</script>
</body>
</html>

