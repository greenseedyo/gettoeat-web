<?php

namespace Event\Helper;

class PercentOff extends AbstractHelper
{
    public $event;
    public $cart_items = array();

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function setCartItems(array $cart_items)
    {
        foreach ($cart_items as $cart_item) {
            if (!$cart_item instanceof \Store\Cashier\CartItem) {
                throw new Exception('needs to be an instanceof \Store\Cashier\CartItem');
            }
        }
        $this->cart_items = $cart_items;
    }

    public function generateDiscountItems()
    {
        $value = 0;
        foreach ($this->items as $item) {
            $value += $item->getSubtotalPrice();
        }
        $discount_item = new \Store\Cashier\DiscountItem();
        $discount_item->unit_price = $value * (-1);
        $discount_item->quantity = 1;

        return array($discount_item);
    }
}

