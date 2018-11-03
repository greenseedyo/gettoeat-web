<?php
require_once __DIR__ . '/../config.php';

$shift = $store->shifts->order('created_at DESC')->first();
//$shift->pushMessageToLineBotChats();
