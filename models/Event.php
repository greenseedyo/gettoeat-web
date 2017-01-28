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

		$this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
		$this->_columns['title'] = array('type' => 'varchar', 'size' => 255);
		$this->_columns['note'] = array('type' => 'varchar', 'size' => 255);
	}
}


