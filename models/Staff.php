<?php
require_once(ROOT_DIR . '/helpers/ip.php');

class StaffRow extends Pix_Table_Row
{
    public function isOff()
    {
        return $this->off ? true : false;
    }

    public function setOn()
    {
        $this->update(array('off' => 0));
    }

    public function setOff()
    {
        $this->update(array('off' => 1));
    }

    public function preInsert($changed_fields = null)
    {
        $same_code_staff = Staff::getByStoreIdAndCode($this->store_id, $this->code);
        if ($same_code_staff and $same_code_staff->id != $this->id) {
            throw new StaffCodeRepeatedException;
        }
    }

    public function preUpdate($changed_fields = null)
    {
        if ($changed_fields['code']) {
            $same_code_staff = Staff::getByStoreIdAndCode($this->store_id, $this->code);
            if ($same_code_staff and $same_code_staff->id != $this->id) {
                throw new StaffCodeRepeatedException;
            }
        }
    }

    public function punch($type)
    {
        $ip_helper = new Helpers\IpHelper();
        $client_ip = $ip_helper->getClientLongIp();
        // TODO: 檢查本日是否已打過卡
        $data = array(
            'type' => $type,
            'timestamp' => time(),
            'created_from' => $client_ip,
            'updated_from' => $client_ip,
        );
        $this->create_punch_logs($data);
    }
}

class Staff extends Pix_Table
{
    public $_rowClass = 'StaffRow';

    public function init()
    {
        $this->_name = 'staff';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['group_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['email'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['phone'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['code'] = array('type' => 'varchar', 'size' => 10);
        $this->_columns['off'] = array('type' => 'tinyint', 'unsigned' => true, 'size' => 1);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_by'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('store_id_off', array('store_id', 'off'));
        $this->addIndex('store_id_code', array('store_id', 'code'));

        $this->_relations['group'] = array('rel' => 'has_one', 'type' => 'StaffGroup', 'foreign_key' => 'group_id');
        $this->_relations['punch_logs'] = array('rel' => 'has_many', 'type' => 'PunchLog', 'foreign_key' => 'staff_id');
    }

    public static function getByStoreIdAndCode(int $store_id, string $code): ?StaffRow
    {
        return self::search(array('store_id' => $store_id, 'code' => $code))->first();
    }
}


class StaffCodeRepeatedException extends Exception
{
}
