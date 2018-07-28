<?php

class ShiftRow extends Pix_Table_Row
{
    public function getCashSales()
    {
    }

    public function getPreviousFloat()
    {
    }

    public function getExpectedAmount()
    {
    }

    public function getDifference()
    {
    }
}

class Shift extends Pix_Table
{
    public $_rowClass = 'ShiftRow';

    public function init()
    {
        $this->_name = 'shift';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['open_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['close_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['paid_in'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['paid_out'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['adjustment_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['adjustment_type'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['adjustment_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['closed_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('store_id', array('store_id'));
        $this->addIndex('adjustment_by', array('adjustment_by'));
        $this->addIndex('closed_by', array('closed_by'));
        $this->addIndex('created_at', array('created_at'));
    }

    const ADJUSTMENT_PASS = 0;
    const ADJUSTMENT_TAKEOUT = 1;
    const ADJUSTMENT_ADD = 2;
}
