<?php

class ProductRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->store_id = $this->category->store_id;
    }
}

class Product extends Pix_Table
{
    public $_rowClass = 'ProductRow';

    public function init()
    {
        $this->_name = 'product';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['category_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 20);
        $this->_columns['price'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['position'] = array('type' => 'tinyint', 'unsigned' => true);
        $this->_columns['off'] = array('type' => 'tinyint', 'unsigned' => true);

        $this->addIndex('store_id', array('store_id'));
        $this->addIndex('category_id', array('category_id', 'position'));

        $this->_relations['category'] = array('rel' => 'has_one', 'type' => 'Category', 'foreign_key' => 'category_id');
    }
}
