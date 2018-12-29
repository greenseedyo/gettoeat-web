<?php

namespace Store\Cashier;

class DiscountItem implements AbstractReceiptItem
{
    protected $event;
    protected $quantity;
    protected $unit_price;
    protected $title;

    public function __construct(\EventRow $event)
    {
        $this->event = $event;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function setUnitPrice($unit_price)
    {
        $this->unit_price = $unit_price;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title ?: $this->event->title;
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

    public function getEvent()
    {
        return $this->event;
    }
}

