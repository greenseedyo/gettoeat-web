<?php
require_once 'config.php';

if ($event = $store->getEventById(intval($_GET['id']))) {
    include(VIEWS_DIR . "/index/event_forms/{$event->type->name}.html");
}
