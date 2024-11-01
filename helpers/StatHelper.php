<?php
namespace Helpers;
require_once(ROOT_DIR . '/helpers/payment_methods.php');

use StoreRow;
use Datetime;
use DateInterval;
use BillDiscount;
use BillItem;
use BillPayment;
use Product;
use Exception;

class StatHelper
{
    protected $filters = array();

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

    public function setFilter($name, $value)
    {
        $this->filters[$name] = $value;
    }

    public function getStatResult(): StatResult
    {
        switch ($this->topic) {
        case 'shift':
            return $this->getShiftStatResult();
        case 'product':
            return $this->getProductStatResult();
        case 'category':
            return $this->getCategoryStatResult();
        case 'customers':
            return $this->getCustomersStatResult();
        case 'event':
            return $this->getEventStatResult();
        case 'payment_method':
            return $this->getPaymentMethodStatResult();
        case 'overview':
        default:
            return $this->getOverviewStatResult();
        }
    }

    private function calculateEndDatetime(Datetime $start_datetime): Datetime
    {
        $start_at = $start_datetime->getTimestamp();
        if ($this->interval->days) {
            $datetime = (new Datetime())->setTimestamp($start_at)->modify("+ {$this->interval->days} days");
        } else {
            $datetime = (new Datetime())->setTimestamp($start_at)->add($this->interval);
        }
        return $datetime;
    }

    private function getOverviewStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

