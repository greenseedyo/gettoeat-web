<?php

namespace Event\Helper;

class PriceOff extends AbstractHelper
{
    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function generateDiscountItems($data = null)
    {
        $discount_item = new \Store\Cashier\DiscountItem();
        $discount_item->unit_price = $data['price'] * (-1);
        $discount_item->quantity = 1;

        return array($discount_item);
    }

    public function setData($data = null)
    {
    }

    public function getData()
    {
        return null;
    }
}

