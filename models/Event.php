<?php

class EventRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->created_at = time();
        $this->updated_at = time();
    }

    public function preUpdate($changed_fields = array())
    {
        $this->updated_at = time();
    }

    public function getHelper()
    {
        $class = sprintf('\%s\%s', 'Event\Helper', $this->type->name);
        return new $class($this);
    }
}

class Event extends Pix_Table
{
    public $_rowClass = 'EventRow';

    public function init()
    {
        $this->_name = 'event';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['type_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['start_at'] = array('type' => 'int', 'unsigned' => true, 'default' => 0);
        $this->_columns['end_at'] = array('type' => 'int', 'unsigned' => true, 'default' => 0);
        $this->_columns['title'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['note'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['data'] = array('type' => 'text', 'not-null' => false);

        $this->addIndex('store_id', array('store_id'));
        $this->addIndex('type_id', array('type_id'));
        $this->addIndex('created_at', array('created_at'));
        $this->addIndex('updated_at', array('updated_at'));
        $this->addIndex('start_at', array('start_at'));
        $this->addIndex('end_at', array('end_at'));

        $this->_relations['type'] = array('rel' => 'has_one', 'type' => 'EventType', 'foreign_key' => 'type_id');
        $this->_relations['store'] = array('rel' => 'has_one', 'type' => 'Store', 'foreign_key' => 'store_id');
    }
}
