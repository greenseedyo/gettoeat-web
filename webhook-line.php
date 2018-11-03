<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/StatHelper.php');

if ('development' == $environment) {
    //$msg = '{"events":[{"type":"join","replyToken":"6cc68fd121f54d3ca479fb63c55f4f32","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","type":"group"},"timestamp":1541224257292}]}';
    //$msg = '{"events":[{"type":"message","replyToken":"2c0ed637105845f78964419efe51a090","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","userId":"U8d06f9b05c23c2e1279dce883a3d3dc5","type":"group"},"timestamp":1541263944809,"message":{"type":"text","id":"8811946762260","text":"本月營收"}}]}';
    //$msg = '{"events":[{"type":"message","replyToken":"c1633358289645e79aa0b9bd34195513","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","userId":"U8d06f9b05c23c2e1279dce883a3d3dc5","type":"group"},"timestamp":1541188811207,"message":{"type":"text","id":"8807694197168","text":"今誰收"}}]}';
    $msg = '{"events":[{"type":"message","replyToken":"c1633358289645e79aa0b9bd34195513","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","userId":"U8d06f9b05c23c2e1279dce883a3d3dc5","type":"group"},"timestamp":1541188811207,"message":{"type":"text","id":"8807694197168","text":"各位 營收匯一下"}}]}';
} else {
    $msg = file_get_contents('php://input');
}

if (!$msg) {
    echo 'no message.';
    exit;
}

function getTotalSales($store, $start_date, $end_date)
{
    $day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");
    $start_datetime = (new Datetime($start_date))->add($day_change_interval);
    $end_datetime = (new Datetime($end_date))->add(new DateInterval('P1D'))->add($day_change_interval);
    $period_interval = $start_datetime->diff($end_datetime);
    $helper = new StatHelper($store);
    $helper->setTopic('overview');
    $helper->setInterval($period_interval);
    $helper->setStartDatetime($start_datetime);
    $helper->setEndDatetime($end_datetime);
    $stat_result = $helper->getStatResult();
    $chart = $stat_result->getChart('總覽');
    $datasets = $chart->getDataSets();
    $total_sales = $datasets['總營收'][0];
    return $total_sales;
}

function getStaffAdjustmentsLastMonth($store)
{
    $start_date = date('Y-m-01', strtotime('last month'));
    $end_date = date('Y-m-d', strtotime('last day of last month'));
    $day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");
    $start_datetime = (new Datetime($start_date))->add($day_change_interval);
    $end_datetime = (new Datetime($end_date))->add(new DateInterval('P1D'))->add($day_change_interval);
    $period_interval = $start_datetime->diff($end_datetime);
    $helper = new StatHelper($store);
    $helper->setTopic('shift');
    $helper->setInterval($period_interval);
    $helper->setStartDatetime($start_datetime);
    $helper->setEndDatetime($end_datetime);
    $stat_result = $helper->getStatResult();
    $chart = $stat_result->getChart('收款人結餘金額');
    $names = $chart->getXAxisCategories();
    $datasets = $chart->getDataSets();
    $amount = $datasets['金額'];
    $data = array();
    if (empty($names)) {
        return array();
    }
    foreach ($names as $key => $name) {
        $data[$name] = $amount[$key];
    }
    return $data;
}

$data = json_decode($msg);
$event = $data->events[0];
$event_type = $event->type;
$source = $event->source;
$source_type = $source->type;
switch ($source_type) {
case 'user':
    $source_id = $source->userId;
    break;
case 'group':
    $source_id = $source->groupId;
    break;
case 'room':
    $source_id = $source->roomId;
    break;
}

$line_bot_chat = $store->getLineBotChat($source_type, $source_id);
if (!$line_bot_chat) {
    $line_bot_chat = $store->create_line_bot_chats(array('source_type' => $source_type, 'source_id' => $source_id));
}

$reply_token = $event->replyToken ?: null;

switch ($event_type) {
case 'join':
    $line_bot_chat->update(array('joined_at' => time()));
    break;
case 'message':
    $message = $event->message;
    $text = $message->text;
    if (!$line_bot_chat = LineBotChat::getBySource($source_type, $source_id)) {
        break;
    }
    $store = $line_bot_chat->store;
    if (strstr($text, '本月') and strstr($text, '收')) {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        $total_sales = getTotalSales($store, $start_date, $end_date);
        $reply_message = sprintf("%s月營收至目前共 $%s", date('m'), $total_sales);
    } elseif (strstr($text, '上月') and strstr($text, '收')) {
        $start_date = date('Y-m-01', strtotime('last month'));
        $end_date = date('Y-m-d', strtotime('last day of last month'));
        $total_sales = getTotalSales($store, $start_date, $end_date);
        $reply_message = sprintf("%s月營收共 $%s", date('m', strtotime('last month')), $total_sales);
    } elseif (strstr($text, '各位') and strstr($text, '收') and strstr($text, '匯')) {
        $result = getStaffAdjustmentsLastMonth($store);
        $lines = array();
        $lines[] = sprintf("[%s月收帳統計]", date('m', strtotime('last month')));
        foreach ($result as $name => $amount) {
            $lines[] = sprintf("%s: %s%s", $name, $currency_symbol, $amount);
        }
        $reply_message = implode(PHP_EOL, $lines);
    } elseif (strstr($text, '今') and strstr($text, '誰') and strstr($text, '收')) {
        $staffs = $store->staffs->toArray('name');
        $key = array_rand($staffs);
        $reply_message = $staffs[$key] . "(來亂的)";
    }
    break;
case 'leave':
    $line_bot_chat->update(array('left_at' => time()));
    break;
}

if ($reply_message) {
    if ('development' == $environment) {
        $line_bot_chat->pushMessage($reply_message);
    } else {
        $line_bot_chat->replyMessage($reply_token, $reply_message);
    }
}

if ($push_message) {
    $line_bot_chat->pushMessage($push_message);
}

echo 'ok';
