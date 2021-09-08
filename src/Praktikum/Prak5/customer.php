<?php declare(strict_types=1);
// UTF-8 marker äöüÄÖÜß€
session_start();
/**
 * Customer class that represents the 'Customer' page
 *
 * PHP Version 7.4
 *
 * @file     Customer.php
 * @package  Prak2
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 * @version  3.0
 */

require_once './base/Page.php';
require_once './model/OrderItem.php';

/**
 * This is a template for top level classes, which represent
 * a complete web page and which are called directly by the user.
 * Usually there will only be a single instance of such a class.
 * The name of the template is supposed
 * to be replaced by the name of the specific HTML page e.g. baker.
 * The order of methods might correspond to the order of thinking
 * during implementation.
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 */
class Customer extends Page
{
    // to do: declare reference variables for members 
    // representing substructures/blocks


    /**
     * Instantiates members (to be defined above).
     * Calls the constructor of the parent i.e. page class.
     * So, the database connection is established.
     * @throws Exception
     */
    protected function __construct()
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }

    /**
     * Cleans up whatever is needed.
     * Calls the destructor of the parent i.e. page class.
     * So, the database connection is closed.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Fetch all data that is necessary for later output.
     * Data is returned in an array e.g. as associative array.
	 * @return array An array containing the requested data. 
	 * This may be a normal array, an empty array or an associative array.
     */
    protected function getViewData(): array
    {
        error_reporting(E_ALL);
        $orders = array();
        if (!isset($_SESSION["BestellungID"])){
            return $orders;
        }
        
        $query  = "SELECT article.name,article.picture,article.price,ordered_article.* FROM ordered_article JOIN article WHERE ordered_article.article_id = article.article_id AND ordered_article.ordering_id = {$_SESSION["BestellungID"]}";
        $records = $this->_database->query($query);
        if (!$records) {
            throw new Exception("Query failed: ". $this->_database->error);
        }

        while ($record = $records->fetch_assoc()) {

            $order = new OrderItem(
                $record["name"],
                $record["ordered_article_id"],
                $record["ordering_id"],
                $record["status"],
                $record["picture"],
                $record["price"]
            );
            $orders[$order->itemId] = $order;
        }
        $records->free();

        return $orders;
    }

    /**
     * First the required data is fetched and then the HTML is
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if available- the content of
     * all views contained is generated.
     * Finally, the footer is added.
	 * @return void
     */
    protected function generateView():void
    {
		$data = $this->getViewData();
        $this->generatePageHeader("Kunde");

        echo <<< EOT
        <main>
        EOT;

        if (isset($_GET["status"]) && $_GET["status"] == "order_submitted") {
            echo "Deine Bestellung wurde erfolgreich abgesendet.";
        }

        echo <<< EOT
            <h1 class="text-center">Deine Bestellungen</h1>
            <div class="row">
                <div class="col" id="order-container">
                </div>
            </div>
        </main>
        <script src="scripts/customer.js"></script>
        EOT;

        $this->generatePageFooter();
    }

    private function genAttr($order, $min) {
        return $order->status >= $min ? "done" : "";
    }

    /**
     * Print article Status for each pizza
     *
     * @param OrderItem an order item
     */
    public function printOrderedArticle($order): void
    {
        echo <<< EOT
        <div class="pizza">
            <img alt="" src="{$order->picture}" width="180">
            <div class="pizza-meta">
                <h2>Bestellung #{$order->orderId} - Pizza {$order->name}</h2>

                <div class="order-state-container">
                    <img alt="Bestellt" class="order-state done" src="img/icon-cart-white.svg">
                    <span class="order-line {$this->genAttr($order, 1)}"></span>
                    
                    <img alt="Zubereitung" class="order-state {$this->genAttr($order, 1)}" src="img/icon-blender-white.svg">
                    <span class="order-line {$this->genAttr($order, 2)}"></span>

                    <img alt="Auslieferung" class="order-state {$this->genAttr($order, 3)}" src="img/icon-shipping-white.svg">
                    <span class="order-line {$this->genAttr($order, 4)}"></span>

                    <img alt="Zugestellt" class="order-state {$this->genAttr($order, 4)}" src="img/icon-all-done-white.svg">
                </div>
            </div>
        </div>
        EOT;
    }

    /**
     * This main-function has the only purpose to create an instance
     * of the class and to get all the things going.
     * I.e. the operations of the class are called to produce
     * the output of the HTML-file.
     * The name "main" is no keyword for php. It is just used to
     * indicate that function as the central starting point.
     * To make it simpler this is a static function. That is you can simply
     * call it without first creating an instance of the class.
	 * @return void
     */
    public static function main():void
    {
        try {
            $page = new Customer();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Customer::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >