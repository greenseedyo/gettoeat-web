<?php

namespace Event\Helper;

class PriceOff extends AbstractHelper
{
    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function generateDiscountItemsArray($data = null): \Store\Cashier\DiscountItemsArray
    {
        $discount_item = new \Store\Cashier\DiscountItem();
        $discount_item->unit_price = $data['price'] * (-1);
        $discount_item->quantity = 1;

        $discount_items = new \Store\Cashier\DiscountItemsArray();
        $discount_items->append($discount_item);

        return $discount_items;
    }

    public function setData($data = null)
    {
    }

    public function getData()
    {
        return null;
    }
}

