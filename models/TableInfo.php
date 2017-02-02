<?php

class TableInfoRow extends Pix_Table_Row
{
}

class TableInfo extends Pix_Table
{
    public $_rowClass = 'TableInfoRow';

	public function init()
	{
		$this->_name = 'table_info';
		$this->_primary = array('id');

		$this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
		$this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['version'] = array('type' => 'tinyint', 'unsigned' => true);
		$this->_columns['data'] = array('type' => 'text');

        $this->addIndex('store_id', array('store_id'));
	}
}

