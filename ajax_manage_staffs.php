<?php
require_once 'config.php';

if ($_POST) {
    $error = false;
    if ('add_group' == $_POST['form_name']) {
        $group = $store->create_staff_groups($_POST);
        $msg = "新增成功";
        $redirect_to = '/manage_staffs.php?group_id=' . $group->id;
    } elseif ('edit_group' == $_POST['form_name']) {
        $group = $store->getStaffGroupById($_POST['id']);
        if (!$group instanceof StaffGroupRow) {
            die('bad session.');
        }
        $group->update($_POST);
        $msg = "更新成功";
        $redirect_to = '/manage_staffs.php?group_id=' . $group->id;
    } elseif ('add_staff' == $_POST['form_name']) {
        $group = $store->getStaffGroupById($_POST['group_id']);
        $data = $_POST;
        $data['store_id'] = $store->id;
        try {
            $group->create_staffs($data);
            $msg = "新增成功";
            $redirect_to = '/manage_staffs.php?group_id=' . $group->id;
        } catch (StaffCodeRepeatedException $e) {
            $error = true;
            $msg = "新增失敗: 人員代碼重複";
        }
    } elseif ('edit_staff' == $_POST['form_name']) {
        $staff = $store->getStaffById($_POST['id']);
        try {
            $staff->update($_POST);
            $msg = "更新成功";
            $redirect_to = '/manage_staffs.php?group_id=' . $staff->group_id;
        } catch (StaffCodeRepeatedException $e) {
            $error = true;
            $msg = "更新失敗: 人員代碼重複";
        }
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
        $msg = "更新成功";
    }

    $rtn = array(
        'error' => $error,
        'msg' => $msg,
        'redirect_to' => $redirect_to,
    );
    echo json_encode($rtn);
}

