<?php

namespace Event\Helper;

class CategoryPercentOff extends AbstractHelper
{
    public function generateDiscountItemsArray($options = null): \Store\Cashier\DiscountItemsArray
    {
        $total_price = 0;
        foreach ($this->cart_items as $cart_item) {
            $total_price += $cart_item->getSubtotalPrice();
        }
        $percent_off = $this->getData()['percent'] / 100;
        $value = intval($total_price * $percent_off);
        $discount_item = new \Store\Cashier\DiscountItem($this->event);
        $discount_item->setUnitPrice($value * (-1));
        $discount_item->setQuantity(1);

        $discount_items = new \Store\Cashier\DiscountItemsArray();
        $discount_items[] = $discount_item;

        return $discount_items;
    }

    public function setData($data = array())
    {
        if ($percent_reversed = $data['percent_reversed']) {
            $percent_reversed = floatval("0.{$percent_reversed}") * 100;
            $percent = 100 - $percent_reversed;
        } elseif ($percent = $data['percent']) {
            $percent_reversed = 100 - $percent;
        }
        if (!$category_ids = $data['category_ids']) {
            throw new LogicException('missing category_ids');
        }
        if (!is_array($category_ids)) {
            throw new LogicException('category_ids needs to be an array');
        }
        $this->event->update(array('data' => json_encode($data)));
    }

    public function getData()
    {
        $data = json_decode($this->event->data, 1);
        $valid_categories = array();
        $invalid_categories = array();
        foreach ($this->store->categories as $category) {
            if (in_array($category->id, $data['category_ids'])) {
                $valid_categories[] = $category;
            } else {
                $invalid_categories[] = $category;
            }
        }
        $data['valid_categories'] = $valid_categories;
        $data['invalid_categories'] = $invalid_categories;
        return $data;
    }
}

