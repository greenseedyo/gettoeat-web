<?php

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
        $this->_columns['off'] = array('type' => 'tinyint', 'unsigned' => true, 'size' => 1);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_by'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('store_id_off', array('store_id', 'off'));

        $this->_relations['group'] = array('rel' => 'has_one', 'type' => 'StaffGroup', 'foreign_key' => 'group_id');
    }
}

