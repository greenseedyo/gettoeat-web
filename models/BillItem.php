<?php

class BillItemRow extends Pix_Table_Row
{
    public function getCategory()
    {
        return $this->product->category;
    }

    public function getPrice()
    {
        return $this->unit_price;
    }

    public function getTotalPrice()
    {
        return $this->unit_price * $this->amount;
    }
}

class BillItem extends Pix_Table
{
    public $_rowClass = 'BillItemRow';

    public function init()
    {
        $this->_name = 'bill_item';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['bill_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['product_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['unit_price'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['amount'] = array('type' => 'tinyint', 'unsigned' => true); // FIXME: rename to quantity

        $this->addIndex('bill_id', array('bill_id', 'product_id'));

        $this->_relations['product'] = array('rel' => 'has_one', 'type' => 'Product', 'foreign_key' => 'product_id');
    }
}

