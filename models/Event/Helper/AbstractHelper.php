<?php

namespace Event\Helper;

abstract class AbstractHelper
{
    abstract public function setCartItems(array $cart_items);
    abstract public function generateDiscountItems();
}
