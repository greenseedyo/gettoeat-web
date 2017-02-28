<?php

namespace Store\Cashier;

class DiscountItemsArray implements \ArrayAccess
{
    private $container = array();

    public function __construct()
    {
        $this->container = array();
    }

    public function offsetSet($offset, $value)
    {
        $this->checkInputType($value);
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function checkInputType(DiscountItem $input)
    {
        return true;
    }

    public function toArray()
    {
        return $this->container;
    }
}
