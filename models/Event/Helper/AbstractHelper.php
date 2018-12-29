<?php

namespace Event\Helper;

abstract class AbstractHelper
{
    protected $cart_items = array();
    protected $event;

    public function __construct(\EventRow $event)
    {
        $this->event = $event;
        $this->event_id = $event->id;
        $this->store = $event->store;
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

    public function getCartItems()
    {
        return $this->cart_items;
    }

    public function getEvent()
    {
        return $this->event;
    }

    abstract public function generateDiscountItemsArray($options = null): \Store\Cashier\DiscountItemsArray;
    abstract public function setData($data = null);
    abstract public function getData();
}
