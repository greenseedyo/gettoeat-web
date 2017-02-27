<?php

namespace Store\Cashier;

class DiscountItem extends AbstractReceiptItem
{
    public $event;

    public function __construct(\EventRow $event)
    {
        $this->event = $event;
    }

    public function getTitle()
    {
        return $this->event->title;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    public function getSubtotalPrice()
    {
        return $this->getUnitPrice() * $this->getQuantity();
    }
}

