<?php

class StoreRow extends Pix_Table_Row
{
    public function postInsert()
    {
        $this->createDefaultEvents();
    }

    public function createDefaultEvents()
    {
        // 自行輸入折扣金額
        $event_type = EventType::getByName('PriceOff');
        $data = array(
            'type_id' => $event_type->id,
            'title' => '自行輸入折扣金額',
            'note' => '系統預設折扣活動',
        );
        $this->create_events($data);

        // 9折
        $event_type = EventType::getByName('PercentOff');
        $data = array(
            'type_id' => $event_type->id,
            'title' => '9折',
            'note' => '系統預設折扣活動',
            'data' => json_encode(array('percent' => 10, 'percent_reversed' => 90)),
        );
        $this->create_events($data);
    }

    public function getDateChangeAt()
    {
        return $this->date_change_at;
    }

    public function getDayStartAt(DateTime $date)
    {
        $date_change_at = $this->getDateChangeAt();
        $start_at = mktime($date_change_at, 0, 0, date('m', $date->getTimestamp()), date('d', $date->getTimestamp()), date('Y', $date->getTimestamp()));
        return $start_at;
    }

    public function getDayEndAt(DateTime $date)
    {
        $date_change_at = $this->getDateChangeAt();
        $end_at = mktime($date_change_at, 0, 0, date('m', $date->getTimestamp()), date('d', $date->getTimestamp()) + 1, date('Y', $date->getTimestamp()));
        return $end_at;
    }

    public function getTodayPaidBills()
    {
        $date_change_at = $this->getDateChangeAt();
        if (date('H') > $date_change_at) {
            $date = new DateTime();
        } else {
            $date = new DateTime('-1 day');
        }
        $start_at = $this->getDayStartAt($date);
        $end_at = $this->getDayEndAt($date);
        return $this->bills->search("paid_at > 0 AND ordered_at >= {$start_at} AND ordered_at < {$end_at}");
    }

    public function getCategoryById($category_id)
    {
        $category = $this->categories->search(array('id' => $category_id))->first();
        return $category;
    }

    public function getProductById($product_id)
    {
        $product = $this->products->search(array('id' => $product_id))->first();
        return $product;
    }

    public function getBillById($bill_id)
    {
        $bill = $this->bills->search(array('id' => $bill_id))->first();
        return $bill;
    }

    public function getTablesInfo()
    {
        if (!$tables_info = $this->tables_infos->order('`version` DESC')->first()) {
            $tables_info = $this->createTablesInfoWithVersion(1);
        }
        return $tables_info;
    }

    public function createTablesInfoWithVersion($version)
    {
        $tables_info = $this->create_tables_infos(array('version' => $version));
        return $tables_info;
    }

    public function getCurrentEvents()
    {
        $now = time();
        $events = $this->events->search("`start_at` <= {$now} AND (`end_at` = 0 OR `end_at` > {$now})");
        return $events;
    }

    public function getCashier()
    {
        $cashier = new Store\Cashier($this);
        return $cashier;
    }

    public function getEventById($event_id)
    {
        $event = $this->events->search(array('id' => $event_id))->first();
        return $event;
    }

    public function getOnlineCategories()
    {
        return $this->categories->search(array('off' => 0));
    }
}

class Store extends Pix_Table
{
    public $_rowClass = 'StoreRow';

    public function init()
    {
        $this->_name = 'store';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['account'] = array('type' => 'varchar', 'size' => 20);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 20);
        $this->_columns['nickname'] = array('type' => 'varchar', 'size' => 10);
        $this->_columns['date_change_at'] = array('type' => 'int', 'size' => 10);

        $this->addIndex('account', array('account'));
        $this->addIndex('name', array('name'));

        $this->_relations['categories'] = array('rel' => 'has_many', 'type' => 'Category', 'foreign_key' => 'store_id');
        $this->_relations['products'] = array('rel' => 'has_many', 'type' => 'Product', 'foreign_key' => 'store_id');
        $this->_relations['bills'] = array('rel' => 'has_many', 'type' => 'Bill', 'foreign_key' => 'store_id');
        $this->_relations['events'] = array('rel' => 'has_many', 'type' => 'Event', 'foreign_key' => 'store_id');
        $this->_relations['tables_infos'] = array('rel' => 'has_many', 'type' => 'TablesInfo', 'foreign_key' => 'store_id');
    }

    public static function getByAccount($account)
    {
        return self::search(array('account' => $account))->first();
    }
}
