<?php
require_once 'config.php';
require_once(ROOT_DIR . '/helpers/StatHelper.php');
if (php_sapi_name() == "cli") {
    // FIXME: 應該要用 api，但現在只有 buddyhouse.gettoeat.com 有 ssl 憑證
    $store_account = 'buddyhouse';
}
// FIXME: 應該要用 api，但現在只有 buddyhouse.gettoeat.com 有 ssl 憑證
if ('buddyhouse' !== $store_account) {
    die();
}
$store = null;

if ('development' == $environment) {
    // Buddyhouse
    //$msg = '{"events":[{"type":"join","replyToken":"6cc68fd121f54d3ca479fb63c55f4f32","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","type":"group"},"timestamp":1541224257292}]}';
    //$msg = '{"events":[{"type":"message","replyToken":"2c0ed637105845f78964419efe51a090","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","userId":"U8d06f9b05c23c2e1279dce883a3d3dc5","type":"group"},"timestamp":1541263944809,"message":{"type":"text","id":"8811946762260","text":"本月營收"}}]}';
    //$msg = '{"events":[{"type":"message","replyToken":"c1633358289645e79aa0b9bd34195513","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","userId":"U8d06f9b05c23c2e1279dce883a3d3dc5","type":"group"},"timestamp":1541188811207,"message":{"type":"text","id":"8807694197168","text":"今誰收"}}]}';
    //$msg = '{"events":[{"type":"message","replyToken":"c1633358289645e79aa0b9bd34195513","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","userId":"U8d06f9b05c23c2e1279dce883a3d3dc5","type":"group"},"timestamp":1541188811207,"message":{"type":"text","id":"8807694197168","text":"各位 營收匯一下"}}]}';
    // GetToEat
    //$msg = '{"events":[{"type":"message","replyToken":"ba86ae42f46b4b1c9af51710890da17e","source":{"groupId":"C00cde8174d7577c570eea42d20071654","userId":"U3ab951088274f21f42a22876e1eabb77","type":"group"},"timestamp":1546355372427,"message":{"type":"text","id":"9109996296131","text":"訂閱buddyhouse"}}],"destination":"U7fe7bda5e4ca12e2992b7f3493eaa113"}';
    $msg = '{"events":[{"type":"message","replyToken":"ba86ae42f46b4b1c9af51710890da17e","source":{"groupId":"C00cde8174d7577c570eea42d20071654","userId":"U3ab951088274f21f42a22876e1eabb77","type":"group"},"timestamp":1546355372427,"message":{"type":"text","id":"9109996296131","text":"本月營收"}}],"destination":"U7fe7bda5e4ca12e2992b7f3493eaa113"}';
} else {
    $msg = file_get_contents('php://input');
}

echo "message: {$msg}\n";

function getTotalSales($store, $start_date, $end_date)
{
    $day_change_interval = new DateInterval("PT{$store->getDateChangeAt()}H");
    $start_datetime = (new Datetime($start_date))->add($day_change_interval);
    $end_datetime = (new Datetime($end_date))->add(new DateInterval('P1D'))->add($day_change_interval);
    $period_interval = $start_datetime->diff($end_datetime);
    $helper = new Helpers\StatHelper($store);
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
    $helper = new Helpers\StatHelper($store);
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

if (!$source_id) {
    die('source id not found.');
}

if (!$line_bot_chat = LineBotChat::getBySource($source_type, $source_id)) {
    $line_bot_chat = LineBotChat::insert(array('source_type' => $source_type, 'source_id' => $source_id));
}

$reply_token = $event->replyToken ?: null;

switch ($event_type) {
case 'join':
    $line_bot_chat->update(array('joined_at' => time()));
    $reply_message = '請輸入"訂閱+您的帳號"，例:"訂閱store123"';
    break;
case 'message':
    $message = $event->message;
    $text = $message->text;
    if (preg_match('/^訂閱(.*)$/', $text, $matches)) {
        $account = trim($matches[1]);
        if ($store = Store::getByAccount($account)) {
            $reply_message = "訂閱 {$store->name} 成功";
            $line_bot_chat->setStore($store);
        }
        break;
    }
    if (!$store = $line_bot_chat->store) {
        break;
    }
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
        $response = $line_bot_chat->pushMessage($reply_message);
    } else {
        $response = $line_bot_chat->replyMessage($reply_token, $reply_message);
    }
}

if ($push_message) {
    $response = $line_bot_chat->pushMessage($push_message);
}

if (200 != $response->getHttpStatus()) {
    // TODO: save log
    echo print_r($response);
} else {
    echo "ok\n";
}
