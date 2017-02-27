<?php

class CategoryRow extends Pix_Table_Row
{
    public function getCurrentProducts()
    {
        return $this->products->search(array('off' => 0));
    }
}

class Category extends Pix_Table
{
    public $_rowClass = 'CategoryRow';

    public function init()
    {
        $this->_name = 'category';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 10);

        $this->addIndex('store_id', array('store_id'));

        $this->_relations['products'] = array('rel' => 'has_many', 'type' => 'Product', 'foreign_key' => 'category_id');
    }
}
