<?php

namespace Event\Helper;

class PriceOff extends AbstractHelper
{
    public function generateDiscountItemsArray($options = null): \Store\Cashier\DiscountItemsArray
    {
        $discount_item = new \Store\Cashier\DiscountItem($this->event);
        $discount_item->setUnitPrice($options['price'] * (-1));
        $discount_item->setQuantity(1);

        $discount_items = new \Store\Cashier\DiscountItemsArray();
        $discount_items[] = $discount_item;

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

