<?php
require_once(ROOT_DIR . '/helpers/payment_methods.php');

class ShiftRow extends Pix_Table_Row
{
    public function postInsert()
    {
        $this->pushMessageToLineBotChats();
    }

    public function pushMessageToLineBotChats()
    {
        $chats = $this->store->getValidLineBotChats();
        if (0 == $chats->count()) {
            return;
        }
        $message = $this->formatMessage();
        foreach ($chats as $chat) {
            $chat->pushMessage($message);
        }
    }

    public function formatMessage()
    {
        $currency_symbol = $this->getCurrencySymbol();

        // 關帳日期
        $datetime = new Datetime();
        $datetime->setTimestamp($this->created_at);
        $start_at = $this->store->getDayStartAt($datetime);
        $date = date('Y/m/d', $start_at);

        // 撈出至前次關帳之間的訂單
        $previous_shift = $this->store->shifts->after($this)->order('created_at DESC')->first();
        if ($previous_shift) {
            $now = time();
            $shift_bills = $this->store->bills->search("`paid_at` BETWEEN {$previous_shift->created_at} AND {$this->created_at}");
        } else {
            $shift_bills = $this->store->getTodayPaidBills($datetime);
        }

        // 撈出各付款方式金額
        $all_payment_method_keys = array_unique($shift_bills->getPaymentMethods()->toArray('payment_method'));
        $factory = new Helpers\PaymentMethodFactory;
        $payment_method_items = $factory->getItemsByKeys($all_payment_method_keys);
        foreach ($payment_method_items as $item) {
            $method_total = $shift_bills->filterByPaymentMethodKey($item->getKey())->sum('price');
            $item->setProperty('total', $method_total);
        }
        $start_time = date('m/d H:i', $previous_shift->created_at);
        $end_time = date('m/d H:i', $this->created_at);

        $msg = sprintf("[%s關帳資訊]%s", $date, PHP_EOL);
        $msg .= sprintf("關帳時間: %s - %s%s", $start_time, $end_time, PHP_EOL);
        $msg .= sprintf("總營收: %s%s%s", $currency_symbol, $shift_bills->sum('price'), PHP_EOL);
        foreach ($payment_method_items as $item) {
            $msg .= sprintf("%s: %s%s%s", $item->getText('tw'), $currency_symbol, $item->getProperty('total'), PHP_EOL);
        }
        $msg .= sprintf("錢櫃初始金額: %s%s%s", $currency_symbol, $this->open_amount, PHP_EOL);
        $msg .= sprintf("錢櫃實際現金: %s%s%s", $currency_symbol, $this->close_amount, PHP_EOL);
        $msg .= sprintf("臨時支出: %s%s%s", $currency_symbol, $this->paid_out, PHP_EOL);
        $msg .= sprintf("臨時收入: %s%s%s", $currency_symbol, $this->paid_in, PHP_EOL);
        $msg .= sprintf("現金短溢: %s%s", $this->getDifferenceText(), PHP_EOL);
        $adjustment_type = (int)$this->adjustment_type;
        $adjustment_amount = $this->adjustment_amount;
        $adjustment_by = $this->getAdjustmentByInText();
        if (Shift::ADJUSTMENT_TAKEOUT === $adjustment_type) {
            $msg .= sprintf("營收取出: %s%s%s", $currency_symbol, $adjustment_amount, PHP_EOL);
            if ($adjustment_by) {
                $msg .= sprintf("取出人: %s%s", $adjustment_by, PHP_EOL);
            }
        } else if (Shift::ADJUSTMENT_ADD === $adjustment_type) {
            $msg .= sprintf("錢櫃補款: %s%s%s", $currency_symbol, $adjustment_amount, PHP_EOL);
            if ($adjustment_by) {
                $msg .= sprintf("補款人: %s%s", $adjustment_by, PHP_EOL);
            }
        }
        $msg .= sprintf("錢櫃留存現金: %s%s%s", $currency_symbol, $this->getFloat(), PHP_EOL);

        return $msg;
    }

    public function preInsert()
    {
        $this->created_at = time();
    }

    public function getExpectedAmount()
    {
        return $this->open_amount + $this->cash_sales + $this->paid_in - $this->paid_out;
    }

    public function getDifference()
    {
        return $this->close_amount - $this->getExpectedAmount();
    }

    public function getDifferenceText()
    {
        $difference = $this->getDifference();
        $currency_symbol = $this->getCurrencySymbol();
        if ($difference < 0) {
            return '-' . $currency_symbol . ($difference * (-1));
        } else {
            return '+' . $currency_symbol . $difference;
        }
    }

    public function getCurrencySymbol()
    {
        return '$';
    }

    public function getFloat()
    {
        $close_amount = $this->close_amount;
        switch ($this->adjustment_type) {
        case Shift::ADJUSTMENT_PASS:
            return $close_amount;
        case Shift::ADJUSTMENT_TAKEOUT:
            return $close_amount - $this->adjustment_amount;
        case Shift::ADJUSTMENT_ADD:
            return $close_amount + $this->adjustment_amount;
        }
    }

    public function getAdjustmentValue()
    {
        switch ($this->adjustment_type) {
        case Shift::ADJUSTMENT_PASS:
            return 0;
        case Shift::ADJUSTMENT_TAKEOUT:
            return $this->adjustment_amount;
        case Shift::ADJUSTMENT_ADD:
            return $this->adjustment_amount * (-1);
        }
    }

    public function getAdjustmentByInText()
    {
        return Staff::find($this->adjustment_by)->name ?? '';
    }
}

class Shift extends Pix_Table
{
    public $_rowClass = 'ShiftRow';

    public function init()
    {
        $this->_name = 'shift';
        $this->_primary = array('id');

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true, 'unsigned' => true);
        $this->_columns['store_id'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['cash_sales'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['open_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['close_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['paid_in'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['paid_out'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['adjustment_type'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['adjustment_amount'] = array('type' => 'double', 'unsigned' => true);
        $this->_columns['adjustment_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['closed_by'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_at'] = array('type' => 'int', 'unsigned' => true);

        $this->addIndex('store_id', array('store_id'));
        $this->addIndex('adjustment_by', array('adjustment_by'));
        $this->addIndex('closed_by', array('closed_by'));
        $this->addIndex('created_at', array('created_at'));

        $this->_relations['store'] = array('rel' => 'has_one', 'type' => 'Store', 'foreign_key' => 'store_id');
    }

    const ADJUSTMENT_PASS = 0;
    const ADJUSTMENT_TAKEOUT = 1;
    const ADJUSTMENT_ADD = 2;
}
