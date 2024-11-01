<?php

class TablesInfoRow extends Pix_Table_Row
{
    public function getHelper()
    {
        $helper = new TablesInfo_Helper($this);
        return $helper;
    }
}

class TablesInfo extends Pix_Table
{
    public $_rowClass = 'TablesInfoRow';

    public function init()
    {
        $this->_name = 'tables_info';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['version'] = array('type' => 'tinyint', 'unsigned' => true);
        $this->_columns['data'] = array('type' => 'text', 'default' => '');

        $this->addIndex('store_version', array('store_id', 'version'));
    }
}