        $stat_result = new StatResult();
        $stat_items = array('總營收', '折扣');
        $stat_chart = $stat_result->createChart('總覽', $stat_items);

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?? $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = $this->calculateEndDatetime($tmp_start_datetime);
            if ($tmp_end_datetime > $this->end_datetime) {
                $tmp_end_datetime = $this->end_datetime;
            }
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);
            $discount_sum = 0;

            $bills = $this->store->bills->search("`ordered_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}");
            $price_sum = $bills->sum('price');
            $bill_ids = $bills->toArray('id');
            $bill_discounts = BillDiscount::search(1)->searchIn('bill_id', $bill_ids);

            foreach ($bill_discounts as $bill_discount) {
                $discount_sum += $bill_discount->value;
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

    private function getCustomersStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

        $stat_result = new StatResult();
        $count_stat_chart = $stat_result->createChart('來客數統計', array('總來客數', '結帳次數'));
        $avg_stat_chart = $stat_result->createChart('平均客單價', array('平均客單價'));

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?? $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = $this->calculateEndDatetime($tmp_start_datetime);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);

            $bills = $this->store->bills->search("`ordered_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}");
            $bills_count = intval($bills->count());
            $customers_sum = $bills->sum('custermers');
            $price_sum = $bills->sum('price');
            $avg_price = 0;
            if ($customers_sum > 0) {
                $avg_price = round($price_sum / $customers_sum, 2);
            }

            $dataset = array('總來客數' => $customers_sum, '結帳次數' => $bills_count);
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
        $product_category_ids = $this->store->products->toArray('category_id');

        $stat_result = new StatResult();
        $stat_items = $category_names;
        $turnover_chart = $stat_result->createChart('分類營收', $stat_items);
        $quantity_chart = $stat_result->createChart('分類銷售數量', $stat_items);

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?? $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = $this->calculateEndDatetime($tmp_start_datetime);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);
            $turnover_dataset = array_fill_keys($stat_items, 0);
            $quantity_dataset = array_fill_keys($stat_items, 0);

            $bill_ids = $this->store->bills->search("`ordered_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}")->toArray('id');
            $bill_items = BillItem::search(1)->searchIn('bill_id', $bill_ids);

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

    private function getProductStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

        $products = $this->store->products;
        $product_names = $products->toArray('name');

        $stat_result = new StatResult();
        $chart = $stat_result->createChart('商品營收及數量', array('金額', '數量'));

        $start_at = $this->start_datetime->getTimestamp();
        $end_at = $this->end_datetime->getTimestamp();
        $period_name = sprintf('%s - %s', date('Y-m-d(D) H:i', $start_at), date('Y-m-d(D) H:i', $end_at));

        $bills = $this->store->bills->search("`ordered_at` BETWEEN {$start_at} AND {$end_at}");

        if ($custermers = $this->filters['custermers']) {
            $bills = $bills->search(array('custermers' => $custermers));
        }

        $bill_ids = $bills->toArray('id');
        $bill_items = BillItem::search(1)->searchIn('bill_id', $bill_ids);

        if ($category_id = $this->filters['category_id']) {
            $product_ids = Product::search(array('category_id' => $category_id))->toArray('id');
            $bill_items = $bill_items->searchIn('product_id', $product_ids);
        }

        $turnover_dataset = array();
        $quantity_dataset = array();
        foreach ($bill_items as $key => $bill_item) {
            $product_name = $product_names[$bill_item->product_id];
	    $turnover_dataset[$product_name] = $turnover_dataset[$product_name] ?? 0;
            $turnover_dataset[$product_name] += $bill_item->getTotalPrice();
	    $quantity_dataset[$product_name] = $quantity_dataset[$product_name] ?? 0;
            $quantity_dataset[$product_name] += $bill_item->amount;
        }
        arsort($turnover_dataset);
        foreach ($turnover_dataset as $product_name => $total_price) {
            $total_quantity = $quantity_dataset[$product_name] ?? 0;
            $dataset = array('金額' => $total_price, '數量' => $total_quantity);
            $chart->append($product_name, $dataset);
        }

        return $stat_result;
    }

    private function getShiftStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

        $staffs = $this->store->staffs;
        $staff_names = $staffs->toArray('name');

        $stat_result = new StatResult();
        // $staff_chart 是加總圖表不分周期
        $staff_chart = $stat_result->createChart('收款人結餘金額及處理次數', array('金額', '次數'));
        $staff_adjustment_dataset = array();
        $staff_quantity_dataset = array();
        // shift_chart 要分周期
        $shift_stat_items = array('結餘金額', '臨時支出', '臨時收入', '短溢');
        $shift_chart = $stat_result->createChart('關帳統計', $shift_stat_items);

        $start_at = $this->start_datetime->getTimestamp();
        $end_at = $this->end_datetime->getTimestamp();

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?? $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = $this->calculateEndDatetime($tmp_start_datetime);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);

            $shift_dataset = array_fill_keys($shift_stat_items, 0);

            $shifts = $this->store->shifts->search("`created_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}");
            foreach ($shifts as $shift) {
                $staff_name = $staff_names[$shift->adjustment_by] ?? '老闆';
		$staff_adjustment_dataset[$staff_name] = $staff_adjustment_dataset[$staff_name] ?? 0;
                $staff_adjustment_dataset[$staff_name] += $shift->getAdjustmentValue();
		$staff_quantity_dataset[$staff_name] = $staff_quantity_dataset[$staff_name] ?? 0;
                $staff_quantity_dataset[$staff_name] += 1;
                $shift_dataset['結餘金額'] += $shift->getAdjustmentValue();
                $shift_dataset['臨時支出'] += $shift->paid_out;
                $shift_dataset['臨時收入'] += $shift->paid_in;
                $shift_dataset['短溢'] += $shift->getDifference();
            }
            $shift_chart->append($period_name, $shift_dataset);

            $tmp_start_datetime = $tmp_end_datetime;
        }
        arsort($staff_adjustment_dataset);
        foreach ($staff_adjustment_dataset as $staff_name => $total_price) {
            $total_quantity = $staff_quantity_dataset[$staff_name] ?? 0;
            $dataset = array('金額' => $total_price, '次數' => $total_quantity);
            $staff_chart->append($staff_name, $dataset);
        }

        return $stat_result;
    }

    private function getEventStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

        $events = $this->store->events;
        $event_titles = $events->toArray('title');

        $stat_result = new StatResult();
        $stat_items = $event_titles;
        $value_chart = $stat_result->createChart('折扣活動金額統計', $stat_items);
        $quantity_chart = $stat_result->createChart('折扣活動次數統計', $stat_items);

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?? $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = $this->calculateEndDatetime($tmp_start_datetime);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);
            $value_dataset = array_fill_keys($stat_items, 0);
            $quantity_dataset = array_fill_keys($stat_items, 0);

            $bill_ids = $this->store->bills->search("`ordered_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}")->toArray('id');
            $bill_discounts = BillDiscount::search(1)->searchIn('bill_id', $bill_ids);

            foreach ($bill_discounts as $key => $bill_discount) {
                $event_title = $event_titles[$bill_discount->event_id];
                $value_dataset[$event_title] += $bill_discount->value;
                $quantity_dataset[$event_title] += 1;
            }
            $value_chart->append($period_name, $value_dataset);
            $quantity_chart->append($period_name, $quantity_dataset);

            $tmp_start_datetime = $tmp_end_datetime;
        }

        return $stat_result;
    }

    private function getPaymentMethodStatResult(): StatResult
    {
        if (!isset($this->interval)) {
            throw new StatHelperException('interval not set');
        }

        $payment_method_texts = PaymentMethodFactory::getAllTexts('tw');

        $stat_result = new StatResult();
        $stat_items = $payment_method_texts;
        $value_chart = $stat_result->createChart('付款方式金額統計', $stat_items);
        $quantity_chart = $stat_result->createChart('付款方式次數統計', $stat_items);

        while (!isset($tmp_end_datetime) or $tmp_end_datetime < $this->end_datetime) {
            $tmp_start_datetime = $tmp_start_datetime ?? $this->start_datetime;
            $tmp_start_at = $tmp_start_datetime->getTimestamp();
            $tmp_end_datetime = $this->calculateEndDatetime($tmp_start_datetime);
            $tmp_end_at = $tmp_end_datetime->getTimestamp();
            $period_name = date('Y-m-d(D) H:i', $tmp_start_at);
            $value_dataset = array_fill_keys($stat_items, 0);
            $quantity_dataset = array_fill_keys($stat_items, 0);

            $bill_ids = $this->store->bills->search("`ordered_at` BETWEEN {$tmp_start_at} AND {$tmp_end_at}")->toArray('id');
            $bill_payments = BillPayment::search(1)->searchIn('bill_id', $bill_ids);

            foreach ($bill_payments as $key => $bill_payment) {
                $payment_method_text = $payment_method_texts[$bill_payment->payment_method];
                $value_dataset[$payment_method_text] += $bill_payment->amount;
                $quantity_dataset[$payment_method_text] += 1;
            }
            $value_chart->append($period_name, $value_dataset);
            $quantity_chart->append($period_name, $quantity_dataset);

            $tmp_start_datetime = $tmp_end_datetime;
        }
        $value_chart->removeEmptyDatasets();
        $quantity_chart->removeEmptyDatasets();

        return $stat_result;
    }
}


