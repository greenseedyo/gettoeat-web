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
            return $this->getCategoryStatResult();
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

        $stat_result = new StatResult();
        $stat_items = array('總營收', '折扣');
        $stat_chart = $stat_result->createChart('總覽', $stat_items);

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?: $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = (new Datetime())->setTimestamp($tmp_start_at)->add($this->interval);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);
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
            $dataset = array(
                '總營收' => $price_sum,
                '折扣' => $discount_sum
            );
            $stat_chart->append($period_name, $dataset);

            $tmp_start_datetime = $tmp_end_datetime;
        }

        return $stat_result;
    }

    private function getCategoryStatResult(): StatResult
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
        $product_category_ids = $this->store->products->toArray('category_id');

        $categories = $this->store->categories;
        $category_names = $categories->toArray('name');

        $stat_result = new StatResult();
        $stat_items = $category_names;
        $turnover_chart = $stat_result->createChart('分類營收', $stat_items);
        $quantity_chart = $stat_result->createChart('分類銷售數量', $stat_items);

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?: $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = (new Datetime())->setTimestamp($tmp_start_at)->add($this->interval);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);
            $turnover_dataset = array_fill_keys($stat_items, 0);
            $quantity_dataset = array_fill_keys($stat_items, 0);
            foreach ($bill_datasets as $i => $bill_data) {
                if ($bill_data['ordered_at'] > $tmp_end_at) {
                    break;
                }
                foreach ($bill_item_datasets as $j => $item_data) {
                    if ($item_data['bill_id'] == $bill_data['id']) {
                        $category_id = $product_category_ids[$item_data['product_id']];
                        $category_name = $category_names[$category_id];
                        $turnover_dataset[$category_name] += $item_data['unit_price'] * $item_data['amount'];
                        $quantity_dataset[$category_name] += $item_data['amount'];
                        unset($bill_item_datasets[$j]);
                    }
                }
                unset($bill_datasets[$i]);
            }
            $turnover_chart->append($period_name, $turnover_dataset);
            $quantity_chart->append($period_name, $quantity_dataset);

            $tmp_start_datetime = $tmp_end_datetime;
        }

        return $stat_result;
    }
}


class StatResult
{
    private $charts = array();

    public function createChart(string $title, array $stat_items)
    {
        $stat_chart = new StatChart($title, $stat_items);
        $this->charts[] = $stat_chart;
        return $stat_chart;
    }

    public function getCharts()
    {
        return $this->charts;
    }
}


class StatChart
{
    private $title;
    private $period_names = array();
    private $datasets = array();
    private $stat_items = array();

    public function __construct(string $title, array $stat_items)
    {
        $this->title = $title;
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
        foreach ($dataset as $stat_item => $data) {
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

    public function getTitle()
    {
        return $this->title;
    }
}


class StatHelperException extends Exception
{
}
