<?php
require_once 'config.php';

if ('development' == $environment) {
    $msg = '{"events":[{"type":"join","replyToken":"6cc68fd121f54d3ca479fb63c55f4f32","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","type":"group"},"timestamp":1541224257292}]}';
    //$msg = '{"events":[{"type":"message","replyToken":"42129cec8d204dec8ce47688285056e9","source":{"userId":"U8d06f9b05c23c2e1279dce883a3d3dc5","type":"user"},"timestamp":1541223730298,"message":{"type":"text","id":"8809243479449","text":"i"}}]}';
    //$msg = '{"events":[{"type":"message","replyToken":"c1633358289645e79aa0b9bd34195513","source":{"groupId":"Cfd0e257d93a9346c5ba7a48d2f4caae1","userId":"U8d06f9b05c23c2e1279dce883a3d3dc5","type":"group"},"timestamp":1541188811207,"message":{"type":"text","id":"8807694197168","text":"p"}}]}';
} else {
    $msg = file_get_contents('php://input');
}

$data = json_decode($msg);
$event_type = $data->events[0]->type;
$source = $data->events[0]->source;
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

switch ($event_type) {
case 'join':
    $line_bot_chat->update(array('joined_at' => time()));
    break;
case 'message':
    break;
case 'leave':
    $line_bot_chat->update(array('left_at' => time()));
    break;
}

echo 'ok';
