<?php

namespace Store\Cashier;

class DiscountItem extends AbstractReceiptItem
{
    public $event_id;

    protected function getEvent()
    {
        if ($this->event) {
            return $this->event;
        }
        if (!$event = Event::find(intval($this->event_id))) {
            throw new Exception("event not found, id: {$this->event_id}");
        }
        $this->event = $event;
        return $this->event;
    }

    public function getTitle()
    {
        return $this->getEvent()->title;
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

