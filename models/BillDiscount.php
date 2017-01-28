<?php

class BillDiscountRow extends Pix_Table_Row
{
}

class BillDiscount extends Pix_Table
{
    public $_rowClass = 'BillDiscountRow';

	public function init()
	{
		$this->_name = 'bill_discount';
		$this->_primary = array('id');

		$this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
		$this->_columns['bill_id'] = array('type' => 'int', 'size' => 10);
		$this->_columns['value'] = array('type' => 'int', 'size' => 10);
		$this->_columns['event_id'] = array('type' => 'int', 'size' => 10);

        $this->_relations['event'] = array('rel' => 'has_one', 'type' => 'Event', 'foreign_key' => 'event_id');
	}
}

