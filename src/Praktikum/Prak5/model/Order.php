<?php declare(strict_types=1);


class Order
{
    public $ordered_article_id = array();
    public $ordered_article_name = array();
    public $orderId;
    public $status;// all pizzas now have the same status
    public $price;
    public $address;

    function __construct( $orderId, $status,$price,$address)
    {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->price = $price;
        $this->address = $address;
    }
}