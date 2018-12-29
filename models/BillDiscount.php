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

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['bill_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['value'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['event_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['title'] = array('type' => 'varchar', 'size' => 255);

        $this->addIndex('bill_id', array('bill_id'));
        $this->addIndex('event_id', array('event_id'));

        $this->_relations['event'] = array('rel' => 'has_one', 'type' => 'Event', 'foreign_key' => 'event_id');
        $this->_relations['bill'] = array('rel' => 'has_one', 'type' => 'Bill', 'foreign_key' => 'bill_id');
    }
}

