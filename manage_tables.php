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
$new_grid_x = 20;
$new_grid_y = 10;
$new_grid_z = $z;
$new_grid_width = 80;
$new_grid_height = 40;
$max_height = $new_grid_height;
foreach ($tables as $table) {
    if ($table->active) {
        $active_tables[] = $table;
    } else {
        $table->x = $new_grid_x * (++ $x);
        $table->y = $new_grid_y;
        $table->z = (-- $z);
        $max_height = ($table->height > $max_height ? $table->height : $max_height);
        $inactive_tables[] = $table;
    }
}
$well_height = $max_height + 20;

if ($_POST) {
    if ('save_table' == $_POST['form_name']) {
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
    <script type="text/javascript" src="/scripts/manage_tables.js?v=<?= STATIC_VERSION ?>"></script>
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
            <div class="panel-body" style="font-size: 0">
                <div class="alert alert-info" role="alert" style="font-size: initial">
                    <ul>
                        <li>將網格大小調整至適當尺寸，即為室內空間</li>
                        <li>將桌號拖曳至網格內並調整至適當尺寸</li>
                    </ul>
                </div>
                <hr>
                <form id="form-add-table">
                    <fieldset style="font-size: initial">
                        <div class="col-lg-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="name" value="" placeholder="新增桌號" />
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-default">新增</button>
                                </span>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div class="block-separator">&nbsp;</div>

                <div id="tables-inactive" class="well" style="height: <?= intval($well_height) ?>px;">
                    <?php foreach ($inactive_tables as $key => $table) { ?><div class="table-grid btn btn-default btn-lg" style="width: <?= intval($table->width) ?>px; height: <?= intval($table->height) ?>px; line-height: <?= intval($table->height) ?>px; left: <?= intval($table->x) ?>px; top: <?= intval($table->y) ?>px; z-index: <?= intval($table->z) ?>"><?= htmlspecialchars($table->name) ?></div><?php } ?>
                </div>

                <div id="trash-can" class="well">
                    <span class="glyphicon glyphicon-trash"></span>
                </div>

                <div class="inline-block-separator">&nbsp;</div>

                <div id="tables-active">
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
    var GTE = GTE || {};
    GTE.gridPixel = 10;
    GTE.new_grid_width = <?= intval($new_grid_width) ?>;
    GTE.new_grid_height = <?= intval($new_grid_height) ?>;
    GTE.new_grid_x = <?= intval($new_grid_x) ?>;
    GTE.new_grid_y = <?= intval($new_grid_y) ?>;
    GTE.new_grid_z = <?= intval($new_grid_z) ?>;

    $(".table-grid").initTableGrid(GTE.gridPixel);

    $("#tables-active")
        .resizable({grid: GTE.gridPixel})
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

    $("#trash-can").droppable({
        acceept: ".table-grid",
        drop: function(event, ui) {
            $(ui.draggable).remove();
        }
    });

    $("#form-add-table").submit(function(e){
        e.preventDefault();
        var $form = $(this);
        $("#tables-inactive").find(".table-grid").css("left", "+=" + GTE.new_grid_x);
        $("<div>")
            .addClass("table-grid btn btn-default btn-lg")
            .text($form.find(":input[name=name]").val())
            .width(GTE.new_grid_width)
            .height(GTE.new_grid_height)
            .css("line-height", GTE.new_grid_height + "px")
            .css("left", GTE.new_grid_x + "px")
            .css("top", GTE.new_grid_y + "px")
            .css("z-index", GTE.new_grid_z ++)
            .initTableGrid()
            .appendTo("#tables-inactive");
    });

    $("#form-save-table").submit(function(e) {
    });
});
</script>
</body>
</html>

