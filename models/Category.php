<?php

class CategoryRow extends Pix_Table_Row
{
}

class Category extends Pix_Table
{
    public $_rowClass = 'CategoryRow';

	public function init()
	{
		$this->_name = 'category';
		$this->_primary = array('id');

		$this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
		$this->_columns['name'] = array('type' => 'varchar', 'size' => 10);
	}
}
