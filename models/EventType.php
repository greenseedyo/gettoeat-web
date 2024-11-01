<?php

class EventTypeRow extends Pix_Table_Row
{
}

class EventType extends Pix_Table
{
    public $_rowClass = 'EventTypeRow';

    public function init()
    {
        $this->_name = 'event_type';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['description'] = array('type' => 'varchar', 'size' => 255);

        $this->addIndex('name', array('name'), 'unique');
    }

    public static function getByName($name)
    {
        return EventType::search(array('name' => $name))->first();
    }
}
