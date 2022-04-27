<?php
namespace Helpers;

use Store;
use BillPaymentRow;

class PaymentMethodFactory
{
    protected static function getAllData(): array
    {
        $data = array(
            Store::PAYMENT_METHOD_CASH => array(
                'key' => Store::PAYMENT_METHOD_CASH,
                'name' => 'cash',
                'texts' => array('tw' => '現金'),
            ),
            Store::PAYMENT_METHOD_CARD => array(
                'key' => Store::PAYMENT_METHOD_CARD,
                'name' => 'card',
                'texts' => array('tw' => '信用卡'),
            ),
            Store::PAYMENT_METHOD_JKOPAY => array(
                'key' => Store::PAYMENT_METHOD_JKOPAY,
                'name' => 'jkopay',
                'texts' => array('tw' => '街口支付'),
            ),
            Store::PAYMENT_METHOD_UBEREATS => array(
                'key' => Store::PAYMENT_METHOD_UBEREATS,
                'name' => 'ubereats',
                'texts' => array('tw' => 'Uber Eats'),
            ),
            Store::PAYMENT_METHOD_LINE_PAY => array(
                'key' => Store::PAYMENT_METHOD_LINE_PAY,
                'name' => 'linepay',
                'texts' => array('tw' => 'LINE Pay'),
            ),
        );
        return $data;
    }

    public static function getAllTexts(string $country_code = 'tw'): array
    {
        $all_data = self::getAllData();
        $items = array();
        foreach ($all_data as $data) {
            $items[$data['key']] = $data['texts'][$country_code];
        }
        return $items;
    }

    public static function getAllItems(): array
    {
        $all_data = self::getAllData();
        $items = array();
        foreach ($all_data as $data) {
            $items[] = new PaymentMethodItem($data);
        }
        return $items;
    }

    public function getItemsByKeys(array $payment_method_keys): array
    {
        $all = $this->getAllData();
        $payment_methods_data = array_filter($all, function($key) use ($payment_method_keys) {
            return in_array($key, $payment_method_keys);
        }, ARRAY_FILTER_USE_KEY);

        $items = array();
        foreach ($payment_methods_data as $data) {
            $items[] = new PaymentMethodItem($data);
        }
        return $items;
    }

    public function getItemByBillPaymentRow(BillPaymentRow $bill_payment_row): PaymentMethodItem
    {
        $data = $this->getAllData()[$bill_payment_row->payment_method];
        $item = new PaymentMethodItem($data);
        return $item;
    }
}


class PaymentMethodItem
{
    private $key;
    private $name;
    private $texts = array();

    public function __construct($data)
    {
        $this->key = $data['key'];
        $this->name = $data['name'];
        $this->texts = $data['texts'];
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getText(string $country_code = 'tw'): string
    {
        return $this->texts[$country_code];
    }

    public function setProperty($name, $value)
    {
        $this->{$name} = $value;
    }

    public function getProperty($name)
    {
        return $this->{$name};
    }
}
