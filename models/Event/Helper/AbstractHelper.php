<?php

namespace Event\Helper;

abstract class AbstractHelper
{
    public $cart_items = array();

    public function setCartItems(array $cart_items)
    {
        foreach ($cart_items as $cart_item) {
            if (!$cart_item instanceof \Store\Cashier\CartItem) {
                throw new Exception('needs to be an instanceof \Store\Cashier\CartItem');
            }
        }
        $this->cart_items = $cart_items;
    }

    abstract public function generateDiscountItems($data = null);
    abstract public function setData($data = null);
    abstract public function getData();
}
