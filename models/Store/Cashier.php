<?php

namespace Store;

class Cashier
{
    public $store;
    public $date;
    public $table;
    public $ordered_at;
    public $custermers;
    public $cart_items = array();
    public $discount_items = array();
    public $events = array();
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

    public function addItem(Cashier\CartItem $cart_item)
    {
        $this->cart_items[] = $cart_item;
        return $this;
    }

    public function addEvent(\EventRow $event)
    {
        $this->events[] = $event;
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
        foreach ($this->events as $event) {
            $event_helper = $event->getHelper();
            $event_helper->setCartItems($this->cart_items);
            $discount_items = array_merge($discount_items, $event_helper->generateDiscountItemsArray()->getArrayCopy());
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
        print_r($this->getPreviewData());exit;
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
            $data = array('product_id' => $cart_item->product_id, 'amount' => $cart_item->quantity);
            $item = $bill->create_items($data);
        }
        foreach ($this->discount_items as $discount_item) {
            /* create bill item */
            $data = array(
                'event_id' => $discount_item->event_id,
                'value' => $discount_item->getSubtotalPrice() * (-1),
            );
            $item = $bill->create_discounts($data);
        }

        return $bill;
    }
}
