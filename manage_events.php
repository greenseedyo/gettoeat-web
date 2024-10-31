<?php
require_once 'config.php';

function assignEventData($event)
{
    $data = array(
        'id' => $event->id,
        'title' => $event->title,
        'start_date' => $event->start_at ? date('Y-m-d', $event->start_at) : '',
        'end_date' => $event->end_at ? date('Y-m-d', $event->end_at) : '',
        'type_description' => $event->type->description,
        'note' => $event->note,
        'data' => $event->getHelper()->getData(),
    );
    return $data;
}

if (!empty($_POST)) {
    if (in_array($_POST['form_name'], array('add_event', 'edit_event'))) {
        $start_date = $_POST['start_date'] ? new DateTime($_POST['start_date']) : null;
        $end_date = $_POST['end_date'] ? new DateTime($_POST['end_date']) : null;
        $data = array(
            'type_id' => $_POST['type_id'],
            'note' => $_POST['note'],
            'start_at' => $start_date ? $store->getDayStartAt($start_date) : 0,
            'end_at' => $end_date ? $store->getDayStartAt($end_date) : 0,
            'title' => $_POST['title'],
        );
        if ('edit_event' == $_POST['form_name']) {
            $event = Event::find(intval($_GET['id']));
            $event->update($data);
        } elseif ('add_event' == $_POST['form_name']) {
            $event = $store->create_events($data);
        }
        $event_helper = $event->getHelper();
        if ($_POST['data'] ?? false) {
            $data = json_decode($_POST['data'], 1);
            $event_helper->setData($data);
        } else {
            // TODO: 目前只有 PercentOff 會用到，不要放這裡
            $event_helper->setData(array(
                'percent' => $_POST['percent'] ?? null,
                'percent_reversed' => $_POST['percent_reversed'] ?? null,
            ));
        }
        header('Location: manage_events.php');
    }
}

if ($_GET['id'] ?? false) {
    $event = $store->getEventById(intval($_GET['id']));
    $event_data = assignEventData($event);
} else {
    $event_datas = array();
    foreach ($store->events as $event) {
        $event_datas[] = assignEventData($event);
    }
}

$event_types = EventType::search(1);
$event_type_datas = array();
foreach ($event_types as $event_type) {
    $event_type_data = $event_type->toArray();
    $event_type_data['selected'] = ($event_type->id == $event->type_id ? true : false);
    $event_type_datas[] = $event_type_data;
}

if (isset($_GET['action']) and 'get_form' == $_GET['action']) {
    $event_type = EventType::find(intval($_GET['type_id']));
    include(VIEWS_DIR . "/manage_events/forms/{$event_type->name}.html");
} else {
    include(VIEWS_DIR . '/manage_events.html');
}
