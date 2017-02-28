<?php

namespace Store\Cashier;

interface AbstractReceiptItem
{
    public function getTitle();
    public function getQuantity();
    public function getUnitPrice();
    public function getSubtotalPrice();
}


