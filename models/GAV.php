<?php

class GAVRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->created_at = time();
        $this->updated_at = time();
    }

    public function preUpdate($columns = array())
    {
        $this->updated_at = time();
    }
}

class GAV extends Pix_Table
{
    public $_rowClass = 'GAVRow';

    public function init()
    {
        $this->_name = 'gav';
        $this->_primary = array('key');

        $this->_columns['key'] = array('type' => 'vahrchar', 'size' => 255);
        $this->_columns['value'] = array('type' => 'text');
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('created_at', array('created_at'));
        $this->addIndex('updated_at', array('updated_at'));
    }
}
