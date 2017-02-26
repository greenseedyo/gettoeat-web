<?php

class BillRow extends Pix_Table_Row
{
}

class Bill extends Pix_Table
{
    public $_rowClass = 'BillRow';

    public function init()
    {
        $this->_name = 'bill';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['year'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['month'] = array('type' => 'tinyint', 'unsigned' => true);
        $this->_columns['date'] = array('type' => 'tinyint', 'unsigned' => true);
        $this->_columns['day'] = array('type' => 'tinyint', 'unsigned' => true);
        $this->_columns['price'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['ordered_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['paid_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['custermers'] = array('type' => 'tinyint', 'unsigned' => true);
        $this->_columns['table'] = array('type' => 'varchar', 'size' => 10);

        $this->addIndex('store_id', array('store_id'));
        $this->addIndex('year', array('year'));
        $this->addIndex('month', array('month'));
        $this->addIndex('date', array('date'));
        $this->addIndex('day', array('day'));
        $this->addIndex('table', array('table'));

        $this->_relations['items'] = array('rel' => 'has_many', 'type' => 'BillItem', 'foreign_key' => 'bill_id');
        $this->_relations['discounts'] = array('rel' => 'has_many', 'type' => 'BillDiscount', 'foreign_key' => 'bill_id');
    }
}
