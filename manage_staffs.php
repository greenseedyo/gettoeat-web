<?php
require_once 'config.php';

if ($_GET['group_id'] ?? false) {
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
} elseif ($_GET['staff_id'] ?? false) {
    $staff = $store->getStaffById($_GET['staff_id']);
} else {
    $groups = $store->staff_groups;
}

include(VIEWS_DIR . '/manage_staffs.html');
