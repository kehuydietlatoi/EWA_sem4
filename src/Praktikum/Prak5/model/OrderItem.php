<?php declare(strict_types=1);
// UTF-8 marker äöüÄÖÜß€

class OrderItem
{
    public $name;
    public $itemId;
    public $orderId;
    public $status;
    public $picture;
    public $price;

    function __construct($name, $itemId, $orderId, $status, $picture, $price)
    {
        $this->name = $name;
        $this->itemId = $itemId;
        $this->orderId = $orderId;
        $this->status = $status;
        $this->picture = $picture;
        $this->price = $price;
    }
}
class OrderItemToDelivery
{
    public $name;
    public $itemId;
    public $orderId;
    public $status;
    public $picture;
    public $price;
    public $address;

    function __construct($name, $itemId, $orderId, $status, $picture, $price,$address)
    {
        $this->name = $name;
        $this->itemId = $itemId;
        $this->orderId = $orderId;
        $this->status = $status;
        $this->picture = $picture;
        $this->price = $price;
        $this->address = $address;
    }
}
