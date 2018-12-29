<?php

namespace Store;

class Cashier
{
    public $store;
    public $date;
    public $table;
    public $ordered_at;
    public $custermers;
    public $payments = array();
    public $cart_items = array();
    public $discount_items = array();
    public $event_datas = array();
    public $receipt_items = array();

    public function __construct(\StoreRow $store)
    {
        $this->store = $store;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function setOrderedAt($timestamp)
    {
        $this->ordered_at = $timestamp;
        return $this;
    }

    public function setCustermers($custermers)
    {
        $this->custermers = $custermers;
        return $this;
    }

    public function addPayment($method_key, $amount = null)
    {
        $this->payments[] = array(
            'method_key' => $method_key,
            'amount' => $amount,
        );
        return $this;
    }

    public function addItem(Cashier\CartItem $cart_item)
    {
        $this->cart_items[] = $cart_item;
        return $this;
    }

    public function addEvent(\EventRow $event, $options = null)
    {
        $this->event_datas[] = array(
            'event' => $event,
            'options' => $options,
        );
        return $this;
    }

    public function setReceiptItems()
    {
        $this->generateDiscountItems();
        $receipt_items = array_merge($this->cart_items, $this->discount_items);
        $this->receipt_items = $receipt_items;
        return $this;
    }

    protected function generateDiscountItems()
    {
        $discount_items = array();
        foreach ($this->event_datas as $event_data) {
            $event_helper = $event_data['event']->getHelper();
            $event_helper->setCartItems($this->cart_items);
            $discount_items = array_merge($discount_items, $event_helper->generateDiscountItemsArray($event_data['options'])->toArray());
        }
        $this->discount_items = $discount_items;
    }

    public function getPreviewData($format = "text")
    {
        $this->setReceiptItems();
        $data = null;
        foreach ($this->receipt_items as $receipt_item) {
            switch ($format) {
            case "text":
                $data .= sprintf(
                    "%s %s %d %s\n",
                    $receipt_item->getTitle(),
                    $receipt_item->getUnitPrice(),
                    $receipt_item->getQuantity(),
                    $receipt_item->getSubtotalPrice()
                );
                break;
            }
        }
        return $data;
    }

    public function getTotalPrice()
    {
        $total_price = 0;
        foreach ($this->receipt_items as $receipt_item) {
            $total_price += $receipt_item->getSubtotalPrice();
        }
        return $total_price;
    }

    public function createBill()
    {
        $this->setReceiptItems();
        $data = array(
            'year' => date('Y', $this->date),
            'month' => date('m', $this->date),
            'date' => date('d', $this->date),
            'day' => date('N', $this->date),
            'price' => $this->getTotalPrice(),
            'table' => $this->table,
            'ordered_at' => $this->ordered_at,
            'custermers' => $this->custermers,
            'paid_at' => time(),
        );

        $bill = $this->store->create_bills($data);

        foreach ($this->cart_items as $cart_item) {
            /* create bill item */
            $data = array(
                'product_id' => $cart_item->getProduct()->id,
                'unit_price' => $cart_item->getUnitPrice(),
                'amount' => $cart_item->getQuantity(),
            );
            $item = $bill->create_items($data);
        }
        foreach ($this->discount_items as $discount_item) {
            /* create bill item */
            $data = array(
                'event_id' => $discount_item->getEvent()->id,
                'value' => $discount_item->getSubtotalPrice() * (-1),
                'title' => $discount_item->getTitle(),
            );
            $item = $bill->create_discounts($data);
        }

        // 暫時只實作單一付款方式
        foreach ($this->payments as $payment) {
            $data = array(
                'payment_method' => $payment['method_key'],
                'amount' => $bill->price,
            );
            $bill->create_payments($data);
        }

        return $bill;
    }
}