class StatResult
{
    private $charts = array();

    public function createChart(string $title, array $stat_items)
    {
        $stat_chart = new StatChart($title, $stat_items);
        $this->charts[$title] = $stat_chart;
        return $stat_chart;
    }

    public function getChart($name)
    {
        return $this->charts[$name];
    }

    public function getCharts()
    {
        return $this->charts;
    }
}


class StatChart
{
    private $title;
    private $y_axis_title;
    private $period_names = array();
    private $datasets = array();
    private $stat_items = array();
    private $x_axis_categories = array();

    public function __construct(string $title, array $stat_items)
    {
        $this->title = $title;
        $this->stat_items = $stat_items;
        foreach ($stat_items as $stat_item) {
            $this->datasets[$stat_item] = array();
        }
    }

    public function removeEmptyDatasets()
    {
        foreach ($this->datasets as $key => $dataset) {
            $is_empty = true;
            foreach ($dataset as $data) {
                if ($data != 0) {
                    $is_empty = false;
                    break;
                }
            }
            if ($is_empty) {
                unset($this->datasets[$key]);
            }
        }
    }

    public function append($x_axis_category, $dataset)
    {
        if (count($dataset) != count($this->stat_items)) {
            throw new StatHelperException('the quantity of dataset not matches stat items');
        }
        $this->x_axis_categories[] = $x_axis_category;
        foreach ($dataset as $stat_item => $data) {
            $this->datasets[$stat_item][] = $data;
        }
    }

    public function getXAxisCategories()
    {
        return $this->x_axis_categories;
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
