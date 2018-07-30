<?php
require_once 'config.php';

if ($_POST) {
    if ('add_group' == $_POST['form_name']) {
        $group = $store->create_staff_groups($_POST);
        header('Location: manage_staffs.php?group_id=' . $group->id);
    } elseif ('edit_group' == $_POST['form_name']) {
        $group = $store->getStaffGroupById($_POST['id']);
        if (!$group instanceof StaffGroupRow) {
            die('bad session.');
        }
        $group->update($_POST);
        header('Location: manage_staffs.php?group_id=' . $group->id);
    } elseif ('add_staff' == $_POST['form_name']) {
        $group = $store->getStaffGroupById($_GET['group_id']);
        $data = $_POST;
        $data['store_id'] = $store->id;
        $group->create_staffs($data);
        header('Location: manage_staffs.php?group_id=' . $_GET['group_id']);
    } elseif ('edit_staff' == $_POST['form_name']) {
        $staff = $store->getStaffById($_POST['id']);
        $staff->update($_POST);
        header('Location: manage_staffs.php?group_id=' . $staff->group_id);
    } elseif ('toggle_staff_status' == $_POST['form_name']) {
        $staff = $store->getStaffById($_POST['id']);
        if (!$staff instanceof StaffRow) {
            die('bad session.');
        }
        if ($staff->isOff()) {
            $staff->setOn();
        } else {
            $staff->setOff();
        }
        header('Location: manage_staffs.php?staff_id=' . $staff->id);
    }
}

if ($_GET['group_id']) {
    $group = $store->getStaffGroupById($_GET['group_id']);
    $staffs = $group->staffs;
    $on_staffs = array();
    $off_staffs = array();
    foreach ($staffs as $staff) {
        if ($staff->off) {
            $off_staffs[] = $staff;
        } else {
            $on_staffs[] = $staff;
        }
    }
} elseif ($_GET['staff_id']) {
    $staff = $store->getStaffById($_GET['staff_id']);
} else {
    $groups = $store->staff_groups;
}

include(VIEWS_DIR . '/manage_staffs.html');
