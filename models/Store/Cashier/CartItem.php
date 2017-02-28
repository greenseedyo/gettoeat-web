<?php

namespace Store\Cashier;

class CartItem implements AbstractReceiptItem
{
    protected $product_id;
    protected $quantity;

    public function __construct($product_id, $quantity)
    {
        $this->product_id = $product_id;
        $this->quantity = $quantity;
    }

    public function getProduct()
    {
        if ($this->product) {
            return $this->product;
        }
        if (!$product = \Product::find(intval($this->product_id))) {
            throw new Exception("product not found, id: {$this->product_id}");
        }
        $this->product = $product;
        return $this->product;
    }

    public function getTitle()
    {
        return $this->getProduct()->name;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getUnitPrice()
    {
        return $this->getProduct()->price;
    }

    public function getSubtotalPrice()
    {
        return $this->getUnitPrice() * $this->getQuantity();
    }
}
