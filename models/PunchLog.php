<?php
require_once(ROOT_DIR . '/helpers/ip.php');

class PunchLogRow extends Pix_Table_Row
{
    public function preInsert()
    {
        $this->created_at = $this->created_at ?: time();
        $this->updated_at = $this->updated_at ?: time();
        $ip_helper = new Helpers\IpHelper();
        $client_ip = $ip_helper->getClientLongIp();
        $this->created_from = $client_ip;
        $this->updated_from = $client_ip;
    }

    public function preUpdate($changed_fields = null)
    {
        $this->updated_at = $this->updated_at ?: time();
        $ip_helper = new Helpers\IpHelper();
        $client_ip = $ip_helper->getClientLongIp();
        $this->updated_from = $client_ip;
    }
}

class PunchLog extends Pix_Table
{
    public $_rowClass = 'PunchLogRow';

    public function init()
    {
        $this->_name = 'punch_log';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['staff_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['type'] = array('type' => 'tinyint', 'unsigned' => true);
        $this->_columns['timestamp'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_from'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_from'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('staff_id', array('staff_id'));
        $this->addIndex('timestamp', array('timestamp'));
    }

    const TYPE_IN = 1;
    const TYPE_OUT = 2;
}


// FIXME: 找個地方放 exceptions
class PunchDuplicatedException extends Exception
{
}
