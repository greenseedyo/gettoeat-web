<?php
require_once 'config.php';

$store = Store::getByAccount($_SESSION['store_account']);
if (!$store instanceof StoreRow) {
    die('找不到此帳號');
}

$tables_info = $store->getTablesInfo();
$helper = $tables_info->getHelper();
$total_height = $helper->getTotalHeight();
$total_width = $helper->getTotalWidth();
$tables = $helper->getTables();
$active_tables = array();
$inactive_tables = array();
$z = count($tables);
$new_grid_x = 20;
$new_grid_y = 10;
$new_grid_z = $z;
$new_grid_width = 80;
$new_grid_height = 40;
foreach ($tables as $table) {
    if ($table->active) {
        $active_tables[] = $table;
    } else {
        $table->x = $new_grid_x * (++ $x);
        $table->y = $new_grid_y;
        $table->z = (-- $z);
        $inactive_tables[] = $table;
    }
}

if ($_POST) {
    if ('save_table' == $_POST['form_name']) {
        print_r($_POST['tables_info']);
        $helper->save($_POST['tables_info']);
    }
    exit;
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

                <div id="tables-inactive" class="well">
                    <?php foreach ($inactive_tables as $key => $table) { ?>
                    <div class="table-grid btn btn-default btn-lg"
                        data-width="<?= intval($table->width) ?>px"
                        data-height="<?= intval($table->height) ?>px"
                        data-line-height="<?= intval($table->height) ?>px"
                        data-left="<?= intval($table->x) ?>px"
                        data-top="<?= intval($table->y) ?>px"
                        data-z-index="<?= intval($table->z) ?>"
                        data-active="<?= intval($table->active) ?>"
                        data-name="<?= htmlspecialchars($table->name) ?>"></div>
                    <?php } ?>
                </div>

                <div id="trash-can" class="well">
                    <span class="glyphicon glyphicon-trash"></span>
                </div>

                <div class="inline-block-separator">&nbsp;</div>

                <div id="tables-active" data-height="<?= intval($total_height) ?>" data-width="<?= intval($total_width) ?>">
                    <?php foreach ($active_tables as $key => $table) { ?>
                    <div class="table-grid btn btn-default btn-lg"
                        data-width="<?= intval($table->width) ?>px"
                        data-height="<?= intval($table->height) ?>px"
                        data-line-height="<?= intval($table->height) ?>px"
                        data-left="<?= intval($table->x) ?>px"
                        data-top="<?= intval($table->y) ?>px"
                        data-z-index="<?= intval($table->z) ?>"
                        data-active="<?= intval($table->active) ?>"
                        data-name="<?= htmlspecialchars($table->name) ?>"></div>
                    <?php } ?>
                </div>
                <hr>
                <form id="form-save-table" method="post">
                    <fieldset>
                        <button type="submit" class="btn btn-default">儲存</button>
                        <input type="hidden" name="form_name" value="save_table" />
                        <input type="hidden" name="tables_info" value="" />
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var GTE = GTE || {};
    GTE.gridPixel = 10;
    GTE.new_grid_width = <?= intval($new_grid_width) ?>;
    GTE.new_grid_height = <?= intval($new_grid_height) ?>;
    GTE.new_grid_x = <?= intval($new_grid_x) ?>;
    GTE.new_grid_y = <?= intval($new_grid_y) ?>;
    GTE.new_grid_z = <?= intval($new_grid_z) ?>;
</script>
<script type="text/javascript" src="/scripts/manage_tables.js?v=<?= STATIC_VERSION ?>"></script>
</body>
</html>

