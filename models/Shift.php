<?php

class ShiftRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->created_at = time();
    }

    public function getExpectedAmount()
    {
        return $this->open_amount + $this->sales + $this->paid_in - $this->paid_out;
    }

    public function getDifference()
    {
        return $this->close_amount - $this->getExpectedAmount();
    }

    public function getFloat()
    {
        $close_amount = $this->close_amount;
        switch ($this->adjustment_type) {
        case Shift::ADJUSTMENT_PASS:
            return $close_amount;
        case Shift::ADJUSTMENT_TAKEOUT:
            return $close_amount - $this->adjustment_amount;
        case Shift::ADJUSTMENT_ADD:
            return $close_amount + $this->adjustment_amount;
        }
    }

    public function getAdjustmentValue()
    {
        switch ($this->adjustment_type) {
        case Shift::ADJUSTMENT_PASS:
            return 0;
        case Shift::ADJUSTMENT_TAKEOUT:
            return $this->adjustment_amount;
        case Shift::ADJUSTMENT_ADD:
            return $this->adjustment_amount * (-1);
        }
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
        $this->_columns['sales'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['open_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['close_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['paid_in'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['paid_out'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['adjustment_type'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['adjustment_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['adjustment_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['closed_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('store_id', array('store_id'));
        $this->addIndex('adjustment_by', array('adjustment_by'));
        $this->addIndex('closed_by', array('closed_by'));
        $this->addIndex('created_at', array('created_at'));

        $this->_relations['store'] = array('rel' => 'has_one', 'type' => 'Store', 'foreign_key' => 'store_id');
    }

    const ADJUSTMENT_PASS = 0;
    const ADJUSTMENT_TAKEOUT = 1;
    const ADJUSTMENT_ADD = 2;
}