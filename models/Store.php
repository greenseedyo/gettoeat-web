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

    public function getBusinessDateByDatetime(DateTime $datetime, $format = 'Y-m-d')
    {
        $start_at = $this->getDayStartAt($datetime);
        return date($format, $start_at);
    }

    public function getDateChangeAt()
    {
        return $this->date_change_at;
    }

    public function getDayStartAt(DateTime $datetime)
    {
        $shift_secs = $this->getDateChangeAt() * 60 * 60;
        $shifted_timestamp = $datetime->getTimestamp() - $shift_secs;
        $shifted_datetime = new Datetime(date('c', $shifted_timestamp));
        $start_date = $shifted_datetime->format('Y-m-d');
        $start_at = strtotime($start_date) + $shift_secs;
        return $start_at;
    }

    public function getDayEndAt(DateTime $datetime)
    {
        return $this->getDayStartAt($datetime) + 86400;
    }

    public function getTodayPaidBills(DateTime $datetime = null)
    {
        if (!$datetime) {
            $datetime = new Datetime('now');
        }
        $date_change_at = $this->getDateChangeAt();
        $start_at = $this->getDayStartAt($datetime);
        $end_at = $this->getDayEndAt($datetime);
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
        $default_data = '{"totalHeight":400,"totalWidth":600,"tables":[{"name":"2桌","width":80,"height":80,"x":"320px","y":"140px","active":true},{"name":"1桌","width":80,"height":80,"x":"200px","y":"140px","active":true},{"name":"外帶2","width":80,"height":40,"x":"260px","y":"340px","active":true},{"name":"外帶1","width":80,"height":40,"x":"160px","y":"340px","active":true},{"name":"老闆","width":80,"height":40,"x":"360px","y":"340px","active":true},{"name":"請至後台設定桌位圖","width":200,"height":60,"x":"200px","y":"40px","active":true}]}';
        $tables_info = $this->create_tables_infos(array('version' => $version, 'data' => $default_data));
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

    public function getCurrentStaffs()
    {
        return $this->staffs->search(array('off' => 0));
    }

    public function getStaffGroupById($group_id)
    {
        $staff_group = $this->staff_groups->search(array('id' => $group_id))->first();
        return $staff_group;
    }

    public function getStaffById($staff_id)
    {
        $staff = $this->staffs->search(array('id' => $staff_id))->first();
        return $staff;
    }

    public function getLineBotChat(string $source_type, string $source_id)
    {
        return $this->line_bot_chats->search(array('source_type' => $source_type, 'source_id' => $source_id))->first();
    }

    public function getValidLineBotChats()
    {
        return $this->line_bot_chats->search('joined_at > 0 AND left_at = 0');
    }

    public function getPaymentMethodKeys(): array
    {
        if (!$this->payment_method_keys) {
            $this->setPaymentMethodKeys(array(Store::PAYMENT_METHOD_CASH));
        }
        $payment_method_keys = explode(Store::PAYMENT_METHODS_DELIMITER, $this->payment_method_keys);
        return $payment_method_keys;
    }

    public function setPaymentMethodKeys(array $payment_method_keys)
    {
        $new_string = implode(Store::PAYMENT_METHODS_DELIMITER, $payment_method_keys);
        if ($new_string !== $this->payment_method_keys) {
            $this->update(array('payment_method_keys' => $new_string));
        }
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
        $this->_columns['date_change_at'] = array('type' => 'int', 'size' => 10, 'default' => 6);
        $this->_columns['payment_method_keys'] = array('type' => 'varchar', 'size' => 255, 'default' => '1');

        $this->addIndex('account', array('account'));
        $this->addIndex('name', array('name'));

        $this->_relations['categories'] = array('rel' => 'has_many', 'type' => 'Category', 'foreign_key' => 'store_id');
        $this->_relations['products'] = array('rel' => 'has_many', 'type' => 'Product', 'foreign_key' => 'store_id');
        $this->_relations['bills'] = array('rel' => 'has_many', 'type' => 'Bill', 'foreign_key' => 'store_id');
        $this->_relations['events'] = array('rel' => 'has_many', 'type' => 'Event', 'foreign_key' => 'store_id');
        $this->_relations['shifts'] = array('rel' => 'has_many', 'type' => 'Shift', 'foreign_key' => 'store_id');
        $this->_relations['staffs'] = array('rel' => 'has_many', 'type' => 'Staff', 'foreign_key' => 'store_id');
        $this->_relations['staff_groups'] = array('rel' => 'has_many', 'type' => 'StaffGroup', 'foreign_key' => 'store_id');
        $this->_relations['tables_infos'] = array('rel' => 'has_many', 'type' => 'TablesInfo', 'foreign_key' => 'store_id');
        $this->_relations['line_bot_chats'] = array('rel' => 'has_many', 'type' => 'LineBotChat', 'foreign_key' => 'store_id');
    }

    const PAYMENT_METHOD_CASH = 1;
    const PAYMENT_METHOD_CARD = 2;
    const PAYMENT_METHOD_JKOPAY = 3;
    const PAYMENT_METHOD_UBEREATS = 4;
    const PAYMENT_METHOD_LINE_PAY = 5;

    const PAYMENT_METHODS_DELIMITER = ',';

    public static function getByAccount(string $account)
    {
        return self::search(array('account' => $account))->first();
    }
}
