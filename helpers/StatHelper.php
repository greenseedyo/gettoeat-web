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
        case 'customers':
            return $this->getCustomersStatResult();
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

        $stat_result = new StatResult();
        $stat_items = array('總營收(折扣前)', '總營收(折扣後)', '折扣');
        $stat_chart = $stat_result->createChart('總覽', $stat_items);

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?: $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = (new Datetime())->setTimestamp($tmp_start_at)->add($this->interval);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);
            $price_sum = 0;
            $discount_sum = 0;

            $bills = $this->store->bills->search("`ordered_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}");
            $real_price_sum = $bills->sum('price');
            $bill_ids = $bills->toArray('id');
            $bill_items = BillItem::search(1)->searchIn('bill_id', $bill_ids);
            $bill_discounts = BillDiscount::search(1)->searchIn('bill_id', $bill_ids);

            foreach ($bill_items as $bill_item) {
                $price_sum += $bill_item->getTotalPrice();
            }
            foreach ($bill_discounts as $bill_discount) {
                $discount_sum += $bill_discount->value;
            }

            $dataset = array(
                '總營收(不含折扣)' => $price_sum,
                '總營收(含折扣)' => $real_price_sum,
                '折扣' => $discount_sum
            );
            $stat_chart->append($period_name, $dataset);

            $tmp_start_datetime = $tmp_end_datetime;
        }

        return $stat_result;
    }

    private function getCustomersStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

        $stat_result = new StatResult();
        $count_stat_chart = $stat_result->createChart('來客數統計', array('總來客數'));
        $avg_stat_chart = $stat_result->createChart('平均客單價', array('平均客單價'));

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?: $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = (new Datetime())->setTimestamp($tmp_start_at)->add($this->interval);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);

            $bills = $this->store->bills->search("`ordered_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}");
            $customers_sum = $bills->sum('custermers');
            $price_sum = $bills->sum('price');
            $avg_price = 0;
            if ($customers_sum > 0) {
                $avg_price = round($price_sum / $customers_sum, 2);
            }

            $dataset = array('總來客數' => $customers_sum);
            $count_stat_chart->append($period_name, $dataset);
            $dataset = array('平均客單價' => $avg_price);
            $avg_stat_chart->append($period_name, $dataset);

            $tmp_start_datetime = $tmp_end_datetime;
        }

        return $stat_result;
    }

    private function getCategoryStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

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

            $bill_ids = $this->store->bills->search("`ordered_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}")->toArray('id');
            $bill_items = BillItem::search(1)->searchIn('bill_id', $bill_ids);
            $product_category_ids = $this->store->products->toArray('category_id');

            foreach ($bill_items as $key => $bill_item) {
                $category_id = $product_category_ids[$bill_item->product_id];
                $category_name = $category_names[$category_id];
                $turnover_dataset[$category_name] += $bill_item->getTotalPrice();
                $quantity_dataset[$category_name] += $bill_item->amount;
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
