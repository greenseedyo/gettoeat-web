<?php
require_once 'config.php';

/* 月營收資料 */
$month_names = array();
$month_turnovers = array();
$month_foods = array();
$month_drinks = array();
$month_discounts = array();
for ($i = 11; $i >= 0; $i --) {
    $yearmonth = mktime(0, 0, 0, date('m') - $i, 1, date('Y'));
    $year = date('Y', $yearmonth);
    $month = date('m', $yearmonth);
    $key = "{$year}-{$month}";
    $month_names[] = $key;
    $month_turnovers[$key] = 0;
    $month_foods[$key] = 0;
    $month_drinks[$key] = 0;
    $month_discounts[$key] = 0;
    foreach (Bill::search(array('year' => $year, 'month' => $month)) as $bill) {
        $month_turnovers[$key] += $bill->price;
        foreach ($bill->items as $item) {
            if ($item->isFood()) {
                $month_foods[$key] += $item->getPrice() * $item->amount;
            } elseif ($item->isDrink()) {
                $month_drinks[$key] += $item->getPrice() * $item->amount;
            }
        }
        foreach ($bill->discounts as $discount) {
            $month_discounts[$key] += $discount->value;
        }
    }
}

/* 每日營收資料 */
$date_names = array();
$date_turnovers = array();
$date_foods = array();
$date_drinks = array();
$date_discounts = array();
for ($i = 30; $i >= 0; $i--) {
    $date_timestamp = mktime(0, 0, 0, date('m'), date('d') - $i, date('Y'));
    $year = date('Y', $date_timestamp);
    $month = date('m', $date_timestamp);
    $date = date('d', $date_timestamp);
    $day = date('D', $date_timestamp);
    $key = "{$month}-{$date}({$day})";
    $date_names[] = $key;
    $date_turnovers[$key] = 0;
    $date_foods[$key] = 0;
    $date_drinks[$key] = 0;
    $date_discounts[$key] = 0;
    foreach (Bill::search(array('year' => $year, 'month' => $month, 'date' => $date)) as $bill) {
        $date_turnovers[$key] += $bill->price;
        foreach ($bill->items as $item) {
            if ($item->isFood()) {
                $date_foods[$key] += $item->getPrice() * $item->amount;
            } elseif ($item->isDrink()) {
                $date_drinks[$key] += $item->getPrice() * $item->amount;
            }
        }
        foreach ($bill->discounts as $discount) {
            $date_discounts[$key] += $discount->value;
        }
    }
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
<script type="text/javascript" src="static/highcharts.js"></script>
</head>

<body>
<div id="month_chart" style="min-width: 310px; height: 400px; margin: 0 auto 30px auto"></div>
<div id="date_chart" style="min-width: 310px; height: 400px; margin: 0 auto 30px auto"></div>

<script>
var chart;
var highchart = function(title, renderTo, categories, turnover, food, drink, discount){
    var char_options = {
        chart: {
            renderTo: renderTo,
            type: 'line',
            marginRight: 130,
            marginBottom: 25
        },
        title: {
            text: title,
            x: -20 //center
        },
        subtitle: {
            text: '',
            x: -20
        },
        xAxis: {
            categories: categories
        },
        yAxis: {
            title: {
                text: '金額'
            },
            plotlines: [{
            value: 0,
            width: 1,
            color: '#808080'
            }]
        },
        tooltip: {
            pointformat: '{series.name}: <b>{point.y}</b><br/>',
            valuesuffix: ' 元',
            shared: true
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalalign: 'middle',
            borderwidth: 0
        },
        series: [{
            name: '總營收'
        }, {
            name: '食物'
        }, {
            name: '飲料'
        }, {
            name: '折扣'
        }]
    };

    char_options.series[0].data = $.parseJSON(turnover);
    char_options.series[1].data = $.parseJSON(food);
    char_options.series[2].data = $.parseJSON(drink);
    char_options.series[3].data = $.parseJSON(discount);

    chart = new Highcharts.Chart(char_options);
};

highchart(
    '月營收狀況',
    'month_chart',
    [<?= implode(',', array_map('json_encode', $month_names)) ?>],
    "[<?= implode(',', array_map('json_encode', $month_turnovers)) ?>]",
    "[<?= implode(',', array_map('json_encode', $month_foods)) ?>]",
    "[<?= implode(',', array_map('json_encode', $month_drinks)) ?>]",
    "[<?= implode(',', array_map('json_encode', $month_discounts)) ?>]"
);

highchart(
    '30天內營收狀況',
    'date_chart',
    [<?= implode(',', array_map('json_encode', $date_names)) ?>],
    "[<?= implode(',', array_map('json_encode', $date_turnovers)) ?>]",
    "[<?= implode(',', array_map('json_encode', $date_foods)) ?>]",
    "[<?= implode(',', array_map('json_encode', $date_drinks)) ?>]",
    "[<?= implode(',', array_map('json_encode', $date_discounts)) ?>]"
);
</script>
</body>
</html>

