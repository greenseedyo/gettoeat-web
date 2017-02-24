<?php

class EventRow extends Pix_Table_Row
{
}

class Event extends Pix_Table
{
    public $_rowClass = 'EventRow';

	public function init()
	{
		$this->_name = 'event';
		$this->_primary = array('id');

		$this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
		$this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['start_at'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['end_at'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['title'] = array('type' => 'varchar', 'size' => 255);
		$this->_columns['note'] = array('type' => 'varchar', 'size' => 255);

        $this->addIndex('store_id', array('store_id'));
	}
}
