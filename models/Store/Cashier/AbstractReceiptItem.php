<?php

namespace Store\Cashier;

abstract class AbstractReceiptItem
{
    public $unit_price;
    public $quantity;

    abstract public function getTitle();
    abstract public function getQuantity();
    abstract public function getUnitPrice();
    abstract public function getSubtotalPrice();
}


