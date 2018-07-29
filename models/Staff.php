<?php

class StaffRow extends Pix_Table_Row
{
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
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['email'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['phone'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['activated'] = array('type' => 'tinyint', 'size' => 1);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_by'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('store_id_activated', array('store_id', 'activated'));
    }
}

