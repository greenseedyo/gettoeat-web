<?php

class BillRow extends Pix_Table_Row
{
    public function pay($item_datas, $event_id)
    {
        if (!is_array($item_datas)) {
            return false;
        }
        $price = 0;
        foreach ($item_datas as $index => $item_data) {
            /* create bill item */
            $product = Product::find(intval($item_data['product_id']));
            $data = array('product_id' => $product->id, 'amount' => $item_data['amount']);
            $item = $this->create_items($data);
            $price += $product->price * $item_data['amount'];
            /* save product info to create discount */
            $item_data['category'] = $product->category;
            $item_data['price'] = $product->price;
            $item_datas[$index] = $item_data;
        }
        /* 折扣 */
        if (4 == $event_id) {
            $value = $price * 0.5;
            $price -= $value;
            $this->create_discounts(array('value' => $value, 'event_id' => $event_id, 'amount' => 1));
        } else {
            if ('takeout' == substr($this->table, 0, 7)) { // 外帶披蕯九折
                $pizzas_count = 0;
                $pizzas_total_price = 0;
                $non_pizza_total_price = 0;
                foreach ($item_datas as $item_data) {
                    if (2 == $item_data['category']) {
                        $pizzas_count += $item_data['amount'];
                        $pizzas_total_price += $item_data['price'] * $item_data['amount'];
                    } else {
                        $non_pizza_total_price += $item_data['price'] * $item_data['amount'];
                    }
                }
                if ($pizzas_count > 0) {
                    $value = round($pizzas_total_price * 0.1);
                    $price -= $value;
                    $this->create_discounts(array('value' => $value, 'event_id' => 1, 'amount' => 1));
                }
            }
            if (3 == $event_id) {
                if ($non_pizza_total_price) {
                    $value = $non_pizza_total_price * 0.1;
                } else {
                    $value = $price * 0.1;
                }
                $price -= $value;
                $this->create_discounts(array('value' => $value, 'event_id' => $event_id, 'amount' => 1));
            }
        }
        $this->update(array('price' => $price, 'paid_at' => time()));
    }

    public function getTableName()
    {
        switch ($this->table) {
        case 'outer_1': return '戶外1';
        case 'outer_2': return '戶外2';
        case 'outer_3': return '戶外3';
        case 'sofa_1': return '沙1';
        case 'sofa_2': return '沙2';
        case 'left_1': return '左1';
        case 'left_2': return '左2';
        case 'left_3': return '左3';
        case 'left_4': return '左4';
        case 'middle_1': return '中1';
        case 'middle_2': return '中2';
        case 'middle_3': return '中3';
        case 'right_1': return '右1';
        case 'right_2': return '右2';
        case 'bar_1': return '吧1';
        case 'bar_2': return '吧2';
        case 'bar_3': return '吧3';
        case 'bar_4': return '吧4';
        case 'bar_5': return '吧5';
        case 'takeout_1': return '外帶1';
        case 'takeout_2': return '外帶2';
        case 'takeout_3': return '外帶3';
        case 'takeout_4': return '外帶4';
        case 'takeout_5': return '外帶5';
        }
    }
}

class Bill extends Pix_Table
{
    public $_rowClass = 'BillRow';

	public function init()
	{
		$this->_name = 'bill';
		$this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
		$this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['year'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['month'] = array('type' => 'tinyint', 'unsigned' => true);
		$this->_columns['date'] = array('type' => 'tinyint', 'unsigned' => true);
		$this->_columns['day'] = array('type' => 'tinyint', 'unsigned' => true);
		$this->_columns['price'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['ordered_at'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['paid_at'] = array('type' => 'int', 'unsigned' => true);
		$this->_columns['custermers'] = array('type' => 'tinyint', 'unsigned' => true);
		$this->_columns['table'] = array('type' => 'varchar', 'size' => 10);

        $this->addIndex('store_id', array('store_id'));
        $this->addIndex('year', array('year'));
        $this->addIndex('month', array('month'));
        $this->addIndex('date', array('date'));
        $this->addIndex('day', array('day'));
        $this->addIndex('table', array('table'));

        $this->_relations['items'] = array('rel' => 'has_many', 'type' => 'BillItem', 'foreign_key' => 'bill_id');
        $this->_relations['discounts'] = array('rel' => 'has_many', 'type' => 'BillDiscount', 'foreign_key' => 'bill_id');
	}
}
