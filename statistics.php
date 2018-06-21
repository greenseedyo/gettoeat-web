<?php
require_once 'config.php';

class StatHelper
{
    public function __construct(StoreRow $store, $start_at, $end_at)
    {
        $this->store = $store;
        $this->start_at = $start_at;
        $this->end_at = $end_at;
    }

    public function getTurnoverSum()
    {
        $bills = $this->store->bills->search("`ordered_at` BETWEEN {$this->start_at} AND {$this->end_at}");
        $turnover = $bills->sum('price');
        return $turnover;
    }

    public function getDiscountSum()
    {
        $bills = $this->store->bills->search("`ordered_at` BETWEEN {$this->start_at} AND {$this->end_at}");
        $bill_discounts = BillDiscount::search(1)->searchIn('bill_id', $bills->toArray('id'));
        $discount = $bill_discounts->sum('value');
        return $discount;
    }
}

/* 月營收資料 */
$month_names = array();
$month_datasets = array(
    '總營收' => array(),
    '折扣' => array(),
);
for ($i = 11; $i >= 0; $i --) {
    $yearmonth = mktime(0, 0, 0, date('m') - $i, 1, date('Y'));
    $year = date('Y', $yearmonth);
    $month = date('m', $yearmonth);
    $yearmonth = "{$year}-{$month}";
    $month_names[] = $yearmonth;
    $start_date = new DateTime($yearmonth);
    $start_at = $store->getDayStartAt($start_date);
    $end_date = new DateTime("{$yearmonth} +1month -1day");
    $end_at = $store->getDayEndAt($end_date);
    $helper = new StatHelper($store, $start_at, $end_at);
    $month_datasets['總營收'][] = $helper->getTurnoverSum();
    $month_datasets['折扣'][] = $helper->getDiscountSum();
}

/* 每日營收資料 */
$date_names = array();
$date_datasets = array(
    '總營收' => array(),
    '折扣' => array(),
);
for ($i = 30; $i >= 0; $i--) {
    $date_timestamp = mktime(0, 0, 0, date('m'), date('d') - $i, date('Y'));
    $year = date('Y', $date_timestamp);
    $month = date('m', $date_timestamp);
    $date = date('d', $date_timestamp);
    $day = date('D', $date_timestamp);
    $date_names[] = "{$month}-{$date}({$day})";
    $datetime = new DateTime("{$year}-{$month}-{$date}");
    $start_at = $store->getDayStartAt($datetime);
    $end_at = $store->getDayEndAt($datetime);
    $helper = new StatHelper($store, $start_at, $end_at);
    $date_datasets['總營收'][] = $helper->getTurnoverSum();
    $date_datasets['折扣'][] = $helper->getDiscountSum();
}

include(VIEWS_DIR . '/statistics.html');
