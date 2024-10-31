<?php

namespace Event\Helper;

class CategoryPercentOff extends AbstractHelper
{
    public function generateDiscountItemsArray($options = null): \Store\Cashier\DiscountItemsArray
    {
        $total_price = 0;
        $percent = $this->getData()['percent'] ?? 0;
        $percent_off = $percent / 100;
        $category_ids = $this->getData()['category_ids'] ?? [];
        $discount_items = new \Store\Cashier\DiscountItemsArray();
        foreach ($this->cart_items as $cart_item) {
            $product = $cart_item->getProduct();
            if (!in_array($product->category_id, $category_ids)) {
                continue;
            }
            $value = $cart_item->getUnitPrice() * $percent_off;
            $title = sprintf('%s-%s', $this->event->title, $product->name);
            $discount_item = new \Store\Cashier\DiscountItem($this->event);
            $discount_item->setUnitPrice($value * (-1));
            $discount_item->setQuantity($cart_item->getQuantity());
            $discount_item->setTitle($title);
            $discount_items[] = $discount_item;
        }

        return $discount_items;
    }

    public function setData($data = array())
    {
        $formatted_data = array();
        if ($percent_reversed = $data['percent_reversed']) {
            $formatted_data['percent_reversed'] = floatval("0.{$percent_reversed}") * 100;
            $formatted_data['percent'] = 100 - $formatted_data['percent_reversed'];
        } elseif ($percent = $data['percent']) {
            $formatted_data['percent_reversed'] = 100 - $percent;
        }
        if (!$category_ids = $data['category_ids']) {
            throw new LogicException('missing category_ids');
        }
        if (!is_array($category_ids)) {
            throw new LogicException('category_ids needs to be an array');
        }
        $formatted_data['category_ids'] = $category_ids;
        $this->event->update(array('data' => json_encode($formatted_data)));
    }

    public function getData()
    {
        $data = json_decode($this->event->data ?? '', 1);
        $data['valid_categories'] = $this->getValidCategories($data);
        $data['invalid_categories'] = $this->getInvalidCategories($data);
        return $data;
    }

    protected function getValidCategories($data)
    {
        foreach ($this->store->categories as $category) {
            if (in_array($category->id, $data['category_ids'])) {
                yield $category;
            }
        }
    }

    protected function getInvalidCategories($data)
    {
        foreach ($this->store->categories as $category) {
            if (!in_array($category->id, $data['category_ids'])) {
                yield $category;
            }
        }
    }
}

