<?php

class BillPaymentRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->created_at = $this->created_at ?: time();
        $this->updated_at = $this->updated_at ?: time();
    }

    public function preUpdate($changed_fields = array())
    {
        $this->updated_at = time();
    }
}

class BillPayment extends Pix_Table
{
    public $_rowClass = 'BillPaymentRow';

    public function init()
    {
        $this->_name = 'bill_payment';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['bill_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['payment_method'] = array('type' => 'tinyint', 'unsigned' => true);
        $this->_columns['amount'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('bill_id', array('bill_id'));
        $this->addIndex('payment_method', array('payment_method'));
        $this->addIndex('created_at', array('created_at'));
        $this->addIndex('updated_at', array('updated_at'));
    }
} 
