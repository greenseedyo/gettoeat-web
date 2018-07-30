<?php

class StaffGroupRow extends Pix_Table_Row
{
}

class StaffGroup extends Pix_Table
{
    public $_rowClass = 'StaffGroupRow';

    public function init()
    {
        $this->_name = 'staff_group';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_by'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('store_id', array('store_id'));

        $this->_relations['staffs'] = array('rel' => 'has_many', 'type' => 'Staff', 'foreign_key' => 'group_id');
    }
}

