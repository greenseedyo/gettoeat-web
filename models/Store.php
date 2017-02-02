<?php

class StoreRow extends Pix_Table_Row
{
    public function getTodayPaidBills()
    {
        if (date('H') > 6) {
            $start_at = mktime(6, 0, 0, date('m'), date('d'), date('Y'));
            $end_at = mktime(6, 0, 0, date('m'), date('d') + 1, date('Y'));
        } else {
            $start_at = mktime(6, 0, 0, date('m'), date('d') - 1, date('Y'));
            $end_at = mktime(6, 0, 0, date('m'), date('d'), date('Y'));
        }
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

        $this->addIndex('account', array('account'));
        $this->addIndex('name', array('name'));

        $this->_relations['categories'] = array('rel' => 'has_many', 'type' => 'Category', 'foreign_key' => 'store_id');
        $this->_relations['products'] = array('rel' => 'has_many', 'type' => 'Product', 'foreign_key' => 'store_id');
        $this->_relations['bills'] = array('rel' => 'has_many', 'type' => 'Bill', 'foreign_key' => 'store_id');
        $this->_relations['events'] = array('rel' => 'has_many', 'type' => 'Event', 'foreign_key' => 'store_id');
	}

    public static function getByAccount($account)
    {
        return self::search(array('account' => $account))->first();
    }
}
