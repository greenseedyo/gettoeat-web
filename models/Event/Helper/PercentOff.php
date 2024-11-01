<?php

namespace Event\Helper;

class PercentOff extends AbstractHelper
{
    public function generateDiscountItemsArray($options = null): \Store\Cashier\DiscountItemsArray
    {
        $total_price = 0;
        foreach ($this->cart_items as $cart_item) {
            $total_price += $cart_item->getSubtotalPrice();
        }
        $percent_off = ($this->getData()['percent'] ?? 0) / 100;
        $value = intval($total_price * $percent_off);
        $discount_item = new \Store\Cashier\DiscountItem($this->event);
        $discount_item->setUnitPrice($value * (-1));
        $discount_item->setQuantity(1);

        $discount_items = new \Store\Cashier\DiscountItemsArray();
        $discount_items[] = $discount_item;

        return $discount_items;
    }

    public function setData($data = null)
    {
        if ($percent_reversed = $data['percent_reversed'] ?? false) {
            $percent_reversed = floatval("0.{$percent_reversed}") * 100;
            $percent = 100 - $percent_reversed;
        } elseif ($percent = $data['percent'] ?? false) {
            $percent_reversed = 100 - $percent;
        }
        $new_data = array(
            'percent' => $percent ?? null,
            'percent_reversed' => $percent_reversed ?? null,
        );
        $this->event->update(array('data' => json_encode($new_data)));
    }

    public function getData()
    {
        return json_decode($this->event->data ?? '', 1);
    }
}

