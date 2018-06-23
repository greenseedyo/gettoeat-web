<?php

class StatHelper
{
    public function __construct(StoreRow $store)
    {
        $this->store = $store;
    }

    public function setStartDatetime(Datetime $start_datetime)
    {
        $this->start_datetime = $start_datetime;
    }

    public function setEndDatetime(Datetime $end_datetime)
    {
        $this->end_datetime = $end_datetime;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    public function setInterval(DateInterval $interval)
    {
        $this->interval = $interval;
    }

    public function getStatResult(): StatResult
    {
        switch ($this->topic) {
        case 'category':
        case 'overview':
        default:
            return $this->getOverviewStatResult();
        }
    }

    private function getOverviewStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

        $start_at = $this->start_datetime->getTimestamp();
        $end_at = $this->end_datetime->getTimestamp();
        $bills = $this->store->bills->search("`ordered_at` BETWEEN {$start_at} AND {$end_at}")->order('ordered_at ASC');
        $bill_datasets = $bills->toArray();
        $bill_ids = array_keys($bill_datasets);
        $bill_item_datasets = BillItem::search(1)->searchIn('bill_id', $bill_ids)->toArray();
        $bill_discount_datasets = BillDiscount::search(1)->searchIn('bill_id', $bill_ids)->toArray();

        $stat_result = new StatResult('總營收', '折扣');

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?: $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = (new Datetime())->setTimestamp($tmp_start_at)->add($this->interval);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d H:i', $tmp_start_at);
            $price_sum = 0;
            $discount_sum = 0;
            foreach ($bill_datasets as $i => $bill_data) {
                if ($bill_data['ordered_at'] > $tmp_end_at) {
                    break;
                }
                foreach ($bill_item_datasets as $j => $item_data) {
                    if ($item_data['bill_id'] == $bill_data['id']) {
                        $price_sum += $item_data['unit_price'] * $item_data['amount'];
                        unset($bill_item_datasets[$j]);
                    }
                }
                foreach ($bill_discount_datasets as $j => $discount_data) {
                    if ($discount_data['bill_id'] == $bill_data['id']) {
                        $discount_sum += $discount_data['value'];
                        unset($bill_discount_datasets[$j]);
                    }
                }
                unset($bill_datasets[$i]);
            }
            $dataset = array($price_sum, $discount_sum);
            $stat_result->append($period_name, $dataset);

            $tmp_start_datetime = $tmp_end_datetime;
        }

        return $stat_result;
    }

    private function getCategorySum()
    {
        $bills = $this->store->bills->search("`ordered_at` BETWEEN {$this->start_at} AND {$this->end_at}");
        $bill_ids = $bills->toArray('id');
        $bill_items = BillItem::search(1)->searchIn('bill_id', $bill_ids);
        $product_datasets = $this->store->products->toArray(array('id', 'category_id'));

        $categories = $store->categories;
        $category_datasets = array();
        foreach ($categories as $category) {
            $dataset = array(
                'id' => $category->id,
                'name' => $category->name,
                'off' => $category->off,
                'sum' => $sum,
            );
            $category_datasets[$category->id] = $dataset;
        }

        foreach ($bill_items as $bill_item) {
            $category_id = $product_datasets[$bill_item->product_id]['category_id'];
            $category_datasets[$category_id]['sum'] += $bill_item->getTotalPrice();
        }

        return $category_datasets;
    }
}


class StatResult
{
    private $period_names = array();
    private $datasets = array();
    private $stat_items = array();

    public function __construct(string ...$stat_items)
    {
        $this->stat_items = $stat_items;
        foreach ($stat_items as $stat_item) {
            $this->datasets[$stat_item] = array();
        }
    }

    public function append($period_name, $dataset)
    {
        if (count($dataset) != count($this->stat_items)) {
            throw new StatHelperException('the quantity of dataset not matches stat items');
        }
        $this->period_names[] = $period_name;
        foreach ($dataset as $index => $data) {
            $stat_item = $this->stat_items[$index];
            $this->datasets[$stat_item][] = $data;
        }
    }

    public function getPeriodNames()
    {
        return $this->period_names;
    }

    public function getDataSets()
    {
        return $this->datasets;
    }
}


class StatHelperException extends Exception
{
}
