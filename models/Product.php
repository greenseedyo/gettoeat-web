<?php

class ProductRow extends Pix_Table_Row
{
}

class Product extends Pix_Table
{
    public $_rowClass = 'ProductRow';

	public function init()
	{
		$this->_name = 'product';
		$this->_primary = array('id');

		$this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
		$this->_columns['name'] = array('type' => 'varchar', 'size' => 20);
		$this->_columns['price'] = array('type' => 'mediumint', 'size' => 8);
		$this->_columns['category'] = array('type' => 'tinyint', 'size' => 3);
		$this->_columns['cost_percent_1'] = array('type' => 'int', 'size' => 10);
		$this->_columns['cost_percent_2'] = array('type' => 'int', 'size' => 10);
		$this->_columns['cost_percent_3'] = array('type' => 'int', 'size' => 10);
		$this->_columns['cost_percent_4'] = array('type' => 'int', 'size' => 10);
		$this->_columns['cost_percent_5'] = array('type' => 'int', 'size' => 10);
		$this->_columns['cost_percent_6'] = array('type' => 'int', 'size' => 10);
		$this->_columns['position'] = array('type' => 'tinyint', 'size' => 3);
		$this->_columns['off'] = array('type' => 'tinyint', 'size' => 1);
	}
}

